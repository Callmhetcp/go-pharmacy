<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\User;
use App\Services\OrderService;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $user = $request->user();

        $savedAddresses = $user
            ? $user->addresses()
                ->orderByDesc('is_default')
                ->latest()
                ->get([
                    'id',
                    'label',
                    'recipient_name',
                    'phone',
                    'address',
                    'city',
                    'state',
                    'country',
                    'postal_code',
                    'delivery_notes',
                    'is_default',
                ])
            : collect();

        return Inertia::render('Checkout/Create', [
            'cart' => $cartItems,
            'cartSubtotal' => $subtotal,
            'deliveryFee' => $deliveryFee,
            'discount' => $discount,
            'total' => $total,
            'user' => $user?->only([
                'id',
                'name',
                'email',
                'phone',
            ]),
            'savedAddresses' => $savedAddresses,
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

            'save_address' => [
                'sometimes',
                'boolean',
            ],

            'saved_address_id' => [
                'nullable',
                'integer',
            ],

            'update_saved_address' => [
                'sometimes',
                'boolean',
            ],

            'address_label' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        if (
            $request->boolean('save_address') &&
            ! $request->user()
        ) {
            abort(403, 'Sign in before saving delivery details.');
        }

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

            $order = DB::transaction(function () use (
                $request,
                $validated,
                $cart,
                $orderService
            ) {
                $order = $orderService->createOrder(
                    $validated,
                    $cart
                );

                if ($request->boolean('save_address')) {
                    $this->saveDeliveryAddress(
                        $request->user(),
                        $validated,
                        $request->boolean('update_saved_address')
                    );
                }

                return $order;
            });

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

    /**
     * Save a new delivery address or explicitly update the selected one.
     * Orders retain their own delivery fields, so later address edits do not
     * alter an order that has already been placed.
     */
    private function saveDeliveryAddress(
        User $user,
        array $data,
        bool $updateSelectedAddress
    ): Address {
        $addressData = [
            'label' => $data['address_label'] ?? 'Delivery address',
            'recipient_name' => $data['customer_name'],
            'phone' => $data['customer_phone'] ?? '',
            'address' => $data['delivery_address'] ?? '',
            'city' => $data['delivery_city'] ?? '',
            'state' => $data['delivery_state'] ?? '',
            'country' => 'Nigeria',
            'delivery_notes' => $data['delivery_notes'] ?? null,
        ];

        if (
            blank($addressData['phone']) ||
            blank($addressData['address']) ||
            blank($addressData['city']) ||
            blank($addressData['state'])
        ) {
            abort(
                422,
                'Phone number and complete delivery address are required to save delivery details.'
            );
        }

        if (! empty($data['saved_address_id'])) {
            $address = $user->addresses()
                ->whereKey($data['saved_address_id'])
                ->firstOrFail();

            if ($updateSelectedAddress) {
                $address->update($addressData);
            }

            return $address;
        }

        $addressData['is_default'] = ! $user->addresses()->exists();

        return $user->addresses()->create($addressData);
    }
}
