<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
   
    /**
     * Reserve inventory for an order atomically.
     *
     * IMPORTANT:
     *
     * The entire reservation must succeed or fail as one transaction.
     *
     * If any item cannot be reserved:
     *
     * - No reservation is retained.
     * - Physical stock is unchanged.
     *
     * This prevents partial reservations on multi-item orders.
     */
    public function reserveOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->loadMissing('items.product');

            foreach ($order->items as $item) {
                $this->reserveOrderItem($item);
            }
        });
    }

    /**
     * Reserve inventory for one order item.
     */
    public function reserveOrderItem(OrderItem $item): void
    {
        $inventory = Inventory::query()
            ->where('product_id', $item->product_id)
            ->lockForUpdate()
            ->first();

        if (! $inventory) {
            throw new RuntimeException(
                "Inventory record for {$item->product_name} could not be found."
            );
        }

        $baseQuantity = (int) $item->base_quantity;

        if ($baseQuantity < 1) {
            throw new RuntimeException(
                "Invalid inventory quantity for {$item->product_name}."
            );
        }

        $availableQuantity = max(
            0,
            (int) $inventory->quantity
                - (int) $inventory->reserved_quantity
        );

        if ($baseQuantity > $availableQuantity) {
            $sellingUnit = $item->selling_unit ?: 'unit';

            $unitsPerSellingUnit = max(
                1,
                (int) ($item->product?->units_per_selling_unit ?? 1)
            );

            $availableSellingUnits = intdiv(
                $availableQuantity,
                $unitsPerSellingUnit
            );

            throw new RuntimeException(
                "Insufficient stock for {$item->product_name}. "
                . "Only {$availableSellingUnits} "
                . "{$sellingUnit}(s) available."
            );
        }

        $inventory->increment(
            'reserved_quantity',
            $baseQuantity
        );
    }

    /**
     * Release all reservations belonging to an order.
     *
     * Used when an order is cancelled or otherwise
     * will not be fulfilled.
     */
    public function releaseOrder(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            $this->releaseOrderItem($item);
        }
    }

    /**
     * Release the reservation for one order item.
     */
    public function releaseOrderItem(OrderItem $item): void
    {
        $inventory = Inventory::query()
            ->where('product_id', $item->product_id)
            ->lockForUpdate()
            ->first();

        if (! $inventory) {
            return;
        }

        $baseQuantity = (int) $item->base_quantity;

        if ($baseQuantity < 1) {
            return;
        }

        $reservedQuantity = (int) $inventory->reserved_quantity;

        if ($reservedQuantity <= 0) {
            return;
        }

        $releaseQuantity = min(
            $baseQuantity,
            $reservedQuantity
        );

        $inventory->decrement(
            'reserved_quantity',
            $releaseQuantity
        );
    }

    /**
     * Fulfill an order atomically.
     *
     * IMPORTANT:
     *
     * The entire fulfillment must succeed or fail as one transaction.
     *
     * If any item cannot be fulfilled:
     *
     * - No stock is deducted.
     * - No reservation is released.
     * - No inventory transaction is created.
     *
     * This protects against partial fulfillment of multi-item orders.
     */
    public function fulfillOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {

            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            /**
             * Payment must be successful before physical stock
             * can be deducted.
             */
            if ($order->payment_status !== 'paid') {
                throw new RuntimeException(
                    'This order cannot be fulfilled because payment has not been completed.'
                );
            }

            /**
             * Cancelled orders must never be fulfilled.
             */
            if ($order->status === 'cancelled') {
                throw new RuntimeException(
                    'This order has been cancelled and cannot be fulfilled.'
                );
            }

            /**
             * Prevent duplicate fulfillment.
             */
            if (in_array(
                $order->status,
                ['processing', 'ready', 'shipped', 'completed'],
                true
            )) {
                throw new RuntimeException(
                    'This order has already been fulfilled and cannot be fulfilled again.'
                );
            }

            $order->loadMissing('items.product');

            /**
             * IMPORTANT:
             *
             * Validate every item BEFORE changing any inventory.
             *
             * This prevents item #1 from being deducted when
             * item #2 later fails.
             */
            foreach ($order->items as $item) {

                $inventory = Inventory::query()
                    ->where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                if (! $inventory) {
                    throw new RuntimeException(
                        "Inventory record for {$item->product_name} could not be found."
                    );
                }

                $baseQuantity = (int) $item->base_quantity;

                if ($baseQuantity < 1) {
                    throw new RuntimeException(
                        "Invalid inventory quantity for {$item->product_name}."
                    );
                }

                $quantityBefore = (int) $inventory->quantity;

                if ($baseQuantity > $quantityBefore) {
                    throw new RuntimeException(
                        "Insufficient physical stock for {$item->product_name}."
                    );
                }
            }

            /**
             * All items have now passed validation.
             *
             * Only after this point do we actually modify inventory.
             */
            foreach ($order->items as $item) {
                $this->fulfillOrderItem($item, $order);
            }

            $order->update([
                'status' => 'processing',
            ]);
        });
    }
    /**
     * Fulfill one order item.
     */
    public function fulfillOrderItem(
        OrderItem $item,
        Order $order
    ): void {
        $inventory = Inventory::query()
            ->where('product_id', $item->product_id)
            ->lockForUpdate()
            ->first();

        if (! $inventory) {
            throw new RuntimeException(
                "Inventory record for {$item->product_name} could not be found."
            );
        }

        $baseQuantity = (int) $item->base_quantity;

        if ($baseQuantity < 1) {
            throw new RuntimeException(
                "Invalid inventory quantity for {$item->product_name}."
            );
        }

        $quantityBefore = (int) $inventory->quantity;

        if ($baseQuantity > $quantityBefore) {
            throw new RuntimeException(
                "Insufficient physical stock for {$item->product_name}."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Remove physical stock
        |--------------------------------------------------------------------------
        */

        $inventory->decrement(
            'quantity',
            $baseQuantity
        );

        /*
        |--------------------------------------------------------------------------
        | Remove reservation
        |--------------------------------------------------------------------------
        */

        $reservedQuantity = (int) $inventory->reserved_quantity;

        $reservationToRelease = min(
            $baseQuantity,
            $reservedQuantity
        );

        if ($reservationToRelease > 0) {
            $inventory->decrement(
                'reserved_quantity',
                $reservationToRelease
            );
        }

        $quantityAfter = $quantityBefore - $baseQuantity;

        /*
        |--------------------------------------------------------------------------
        | Record inventory transaction
        |--------------------------------------------------------------------------
        */

        InventoryTransaction::create([
            'product_id' => $item->product_id,
            'inventory_id' => $inventory->id,
            'user_id' => Auth::id(),
            'type' => 'online_sale',
            'quantity' => -$baseQuantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'reference' => $order->order_number,
            'notes' => sprintf(
                'Online sale: %d %s (%d %s).',
                $item->quantity,
                $item->selling_unit ?: 'unit',
                $baseQuantity,
                $item->base_unit ?: 'unit'
            ),
        ]);
    }

    /**
     * Add stock to inventory.
     *
     * Quantity must always be supplied in BASE UNITS.
     */
    public function addStock(
        Inventory $inventory,
        int $quantity,
        string $type = 'purchase',
        ?string $reference = null,
        ?string $notes = null
    ): void {
        if ($quantity < 1) {
            throw new RuntimeException(
                'Stock quantity must be greater than zero.'
            );
        }

        $inventory = Inventory::query()
            ->whereKey($inventory->id)
            ->lockForUpdate()
            ->firstOrFail();

        $quantityBefore = (int) $inventory->quantity;

        $inventory->increment(
            'quantity',
            $quantity
        );

        InventoryTransaction::create([
            'product_id' => $inventory->product_id,
            'inventory_id' => $inventory->id,
            'user_id' => Auth::id(),
            'type' => $type,
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityBefore + $quantity,
            'reference' => $reference,
            'notes' => $notes,
        ]);
    }

    /**
     * Remove stock manually.
     *
     * Quantity must always be supplied in BASE UNITS.
     */
    public function removeStock(
        Inventory $inventory,
        int $quantity,
        string $type = 'adjustment',
        ?string $reference = null,
        ?string $notes = null
    ): void {
        if ($quantity < 1) {
            throw new RuntimeException(
                'Stock quantity must be greater than zero.'
            );
        }

        $inventory = Inventory::query()
            ->whereKey($inventory->id)
            ->lockForUpdate()
            ->firstOrFail();

        $quantityBefore = (int) $inventory->quantity;

        $availableQuantity = max(
            0,
            $quantityBefore - (int) $inventory->reserved_quantity
        );

        if ($quantity > $availableQuantity) {
            throw new RuntimeException(
                'Cannot remove stock that is currently reserved or unavailable.'
            );
        }

        $inventory->decrement(
            'quantity',
            $quantity
        );

        InventoryTransaction::create([
            'product_id' => $inventory->product_id,
            'inventory_id' => $inventory->id,
            'user_id' => Auth::id(),
            'type' => $type,
            'quantity' => -$quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityBefore - $quantity,
            'reference' => $reference,
            'notes' => $notes,
        ]);
    }

    /**
     * Fulfill a POS sale item immediately.
     *
     * POS sales do not use reservations.
     * Stock is deducted directly from available inventory.
     */
    public function fulfillPosSaleItem(
        OrderItem $item,
        Order $order
    ): void {
        $inventory = Inventory::query()
            ->where('product_id', $item->product_id)
            ->lockForUpdate()
            ->first();

        if (! $inventory) {
            throw new RuntimeException(
                "Inventory record for {$item->product_name} could not be found."
            );
        }

        $baseQuantity = (int) $item->base_quantity;

        if ($baseQuantity < 1) {
            throw new RuntimeException(
                "Invalid inventory quantity for {$item->product_name}."
            );
        }

        $quantityBefore = (int) $inventory->quantity;

        $reservedQuantity = (int) $inventory->reserved_quantity;

        $availableQuantity = max(
            0,
            $quantityBefore - $reservedQuantity
        );

        if ($baseQuantity > $availableQuantity) {
            $sellingUnit = $item->selling_unit ?: 'unit';

            $unitsPerSellingUnit = max(
                1,
                (int) ($item->product?->units_per_selling_unit ?? 1)
            );

            $availableSellingUnits = intdiv(
                $availableQuantity,
                $unitsPerSellingUnit
            );

            throw new RuntimeException(
                "Insufficient stock for {$item->product_name}. "
                . "Only {$availableSellingUnits} "
                . "{$sellingUnit}(s) available."
            );
        }

        $inventory->decrement(
            'quantity',
            $baseQuantity
        );

        InventoryTransaction::create([
            'product_id' => $item->product_id,
            'inventory_id' => $inventory->id,
            'user_id' => Auth::id(),
            'type' => 'pos_sale',
            'quantity' => -$baseQuantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityBefore - $baseQuantity,
            'reference' => $order->order_number,
            'notes' => sprintf(
                'POS sale: %d %s (%d %s).',
                $item->quantity,
                $item->selling_unit ?: 'unit',
                $baseQuantity,
                $item->base_unit ?: 'unit'
            ),
        ]);
    }

    /**
     * Fulfill all items in a POS sale.
     */
    public function fulfillPosSale(Order $order): void
    {
        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            $this->fulfillPosSaleItem($item, $order);
        }
    }
}