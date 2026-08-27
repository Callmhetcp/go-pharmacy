<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page.
     */
    public function create(
        Request $request,
        Settings $settings
    ): Response|RedirectResponse {
        $cart = $request->session()->get('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $cartItems = array_values($cart);

        /*
        |--------------------------------------------------------------------------
        | Calculate checkout totals
        |--------------------------------------------------------------------------
        */

        $subtotal = collect($cartItems)->sum(
            fn ($item) => (float) ($item['subtotal'] ?? 0)
        );

        /*
        |--------------------------------------------------------------------------
        | Delivery settings
        |--------------------------------------------------------------------------
        */

        $deliveryEnabled = $settings->get(
            'delivery.enabled',
            false
        );

        $standardDeliveryFee = (float) $settings->get(
            'delivery.standard_fee',
            0
        );

        $freeDeliveryThreshold = (float) $settings->get(
            'delivery.free_threshold',
            0
        );

        $deliveryFee = 0;

        if ($deliveryEnabled) {
            $deliveryFee = $standardDeliveryFee;

            if (
                $freeDeliveryThreshold > 0 &&
                $subtotal >= $freeDeliveryThreshold
            ) {
                $deliveryFee = 0;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Discount
        |--------------------------------------------------------------------------
        */

        $discount = 0;

        /*
        |--------------------------------------------------------------------------
        | Total
        |--------------------------------------------------------------------------
        */

        $total = max(
            0,
            $subtotal + $deliveryFee - $discount
        );

        return Inertia::render('Checkout/Create', [
            'cart' => $cartItems,
            'cartSubtotal' => $subtotal,
            'deliveryFee' => $deliveryFee,
            'discount' => $discount,
            'total' => $total,
        ]);
    }

    /**
     * Create an order from checkout data.
     *
     * Payment gateway integration is NOT performed here.
     *
     * OrderService handles:
     *
     * - product validation
     * - inventory validation
     * - selling-unit conversion
     * - order totals
     * - order creation
     * - order item creation
     * - inventory reservation
     */
    public function store(
        Request $request,
        OrderService $orderService,
        Settings $settings
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Validate customer information
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'customer_name' => [
                'required',
                'string',
                'max:255',
            ],

            'customer_email' => [
                'required',
                'email',
                'max:255',
            ],

            'customer_phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'delivery_address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'delivery_city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'delivery_state' => [
                'nullable',
                'string',
                'max:100',
            ],

            'delivery_notes' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Get cart
        |--------------------------------------------------------------------------
        */

        $cart = $request->session()->get('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart.index')
                ->with(
                    'error',
                    'Your cart is empty.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate delivery fee
        |--------------------------------------------------------------------------
        */

        $cartItems = array_values($cart);

        $subtotal = collect($cartItems)->sum(
            fn ($item) => (float) ($item['subtotal'] ?? 0)
        );

        $deliveryEnabled = $settings->get(
            'delivery.enabled',
            false
        );

        $standardDeliveryFee = (float) $settings->get(
            'delivery.standard_fee',
            0
        );

        $freeDeliveryThreshold = (float) $settings->get(
            'delivery.free_threshold',
            0
        );

        $deliveryFee = 0;

        if ($deliveryEnabled) {
            $deliveryFee = $standardDeliveryFee;

            if (
                $freeDeliveryThreshold > 0 &&
                $subtotal >= $freeDeliveryThreshold
            ) {
                $deliveryFee = 0;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Add calculated values to order data
        |--------------------------------------------------------------------------
        */

        $validated['delivery_fee'] = $deliveryFee;
        $validated['discount'] = 0;

        try {
            /*
            |--------------------------------------------------------------------------
            | Create order
            |--------------------------------------------------------------------------
            |
            | OrderService handles the complete order transaction:
            |
            | Product
            |     ↓
            | Inventory validation
            |     ↓
            | Selling unit → base unit conversion
            |     ↓
            | Order
            |     ↓
            | Order items
            |     ↓
            | Inventory reservation
            |
            | No real payment gateway is initialized.
            |
            */

            $order = $orderService->createOrder(
                $validated,
                $cart
            );

            /*
            |--------------------------------------------------------------------------
            | Clear cart
            |--------------------------------------------------------------------------
            */

            $request->session()->forget('cart');

            /*
            |--------------------------------------------------------------------------
            | Continue to payment page
            |--------------------------------------------------------------------------
            |
            | The payment page is currently only a placeholder.
            |
            | No Flutterwave, Paystack, OPay, or other gateway
            | transaction is initialized.
            |
            */

            return redirect()
                ->route('payments.create', $order)
                ->with(
                    'success',
                    'Your order has been created successfully.'
                );
        } catch (Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Handle order creation failure
            |--------------------------------------------------------------------------
            */

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $exception->getMessage()
                );
        }
    }
}