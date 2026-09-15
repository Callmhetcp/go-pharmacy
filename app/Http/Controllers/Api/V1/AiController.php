<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\Order;
use App\Services\GeminiService;
use App\Services\GoPharmacyAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AiController extends Controller
{
    /**
     * Return the authenticated customer's active AI conversation.
     */
    public function conversation(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'messages' => [],
            ]);
        }

        $conversation = AiConversation::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [
                'active',
                'waiting_for_pharmacist',
                'with_pharmacist',
            ])
            ->with([
                'messages' => function ($query) {
                    $query->orderBy('created_at');
                },
            ])
            ->latest()
            ->first();

            if ($conversation) {
            $lastMessage = $conversation->messages->last();

            if (
                $lastMessage &&
                $lastMessage->created_at->lte(now()->subHours(24))
            ) {
                $conversation->update([
                    'status' => 'expired',
                    'assigned_to' => null,
                ]);

                $conversation = null;
            }
        }

        if (!$conversation) {
            return response()->json([
                'messages' => [],
            ]);
        }

        return response()->json([
            'status' => $conversation->status,

            'messages' => $conversation->messages
                ->map(function ($message) {
                    return [
                        'role' => $message->sender_type === 'customer'
                            ? 'user'
                            : $message->sender_type,
                        'content' => $message->message,
                        'created_at' => $message->created_at?->toISOString(),
                    ];
                })
                ->values(),
        ]);
    }

    /**
     * Process a customer's AI chat message.
     */
    public function chat(
        Request $request,
        GeminiService $gemini,
        GoPharmacyAiService $goPharmacyAi
    ): JsonResponse {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();
        $conversation = null;
        $handoffRequested = false;

        if ($user) {
            $conversation = AiConversation::query()
                ->where('user_id', $user->id)
                ->whereIn('status', [
                    'active',
                    'waiting_for_pharmacist',
                    'with_pharmacist',
                ])
                ->with([
                    'messages' => function ($query) {
                        $query->latest('created_at')->limit(1);
                    },
                ])
                ->latest()
                ->first();

            if ($conversation) {
                $lastMessage = $conversation->messages->first();

                if (
                    $lastMessage &&
                    $lastMessage->created_at->lte(now()->subHours(24))
                ) {
                    $conversation->update([
                        'status' => 'expired',
                        'assigned_to' => null,
                    ]);

                    $conversation = null;
                }
            }
        }

        try {
            $message = trim($validated['message']);

            /*
            |--------------------------------------------------------------------------
            | Pharmacist takeover
            |--------------------------------------------------------------------------
            |
            | Once a pharmacist has taken over the conversation, customer
            | messages are saved for the pharmacist instead of being sent
            | through Gemini.
            |
            */

            if ($conversation?->status === 'with_pharmacist') {
                $conversation->messages()->create([
                    'sender_type' => 'customer',
                    'sender_id' => $user->id,
                    'message' => $message,
                ]);

                return response()->json([
                    'message' => 'Your message has been sent to the pharmacist.',
                    'role' => 'system',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Determine the type of question
            |--------------------------------------------------------------------------
            */

            $intent = $this->detectIntent(
                $message,
                $goPharmacyAi
            );

            /*
            |--------------------------------------------------------------------------
            | Build the answer
            |--------------------------------------------------------------------------
            */

            if ($intent === 'order_status') {
                $answer = $this->getOrderStatusAnswer($request);
            } else {
                $handoffRequested = $intent === 'pharmacist';

                /*
                |--------------------------------------------------------------------------
                | Basic Go Pharmacy guidance does not require Gemini.
                |--------------------------------------------------------------------------
                */

                $directAnswer = $this->getDirectAnswer($intent);

                if ($directAnswer !== null) {
                    $answer = $directAnswer;
                } elseif ($intent === 'product') {
                    /*
                    |--------------------------------------------------------------------------
                    | Product questions use the Go Pharmacy database first.
                    |--------------------------------------------------------------------------
                    */

                    $filters = $goPharmacyAi->extractProductFiltersLocally(
                        $message
                    );

                    $products = $goPharmacyAi->searchProducts($filters);

                    if ($products->isEmpty()) {
                        $answer = 'I could not find a matching product in the current Go Pharmacy catalog.';
                    } else {
                        $productLines = $products
                            ->map(function ($product) {
                                $availableQuantity =
                                    $product->inventory?->available_quantity ?? 0;

                                $prescription = $product->requires_prescription
                                    ? 'Prescription required'
                                    : 'No prescription required';

                                return $product->name
                                    . ' — ₦'
                                    . number_format((float) $product->price, 2)
                                    . ' — '
                                    . $prescription
                                    . ' — '
                                    . $availableQuantity
                                    . ' available';
                            })
                            ->implode("\n");

                        $answer = "Here are the matching products in the Go Pharmacy catalog:\n\n{$productLines}";
                    }
                } else {
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
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Save the conversation for authenticated customers.
            |--------------------------------------------------------------------------
            */

            if ($user) {
                if (!$conversation) {
                    $conversation = AiConversation::create([
                        'user_id' => $user->id,
                        'status' => 'active',
                    ]);
                }

                $conversation->messages()->create([
                    'sender_type' => 'customer',
                    'sender_id' => $user->id,
                    'message' => $message,
                ]);

                $conversation->messages()->create([
                    'sender_type' => 'assistant',
                    'sender_id' => null,
                    'message' => $answer,
                ]);

                if ($handoffRequested) {
                    $conversation->update([
                        'status' => 'waiting_for_pharmacist',
                    ]);
                }
            }

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
        $user = $request->user();

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
    private function detectIntent(
        string $message,
        GoPharmacyAiService $goPharmacyAi
    ): string {
        $message = strtolower($message);

        $intents = [
            'pharmacist' => [
                'speak to a pharmacist',
                'talk to a pharmacist',
                'contact a pharmacist',
                'connect me to a pharmacist',
                'connect me with a pharmacist',
                'i want a pharmacist',
                'i need a pharmacist',
                'ask a pharmacist',
                'human pharmacist',
                'real pharmacist',
            ],

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
                'remove it from my cart',
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
            $goPharmacyAi->isProductQuestion($message)
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

            'pharmacist' => 'I can connect this conversation to a Go Pharmacy pharmacist. Your request has been sent for pharmacist assistance. Please keep this chat open while you wait for a response.',

            default => null,
        };
    }
}
