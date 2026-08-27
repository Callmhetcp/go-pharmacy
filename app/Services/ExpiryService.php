<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ExpiryService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {
    }

    /**
     * Mark a purchase batch as expired.
     *
     * IMPORTANT:
     *
     * This does NOT remove stock from inventory.
     *
     * The administrator must subsequently choose:
     *
     * - Return to supplier
     * - Dispose
     *
     * Those actions will remove the physical quantity from inventory.
     */
    public function markExpired(PurchaseItem $purchaseItem): void
    {
        DB::transaction(function () use ($purchaseItem) {

            $purchaseItem = PurchaseItem::query()
                ->with('product')
                ->lockForUpdate()
                ->findOrFail($purchaseItem->id);

            if ($purchaseItem->status === 'expired') {
                return;
            }

            if ($purchaseItem->status === 'returned') {
                throw new RuntimeException(
                    'A returned purchase batch cannot be marked as expired.'
                );
            }

            if ($purchaseItem->status === 'disposed') {
                throw new RuntimeException(
                    'A disposed purchase batch cannot be marked as expired.'
                );
            }

            if (
                ! $purchaseItem->expiry_date ||
                $purchaseItem->expiry_date->isFuture()
            ) {
                throw new RuntimeException(
                    'This purchase batch has not expired yet.'
                );
            }

            $remainingQuantity = (int) $purchaseItem->remaining_quantity;

            /*
             * If nothing remains from this batch, there is no physical
             * stock left to return or dispose.
             */
            if ($remainingQuantity <= 0) {
                $purchaseItem->update([
                    'remaining_quantity' => 0,
                    'status' => 'expired',
                ]);

                return;
            }

            /*
             * Only change the batch status here.
             *
             * Inventory is NOT deducted yet.
             *
             * The administrator will choose Return or Dispose.
             */
            $purchaseItem->update([
                'status' => 'expired',
            ]);
        });
    }

    /**
     * Return part or all of an expired purchase batch
     * to the supplier.
     */
    public function returnExpiredToSupplier(
        PurchaseItem $purchaseItem,
        int $quantity
    ): void {
        if ($quantity < 1) {
            throw new RuntimeException(
                'Return quantity must be greater than zero.'
            );
        }

        DB::transaction(function () use ($purchaseItem, $quantity) {

            $purchaseItem = PurchaseItem::query()
                ->with('product')
                ->lockForUpdate()
                ->findOrFail($purchaseItem->id);

            if ($purchaseItem->status !== 'expired') {
                throw new RuntimeException(
                    'Only an expired purchase batch can be returned using this action.'
                );
            }

            $remainingQuantity = (int) $purchaseItem->remaining_quantity;

            if ($remainingQuantity <= 0) {
                throw new RuntimeException(
                    'There is no remaining quantity available for return.'
                );
            }

            if ($quantity > $remainingQuantity) {
                throw new RuntimeException(
                    "You cannot return {$quantity} units. "
                    . "Only {$remainingQuantity} units remain in this expired batch."
                );
            }

            $inventory = Inventory::query()
                ->where('product_id', $purchaseItem->product_id)
                ->lockForUpdate()
                ->first();

            if (! $inventory) {
                throw new RuntimeException(
                    "Inventory record for {$purchaseItem->product->name} could not be found."
                );
            }

            $quantityBefore = (int) $inventory->quantity;

            if ($quantity > $quantityBefore) {
                throw new RuntimeException(
                    'Return quantity exceeds current inventory.'
                );
            }

            /*
             * Expired stock should never remain reserved.
             */
            $reservedQuantity = (int) $inventory->reserved_quantity;

            if ($reservedQuantity > 0) {
                $releaseQuantity = min(
                    $quantity,
                    $reservedQuantity
                );

                $inventory->decrement(
                    'reserved_quantity',
                    $releaseQuantity
                );
            }

            $this->inventoryService->removeStock(
                $inventory,
                $quantity,
                'purchase_return',
                $purchaseItem->batch_number,
                sprintf(
                    'Expired purchase batch returned to supplier. Batch: %s. Expiry date: %s.',
                    $purchaseItem->batch_number ?: 'N/A',
                    $purchaseItem->expiry_date?->format('Y-m-d') ?: 'N/A'
                )
            );

            $newRemainingQuantity = $remainingQuantity - $quantity;

            $status = $newRemainingQuantity <= 0
                ? 'returned'
                : 'expired';

            $purchaseItem->update([
                'remaining_quantity' => $newRemainingQuantity,
                'status' => $status,
            ]);
        });
    }

    /**
     * Dispose part or all of an expired purchase batch.
     *
     * Used when the supplier refuses to collect the expired stock.
     */
    public function disposeExpired(
        PurchaseItem $purchaseItem,
        int $quantity
    ): void {
        if ($quantity < 1) {
            throw new RuntimeException(
                'Disposal quantity must be greater than zero.'
            );
        }

        DB::transaction(function () use ($purchaseItem, $quantity) {

            $purchaseItem = PurchaseItem::query()
                ->with('product')
                ->lockForUpdate()
                ->findOrFail($purchaseItem->id);

            if ($purchaseItem->status !== 'expired') {
                throw new RuntimeException(
                    'Only an expired purchase batch can be disposed.'
                );
            }

            $remainingQuantity = (int) $purchaseItem->remaining_quantity;

            if ($remainingQuantity <= 0) {
                throw new RuntimeException(
                    'There is no remaining quantity available for disposal.'
                );
            }

            if ($quantity > $remainingQuantity) {
                throw new RuntimeException(
                    "You cannot dispose {$quantity} units. "
                    . "Only {$remainingQuantity} units remain in this expired batch."
                );
            }

            $inventory = Inventory::query()
                ->where('product_id', $purchaseItem->product_id)
                ->lockForUpdate()
                ->first();

            if (! $inventory) {
                throw new RuntimeException(
                    "Inventory record for {$purchaseItem->product->name} could not be found."
                );
            }

            $quantityBefore = (int) $inventory->quantity;

            if ($quantity > $quantityBefore) {
                throw new RuntimeException(
                    'Disposal quantity exceeds current inventory.'
                );
            }

            /*
             * Expired stock should never remain reserved.
             */
            $reservedQuantity = (int) $inventory->reserved_quantity;

            if ($reservedQuantity > 0) {
                $releaseQuantity = min(
                    $quantity,
                    $reservedQuantity
                );

                $inventory->decrement(
                    'reserved_quantity',
                    $releaseQuantity
                );
            }

            $this->inventoryService->removeStock(
                $inventory,
                $quantity,
                'expired_disposal',
                $purchaseItem->batch_number,
                sprintf(
                    'Expired stock disposed because it was not returned to supplier. Batch: %s. Expiry date: %s.',
                    $purchaseItem->batch_number ?: 'N/A',
                    $purchaseItem->expiry_date?->format('Y-m-d') ?: 'N/A'
                )
            );

            $newRemainingQuantity = $remainingQuantity - $quantity;

            $status = $newRemainingQuantity <= 0
                ? 'disposed'
                : 'expired';

            $purchaseItem->update([
                'remaining_quantity' => $newRemainingQuantity,
                'status' => $status,
            ]);
        });
    }

    /**
     * Return part or all of a normal purchase batch
     * to the supplier before expiry.
     */
    public function returnToSupplier(
        PurchaseItem $purchaseItem,
        int $quantity
    ): void {
        if ($quantity < 1) {
            throw new RuntimeException(
                'Return quantity must be greater than zero.'
            );
        }

        DB::transaction(function () use ($purchaseItem, $quantity) {

            $purchaseItem = PurchaseItem::query()
                ->with('product')
                ->lockForUpdate()
                ->findOrFail($purchaseItem->id);

            if ($purchaseItem->status !== 'active') {
                throw new RuntimeException(
                    'Only an active purchase batch can be returned through the normal purchase return action.'
                );
            }

            $remainingQuantity = (int) $purchaseItem->remaining_quantity;

            if ($remainingQuantity <= 0) {
                throw new RuntimeException(
                    'There is no remaining quantity available for return.'
                );
            }

            if ($quantity > $remainingQuantity) {
                throw new RuntimeException(
                    "You cannot return {$quantity} units. "
                    . "Only {$remainingQuantity} units remain in this purchase batch."
                );
            }

            $inventory = Inventory::query()
                ->where('product_id', $purchaseItem->product_id)
                ->lockForUpdate()
                ->first();

            if (! $inventory) {
                throw new RuntimeException(
                    "Inventory record for {$purchaseItem->product->name} could not be found."
                );
            }

            $quantityBefore = (int) $inventory->quantity;

            if ($quantity > $quantityBefore) {
                throw new RuntimeException(
                    'Return quantity exceeds current inventory.'
                );
            }

            $this->inventoryService->removeStock(
                $inventory,
                $quantity,
                'purchase_return',
                $purchaseItem->batch_number,
                sprintf(
                    'Purchase batch returned to supplier. Batch: %s. Expiry date: %s.',
                    $purchaseItem->batch_number ?: 'N/A',
                    $purchaseItem->expiry_date?->format('Y-m-d') ?: 'N/A'
                )
            );

            $newRemainingQuantity = $remainingQuantity - $quantity;

            $status = match (true) {
                $newRemainingQuantity <= 0 => 'returned',

                $newRemainingQuantity < (int) $purchaseItem->quantity
                    => 'partially_returned',

                default => 'active',
            };

            $purchaseItem->update([
                'remaining_quantity' => $newRemainingQuantity,
                'status' => $status,
            ]);
        });
    }
}