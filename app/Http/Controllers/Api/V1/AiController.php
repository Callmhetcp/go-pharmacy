<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\GeminiService;
use App\Services\GoPharmacyAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AiController extends Controller
{
    public function chat(
        Request $request,
        GeminiService $gemini,
        GoPharmacyAiService $goPharmacyAi
    ): JsonResponse {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $message = trim($validated['message']);

            /*
            |--------------------------------------------------------------------------
            | Determine the type of question
            |--------------------------------------------------------------------------
            */
            $intent = $this->detectIntent($message);

            /*
            |--------------------------------------------------------------------------
            | Order status requires the authenticated customer's real order data.
            |--------------------------------------------------------------------------
            */

            if ($intent === 'order_status') {
                return response()->json([
                    'message' => $this->getOrderStatusAnswer($request),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Basic Go Pharmacy guidance does not require Gemini.
            |--------------------------------------------------------------------------
            */

            $directAnswer = $this->getDirectAnswer($intent);

            if ($directAnswer !== null) {
                return response()->json([
                    'message' => $directAnswer,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Product questions
            |--------------------------------------------------------------------------
            |
            | Product questions still use the database and Gemini because Gemini
            | helps turn the database results into a natural customer response.
            |
            */
            $products = collect();

            if ($intent === 'product') {
                $filters = $goPharmacyAi->extractProductFilters(
                    $message,
                    $gemini
                );

                if (!empty($filters['search'])) {
                    $products = $goPharmacyAi->searchProducts($filters);
                }

                $productContext = $products->isNotEmpty()
                    ? $goPharmacyAi->buildProductContext($products)
                    : 'No matching products were found in the Go Pharmacy database.';

                $prompt = <<<PROMPT
You are the AI customer assistant for Go Pharmacy, a modern pharmacy and healthcare platform in Nigeria.

The customer asked:

{$message}

The following information comes directly from the Go Pharmacy database:

{$productContext}

Answer the customer clearly, professionally, and briefly.

Important rules:

- Only state product information that appears in the provided Go Pharmacy data.
- Do not invent products, prices, stock levels, prescription requirements, or services.
- If no matching products were found, clearly say that no matching products were found in the current Go Pharmacy catalog.
- If products are found, mention their actual names and prices from the database.
- Respect any budget specified by the customer.
- Do not diagnose the customer.
- Do not recommend medicine based on a diagnosis.
- Do not tell customers to start, stop, or change medication.
- For medical advice, recommend speaking with a pharmacist or qualified healthcare professional.
- Do not expose internal system details.
- Keep the response concise and helpful.

PROMPT;

                $answer = $gemini->generate($prompt);

                return response()->json([
                    'message' => $answer,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Other questions can use Gemini when available.
            |--------------------------------------------------------------------------
            */
            $prompt = <<<PROMPT
You are the AI customer assistant for Go Pharmacy, a modern pharmacy and healthcare platform in Nigeria.

The customer asked:

{$message}

Answer clearly, professionally, and briefly.

Important rules:

- Do not invent Go Pharmacy policies, prices, services, delivery information, payment methods, or other business information.
- Do not expose internal system details.
- Do not diagnose customers.
- Do not prescribe medicines.
- Do not tell customers to start, stop, or change medication.
- For medical advice, recommend speaking with a pharmacist or qualified healthcare professional.
- If you do not have enough confirmed information to answer a Go Pharmacy-specific question, tell the customer to contact Go Pharmacy for confirmation.
- Keep the response concise and helpful.

PROMPT;

            $answer = $gemini->generate($prompt);

            return response()->json([
                'message' => $answer,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Sorry, the AI assistant is temporarily unavailable. Please try again later.',
            ], 503);
        }
    }

    /**
 * Return the authenticated customer's latest order status.
 */
private function getOrderStatusAnswer(Request $request): string
    {
        $user = Auth::guard('web')->user();

        if (!$user) {
            return 'Please log in to your Go Pharmacy account so I can check your order status.';
        }

        $order = Order::query()
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$order) {
            return 'I could not find any orders on your Go Pharmacy account yet.';
        }

        $status = match ($order->status) {
            'pending' => 'pending and awaiting confirmation',
            'confirmed' => 'confirmed',
            'processing' => 'being prepared',
            'ready' => 'ready for delivery',
            'shipped' => 'shipped and on the way',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            default => $order->status,
        };

        $payment = match ($order->payment_status) {
            'paid' => 'Your payment has been received.',
            'pending' => 'Payment is still pending.',
            default => 'Payment status: ' . $order->payment_status . '.',
        };

        return "Your latest order ({$order->order_number}) is currently {$status}. {$payment}";
    }

    /**
     * Detect the customer's question type without using Gemini.
     */
    private function detectIntent(string $message): string
    {
        $message = strtolower($message);

        $intents = [
            'ordering' => [
                'how do i order',
                'how do i place an order',
                'how can i order',
                'how can i place an order',
                'place an order',
                'make an order',
                'buy something',
                'how to order',
                'order a product',
            ],

            'order_status' => [
                'order status',
                'status of my order',
                'what is my order status',
                'where is my order',
                'track my order',
                'track order',
                'order tracking',
                'has my order been confirmed',
                'is my order confirmed',
                'has my order shipped',
                'is my order shipped',
                'when will my order arrive',
            ],

            'delivery' => [
            'delivery',
            'deliver',
            'shipping',
            'ship',
            'delivery fee',
            'delivery cost',
            'how much is delivery',
            'how long will delivery',
            'delivery time',
            'delivery location',
            'where do you deliver',
        ],

            'payment' => [
                'payment',
                'pay',
                'how do i pay',
                'how can i pay',
                'payment method',
                'pay for my order',
            ],

            'prescription' => [
                'prescription',
                'prescription required',
                'need a prescription',
                'do i need a prescription',
                'upload prescription',
            ],

            'cart' => [
            'add to cart',
            'add it to my cart',
            'add product to cart',
            'add item to cart',
            'remove from cart',
            'remove it from cart',
            'remove item from cart',
            'remove product from cart',
            'change quantity',
            'update quantity',
            'increase quantity',
            'decrease quantity',
            'my cart',
            'shopping cart',
            'proceed to checkout',
            'go to checkout',
        ],
        
        ];

        foreach ($intents as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    return $intent;
                }
            }
        }

        if (
            str_contains($message, 'product') ||
            str_contains($message, 'medicine') ||
            str_contains($message, 'drug') ||
            str_contains($message, 'tablet') ||
            str_contains($message, 'capsule') ||
            str_contains($message, 'syrup') ||
            str_contains($message, 'price') ||
            str_contains($message, 'cost') ||
            str_contains($message, 'stock') ||
            $this->containsProductQuestion($message)
        ) {
            return 'product';
        }

        return 'general';
    }

    /**
     * Return answers that do not need Gemini.
     */
    private function getDirectAnswer(string $intent): ?string
    {
        return match ($intent) {
            'cart' => 'To manage your cart, add the product you want from the shop to your cart. Open "Your Cart" to review your items, change quantities using the − and + controls, or remove individual items. When you are ready, click "Proceed to Checkout".',

            'ordering' => 'To place an order, browse or search for the product you need and add it to your cart. Open your cart to review the products, quantities, and subtotal, then click "Proceed to Checkout". On the checkout page, enter your contact and delivery information, select or save a delivery address if needed, and optionally add delivery or order notes. Then click "Continue to Payment". Your order will be created first before you proceed to the payment step. Delivery fees and any applicable discounts are calculated at checkout.',

            'prescription' => 'Some products may require a valid prescription. If you are unsure whether a product requires one, check the product information or contact a Go Pharmacy pharmacist for assistance. I can also help you check a specific product.',

            'delivery' => 'Delivery fees are calculated during checkout based on Go Pharmacy’s current delivery settings. If a free-delivery threshold applies to your order, the delivery fee may be waived. Your final delivery fee is shown in the checkout order summary. For delivery locations or delivery times, please contact Go Pharmacy.',

            'payment' => 'After you complete checkout, your order is created and you are taken to the payment page. Your order will show as awaiting payment, but online payment is not currently available. No payment is taken at this stage. Payment processing will be enabled once Go Pharmacy approves and configures a payment provider.',

            default => null,
        };
    }

    /**
     * Check whether the message contains a known Go Pharmacy product.
     */
    private function containsProductQuestion(string $message): bool
    {
        return \App\Models\Product::query()
            ->where('is_active', true)
            ->where(function ($query) use ($message) {
                $query
                    ->whereRaw('LOWER(name) LIKE ?', ['%' . $message . '%'])
                    ->orWhereRaw('LOWER(generic_name) LIKE ?', ['%' . $message . '%'])
                    ->orWhereRaw('LOWER(brand) LIKE ?', ['%' . $message . '%']);
            })
            ->exists();
    }
}
