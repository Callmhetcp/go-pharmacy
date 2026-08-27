<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService
    ) {
    }

    /**
     * Create an order from the current cart.
     *
     * Payment is intentionally NOT initialized here.
     */
    public function store(Request $request): JsonResponse
    {
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
                'required',
                'string',
                'max:50',
            ],

            'delivery_address' => [
                'required',
                'string',
            ],

            'delivery_city' => [
                'nullable',
                'string',
                'max:255',
            ],

            'delivery_state' => [
                'nullable',
                'string',
                'max:255',
            ],

            'delivery_notes' => [
                'nullable',
                'string',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        try {
            /*
            |--------------------------------------------------------------------------
            | Get the raw server-side cart
            |--------------------------------------------------------------------------
            |
            | Never trust product prices, quantities, or totals supplied
            | by the frontend.
            |
            */

            $cart = $this->getStoredCart();

            if (empty($cart)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty.',
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Create order
            |--------------------------------------------------------------------------
            |
            | OrderService performs:
            |
            | - product validation
            | - inventory locking
            | - stock validation
            | - selling/base-unit conversion
            | - price calculation
            | - order creation
            | - order item creation
            | - inventory reservation
            |
            */

            $order = $this->orderService->createOrder(
                $validated,
                $cart
            );

            /*
            |--------------------------------------------------------------------------
            | Clear cart after successful order creation
            |--------------------------------------------------------------------------
            */

            $this->cartService->clear();

            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully.',
                'data' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,

                    'customer' => [
                        'name' => $order->customer_name,
                        'email' => $order->customer_email,
                        'phone' => $order->customer_phone,
                    ],

                    'delivery' => [
                        'address' => $order->delivery_address,
                        'city' => $order->delivery_city,
                        'state' => $order->delivery_state,
                        'notes' => $order->delivery_notes,
                    ],

                    'amounts' => [
                        'subtotal' => $order->subtotal,
                        'delivery_fee' => $order->delivery_fee,
                        'discount' => $order->discount,
                        'total' => $order->total,
                    ],

                    'items' => $order->items->map(
                        function ($item) {
                            return [
                                'id' => $item->id,
                                'product_id' => $item->product_id,
                                'product_name' => $item->product_name,
                                'product_sku' => $item->product_sku,
                                'unit_price' => $item->unit_price,
                                'quantity' => $item->quantity,
                                'subtotal' => $item->subtotal,
                                'selling_unit' => $item->selling_unit,
                                'base_unit' => $item->base_unit,
                                'base_quantity' => $item->base_quantity,
                            ];
                        }
                    )->values(),
                ],
            ], 201);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get the raw cart stored in the session.
     */
    protected function getStoredCart(): array
    {
        return session('go_pharmacy_cart', []);
    }
}