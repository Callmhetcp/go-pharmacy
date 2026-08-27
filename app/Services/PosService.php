<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Support\Settings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PosService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected Settings $settings
    ) {
    }

    /**
     * Complete a walk-in POS sale.
     */
    public function createSale(array $data): Order
    {
        $items = $data['items'] ?? [];

        if (empty($items)) {
            throw new RuntimeException(
                'The POS cart is empty.'
            );
        }

        $paymentMethod = $data['payment_method'] ?? null;

        if (! in_array(
            $paymentMethod,
            ['cash', 'transfer', 'card'],
            true
        )) {
            throw new RuntimeException(
                'Invalid payment method.'
            );
        }

        /**
         * The currently authenticated admin/cashier
         * is responsible for the POS transaction.
         */
        $cashierId = Auth::id();

        if (! $cashierId) {
            throw new RuntimeException(
                'You must be logged in to process a POS sale.'
            );
        }

        return DB::transaction(function () use (
            $data,
            $items,
            $paymentMethod,
            $cashierId
        ) {
            $subtotal = 0;
            $orderItems = [];

            /*
            |--------------------------------------------------------------------------
            | Validate products and stock
            |--------------------------------------------------------------------------
            */

            foreach ($items as $cartItem) {
                $productId = (int) ($cartItem['product_id'] ?? 0);
                $quantity = (int) ($cartItem['quantity'] ?? 0);

                if ($productId < 1 || $quantity < 1) {
                    throw new RuntimeException(
                        'Invalid product information in POS cart.'
                    );
                }

                $product = Product::query()
                    ->whereKey($productId)
                    ->where('is_active', true)
                    ->with('inventory')
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    throw new RuntimeException(
                        'One of the selected products is no longer available.'
                    );
                }

                $inventory = $product->inventory;

                if (! $inventory) {
                    throw new RuntimeException(
                        "Inventory record not found for {$product->name}."
                    );
                }

                $unitsPerSellingUnit = max(
                    1,
                    (int) $product->units_per_selling_unit
                );

                $baseQuantity =
                    $quantity * $unitsPerSellingUnit;

                $availableQuantity = max(
                    0,
                    (int) $inventory->quantity
                    - (int) $inventory->reserved_quantity
                );

                if ($baseQuantity > $availableQuantity) {
                    $availableSellingQuantity = intdiv(
                        $availableQuantity,
                        $unitsPerSellingUnit
                    );

                    throw new RuntimeException(
                        "Insufficient stock for {$product->name}. "
                        . "Only {$availableSellingQuantity} "
                        . "{$product->sellingUnitLabel()}(s) available."
                    );
                }

                $unitPrice = (float) $product->price;

                $itemSubtotal =
                    $unitPrice * $quantity;

                $subtotal += $itemSubtotal;

                $orderItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $itemSubtotal,
                    'selling_unit' =>
                        $product->sellingUnitLabel(),
                    'base_unit' =>
                        $product->baseUnitLabel(),
                    'base_quantity' => $baseQuantity,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Calculate totals
            |--------------------------------------------------------------------------
            */

            $discount = max(
                0,
                (float) ($data['discount'] ?? 0)
            );

            if ($discount > $subtotal) {
                $discount = $subtotal;
            }

            $total = max(
                0,
                $subtotal - $discount
            );

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            $customer = $data['customer'] ?? [];

            /*
            |--------------------------------------------------------------------------
            | Create order
            |--------------------------------------------------------------------------
            |
            | order_number   = POS transaction identifier
            | receipt_number = Printable receipt identifier
            |
            */

            $order = Order::create([
                'order_number' =>
                    $this->generateOrderNumber(),

                'receipt_number' =>
                    $this->generateReceiptNumber(),

                'user_id' =>
                    $customer['id'] ?? null,

                'customer_name' =>
                    $customer['name']
                    ?? $data['customer_name']
                    ?? 'Walk-in Customer',

                'customer_email' =>
                    $customer['email']
                    ?? $data['customer_email']
                    ?? null,

                'customer_phone' =>
                    $customer['phone']
                    ?? $data['customer_phone']
                    ?? null,

                /*
                |--------------------------------------------------------------------------
                | Cashier
                |--------------------------------------------------------------------------
                */

                'cashier_id' => $cashierId,

                /*
                |--------------------------------------------------------------------------
                | POS orders do not require delivery
                |--------------------------------------------------------------------------
                */

                'delivery_address' => null,
                'delivery_city' => null,
                'delivery_state' => null,
                'delivery_notes' => null,

                /*
                |--------------------------------------------------------------------------
                | Financials
                |--------------------------------------------------------------------------
                */

                'subtotal' => $subtotal,
                'delivery_fee' => 0,
                'discount' => $discount,
                'total' => $total,

                /*
                |--------------------------------------------------------------------------
                | POS status
                |--------------------------------------------------------------------------
                */

                'status' => 'completed',
                'payment_status' => 'paid',

                'notes' =>
                    $data['notes']
                    ?? 'POS walk-in sale.',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create order items
            |--------------------------------------------------------------------------
            */

            foreach ($orderItems as $item) {
                $order->items()->create([
                    'product_id' =>
                        $item['product']->id,

                    'product_name' =>
                        $item['product']->name,

                    'product_sku' =>
                        $item['product']->sku,

                    'unit_price' =>
                        $item['unit_price'],

                    'quantity' =>
                        $item['quantity'],

                    'subtotal' =>
                        $item['subtotal'],

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
            | Load items before inventory fulfillment
            |--------------------------------------------------------------------------
            */

            $order->load('items.product');

            /*
            |--------------------------------------------------------------------------
            | Deduct physical stock immediately
            |--------------------------------------------------------------------------
            */

            $this->inventoryService
                ->fulfillPosSale($order);

            /*
            |--------------------------------------------------------------------------
            | Create successful POS payment
            |--------------------------------------------------------------------------
            */

            $paymentReference =
                $this->generatePaymentReference();

            Payment::create([
                'order_id' =>
                    $order->id,

                'payment_reference' =>
                    $paymentReference,

                'transaction_reference' =>
                    $paymentReference,

                'gateway' => 'pos',

                'gateway_transaction_id' => null,

                'amount' => $total,

                /*
                |--------------------------------------------------------------------------
                | Currency comes from Admin Settings
                |--------------------------------------------------------------------------
                */

                'currency' =>
                    $this->settings->get(
                        'business.currency',
                        'NGN'
                    ),

                'status' => 'successful',

                'payment_method' =>
                    $paymentMethod,

                'gateway_message' =>
                    'POS payment received.',

                'gateway_response' => [
                    'source' => 'admin_pos',
                    'cashier_id' => $cashierId,
                    'payment_method' => $paymentMethod,
                ],

                'paid_at' => now(),

                'verified_at' => now(),

                'refunded_amount' => 0,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Return complete sale
            |--------------------------------------------------------------------------
            */

            return $order->load([
                'items',
                'payments',
                'user',
                'cashier',
            ]);
        });
    }

    /**
     * Get settings required by the POS receipt.
     */
    public function receiptSettings(): array
    {
        return [
            'logo' => $this->settings->get(
                'business.logo',
                null
            ),

            'general' => [
                'business.name' => $this->settings->get(
                    'business.name',
                    ''
                ),

                'business.tagline' => $this->settings->get(
                    'business.tagline',
                    ''
                ),

                'business.phone' => $this->settings->get(
                    'business.phone',
                    ''
                ),

                'business.email' => $this->settings->get(
                    'business.email',
                    ''
                ),

                'business.address' => $this->settings->get(
                    'business.address',
                    ''
                ),

                'business.city' => $this->settings->get(
                    'business.city',
                    ''
                ),

                'business.state' => $this->settings->get(
                    'business.state',
                    ''
                ),
            ],

            'receipt' => [
                'receipt.title' => $this->settings->get(
                    'receipt.title',
                    ''
                ),

                /*
                |--------------------------------------------------------------------------
                | Receipt Number Prefix
                |--------------------------------------------------------------------------
                |
                | Controlled from Admin Settings.
                | Current database value:
                | GP-RCPT-
                |
                */

                'receipt.prefix' => $this->settings->get(
                    'receipt.prefix',
                    'GP-RCPT-'
                ),

                'receipt.order_label' => $this->settings->get(
                    'receipt.order_label',
                    'Order'
                ),

                'receipt.date_label' => $this->settings->get(
                    'receipt.date_label',
                    'Date'
                ),

                'receipt.footer' => $this->settings->get(
                    'receipt.footer',
                    ''
                ),

                'receipt.show_logo' => (bool) $this->settings->get(
                    'receipt.show_logo',
                    true
                ),

                'receipt.show_customer' => (bool) $this->settings->get(
                    'receipt.show_customer',
                    true
                ),

                'receipt.show_payment_method' => (bool) $this->settings->get(
                    'receipt.show_payment_method',
                    true
                ),

                'receipt.show_cashier' => (bool) $this->settings->get(
                    'receipt.show_cashier',
                    true
                ),

                'receipt.show_delivery_fee' => (bool) $this->settings->get(
                    'receipt.show_delivery_fee',
                    true
                ),
            ],

            'financial' => [
                'currency' => $this->settings->get(
                    'business.currency',
                    'NGN'
                ),

                'currency_symbol' => $this->settings->get(
                    'business.currency_symbol',
                    '₦'
                ),
            ],
        ];
    }

    /**
     * Generate a unique POS order number.
     *
     * This identifies the transaction as a POS order.
     *
     * Example:
     * GP-POS-20260821-ABC123
     */
    protected function generateOrderNumber(): string
    {
        do {
            $number =
                'GP-POS-'
                . now()->format('Ymd')
                . '-'
                . strtoupper(Str::random(6));
        } while (
            Order::where(
                'order_number',
                $number
            )->exists()
        );

        return $number;
    }

    /**
     * Generate a unique receipt number.
     *
     * The prefix comes from Admin Settings:
     *
     * receipt.prefix
     *
     * Example:
     * GP-RCPT-20260821-ABC123
     */
    protected function generateReceiptNumber(): string
    {
        $prefix = trim(
            (string) $this->settings->get(
                'receipt.prefix',
                'GP-RCPT-'
            )
        );

        if ($prefix === '') {
            $prefix = 'GP-RCPT-';
        }

        /*
        |--------------------------------------------------------------------------
        | Ensure the configured prefix ends with "-"
        |--------------------------------------------------------------------------
        */

        $prefix = rtrim($prefix, '-') . '-';

        do {
            $number =
                $prefix
                . now()->format('Ymd')
                . '-'
                . strtoupper(Str::random(6));
        } while (
            Order::where(
                'receipt_number',
                $number
            )->exists()
        );

        return $number;
    }

    /**
     * Generate a unique POS payment reference.
     *
     * Example:
     * GP-POS-PAY-20260821140630-ABCDE
     */
    protected function generatePaymentReference(): string
    {
        do {
            $reference =
                'GP-POS-PAY-'
                . now()->format('YmdHis')
                . '-'
                . strtoupper(Str::random(5));
        } while (
            Payment::where(
                'payment_reference',
                $reference
            )->exists()
        );

        return $reference;
    }
}