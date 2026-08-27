<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Support\Settings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class OrderService
{
    protected InventoryService $inventoryService;

    protected Settings $settings;

    public function __construct(
        InventoryService $inventoryService,
        Settings $settings
    ) {
        $this->inventoryService = $inventoryService;
        $this->settings = $settings;
    }

    /**
     * Create an order from checkout data.
     *
     * IMPORTANT:
     * No payment is initialized or created here.
     *
     * Payment gateway integration will only be added after
     * the Go Pharmacy client approves the website and the
     * payment provider has been selected/configured.
     *
     * Packaging rules:
     *
     * - Cart quantity = selling units
     * - Inventory quantity = base units
     * - Product price = price of one selling unit
     *
     * Example:
     *
     * base_unit = tablet
     * selling_unit = pack
     * units_per_selling_unit = 10
     * price = ₦2,000
     *
     * Customer buys 2 packs:
     *
     * - quantity = 2
     * - base_quantity = 20 tablets
     * - subtotal = ₦4,000
     */
    public function createOrder(array $data, array $cart): Order
    {
        if (empty($cart)) {
            throw new RuntimeException('Your cart is empty.');
        }

        return DB::transaction(function () use ($data, $cart) {
            $subtotal = 0;
            $items = [];

            /*
            |--------------------------------------------------------------------------
            | Validate cart products and inventory
            |--------------------------------------------------------------------------
            */

            foreach ($cart as $item) {
                $productId = $item['product_id']
                    ?? $item['id']
                    ?? null;

                $quantity = (int) ($item['quantity'] ?? 0);

                if (!$productId || $quantity < 1) {
                    throw new RuntimeException(
                        'Invalid product information in cart.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Load active product
                |--------------------------------------------------------------------------
                */

                $product = Product::query()
                    ->whereKey($productId)
                    ->where('is_active', true)
                    ->first();

                if (!$product) {
                    throw new RuntimeException(
                        'One of the products in your cart is no longer available.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Lock inventory
                |--------------------------------------------------------------------------
                |
                | Prevent concurrent orders from consuming the same
                | available inventory.
                |
                */

                $inventory = $product->inventory()
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) {
                    throw new RuntimeException(
                        "Inventory record not found for {$product->name}."
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Product units
                |--------------------------------------------------------------------------
                */

                $baseUnit = $product->base_unit ?: 'piece';

                $sellingUnit = $product->selling_unit ?: 'piece';

                $unitsPerSellingUnit = max(
                    1,
                    (int) $product->units_per_selling_unit
                );

                /*
                |--------------------------------------------------------------------------
                | Convert selling quantity to base inventory quantity
                |--------------------------------------------------------------------------
                */

                $baseQuantity =
                    $quantity * $unitsPerSellingUnit;

                /*
                |--------------------------------------------------------------------------
                | Calculate available inventory
                |--------------------------------------------------------------------------
                */

                $availableBaseQuantity = max(
                    0,
                    (int) $inventory->quantity
                    - (int) $inventory->reserved_quantity
                );

                /*
                |--------------------------------------------------------------------------
                | Check stock
                |--------------------------------------------------------------------------
                */

                if ($baseQuantity > $availableBaseQuantity) {
                    $availableSellingQuantity = intdiv(
                        $availableBaseQuantity,
                        $unitsPerSellingUnit
                    );

                    throw new RuntimeException(
                        "Insufficient stock for {$product->name}. "
                        . "Only {$availableSellingQuantity} "
                        . "{$sellingUnit}(s) available."
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Calculate item price
                |--------------------------------------------------------------------------
                */

                $unitPrice = (float) $product->price;

                $itemSubtotal = $unitPrice * $quantity;

                $subtotal += $itemSubtotal;

                /*
                |--------------------------------------------------------------------------
                | Prepare order item
                |--------------------------------------------------------------------------
                */

                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $itemSubtotal,
                    'selling_unit' => $sellingUnit,
                    'base_unit' => $baseUnit,
                    'base_quantity' => $baseQuantity,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Delivery and discount
            |--------------------------------------------------------------------------
            */

            $deliveryEnabled = $this->settings->get(
                'delivery.enabled',
                true
            );

            $standardDeliveryFee = (float) $this->settings->get(
                'delivery.standard_fee',
                0
            );

            $freeDeliveryThreshold = (float) $this->settings->get(
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

            /*
            |--------------------------------------------------------------------------
            | Create order
            |--------------------------------------------------------------------------
            */

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),

                'user_id' => Auth::id(),

                'customer_name' => $data['customer_name'],

                'customer_email' => $data['customer_email'],

                'customer_phone' =>
                    $data['customer_phone'] ?? null,

                'delivery_address' =>
                    $data['delivery_address'] ?? null,

                'delivery_city' =>
                    $data['delivery_city'] ?? null,

                'delivery_state' =>
                    $data['delivery_state'] ?? null,

                'delivery_notes' =>
                    $data['delivery_notes'] ?? null,

                'subtotal' => $subtotal,

                'delivery_fee' => $deliveryFee,

                'discount' => $discount,

                'total' => $total,

                'status' => 'pending',

                /*
                |--------------------------------------------------------------------------
                | Payment status
                |--------------------------------------------------------------------------
                |
                | The order has NOT been paid.
                | No payment gateway has been initialized.
                |
                */

                'payment_status' => 'unpaid',

                'notes' => $data['notes'] ?? null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create order items
            |--------------------------------------------------------------------------
            */

            foreach ($items as $item) {
                $order->items()->create([
                    /*
                    |--------------------------------------------------------------------------
                    | Product snapshot
                    |--------------------------------------------------------------------------
                    */

                    'product_id' =>
                        $item['product']->id,

                    'product_name' =>
                        $item['product']->name,

                    'product_sku' =>
                        $item['product']->sku,

                    /*
                    |--------------------------------------------------------------------------
                    | Pricing snapshot
                    |--------------------------------------------------------------------------
                    */

                    'unit_price' =>
                        $item['unit_price'],

                    'quantity' =>
                        $item['quantity'],

                    'subtotal' =>
                        $item['subtotal'],

                    /*
                    |--------------------------------------------------------------------------
                    | Unit snapshot
                    |--------------------------------------------------------------------------
                    */

                    'selling_unit' =>
                        $item['selling_unit'],

                    'base_unit' =>
                        $item['base_unit'],

                    'base_quantity' =>
                        $item['base_quantity'],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Reserve inventory
            |--------------------------------------------------------------------------
            |
            | InventoryService is responsible for reserving the required
            | base quantity for this order.
            |
            */

            $order->load('items');

            $this->inventoryService->reserveOrder($order);

            /*
            |--------------------------------------------------------------------------
            | IMPORTANT: NO PAYMENT RECORD HERE
            |--------------------------------------------------------------------------
            |
            | Do NOT create a Payment model here.
            |
            | Payment gateway integration will be added only after:
            |
            | 1. Client approval
            | 2. Gateway selection
            | 3. Gateway credentials/configuration
            | 4. Payment workflow implementation
            |
            */

            return $order->load('items');
        });
    }

    /**
     * Cancel an order and release its inventory reservations.
     *
     * IMPORTANT:
     *
     * Only orders that have not been fulfilled or cancelled
     * may be cancelled.
     *
     * Payment is not processed or refunded here because the
     * current Go Pharmacy payment system is still a placeholder.
     */
    public function cancelOrder(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            /**
             * Lock the order to prevent concurrent cancellation
             * or fulfillment operations.
             */
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            /**
             * Prevent duplicate cancellation.
             */
            if ($order->status === 'cancelled') {
                throw new RuntimeException(
                    'This order has already been cancelled.'
                );
            }

            /**
             * Orders that have already been fulfilled cannot
             * be cancelled because their physical inventory
             * has already been deducted.
             */
            if (
                    in_array(
                        $order->status,
                        [
                            'processing',
                            'ready',
                            'shipped',
                            'completed',
                        ],
                        true
                    )
                ) {
                throw new RuntimeException(
                    'This order can no longer be cancelled because fulfillment has already started.'
                );
            }

            /**
             * Release all inventory reservations.
             */
            $order->load('items');

            $this->inventoryService->releaseOrder($order);

            /**
             * Mark the order as cancelled.
             *
             * Payment remains untouched because the current
             * payment system is only a placeholder.
             */
            $order->update([
                'status' => 'cancelled',
            ]);

            return $order->fresh([
                'items',
                'payments',
            ]);
        });
    }


    /**
     * Generate a unique order number.
     */
   protected function generateOrderNumber(): string
    {
        $prefix = $this->settings->get(
            'orders.prefix',
            'GP-'
        );

        $prefix = trim($prefix);

        do {
            $number =
                $prefix
                . now()->format('Ymd')
                . '-'
                . strtoupper(Str::random(6));
        } while (
            Order::where('order_number', $number)->exists()
        );

        return $number;
    }
}