<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseItem;
use App\Services\ExpiryService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpiryReminderController extends Controller
{
    /**
     * Display expired and soon-to-expire purchase batches.
     */
    public function index(): Response
    {
        $today = Carbon::today();

        /*
        |--------------------------------------------------------------------------
        | All expired batches
        |--------------------------------------------------------------------------
        |
        | Show every purchase batch whose expiry date has passed.
        | This intentionally includes batches with zero remaining quantity
        | so the admin can see the complete expiry history.
        |
        */
        $expiredProducts = PurchaseItem::query()
            ->with([
                'product:id,name,sku',
                'purchase:id,reference,supplier_id,purchase_date',
                'purchase.supplier:id,name,company_name',
            ])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', $today)
            ->orderBy('expiry_date')
            ->get()
            ->map(
                fn (PurchaseItem $item) => $this->formatExpiredItem(
                    $item,
                    $today
                )
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Expiring within 7 days
        |--------------------------------------------------------------------------
        |
        | Only show stock that is still available.
        |
        */
        $expiringSoon = PurchaseItem::query()
            ->with([
                'product:id,name,sku',
                'purchase:id,reference,supplier_id,purchase_date',
                'purchase.supplier:id,name,company_name',
            ])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $today)
            ->whereDate(
                'expiry_date',
                '<=',
                $today->copy()->addDays(7)
            )
            ->where('remaining_quantity', '>', 0)
            ->orderBy('expiry_date')
            ->get()
            ->map(
                fn (PurchaseItem $item) => $this->formatExpiringItem(
                    $item,
                    $today
                )
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Expiring within 30 days
        |--------------------------------------------------------------------------
        |
        | Only show stock that is still available.
        |
        */
        $expiringWithin30Days = PurchaseItem::query()
            ->with([
                'product:id,name,sku',
                'purchase:id,reference,supplier_id,purchase_date',
                'purchase.supplier:id,name,company_name',
            ])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $today)
            ->whereDate(
                'expiry_date',
                '<=',
                $today->copy()->addDays(30)
            )
            ->where('remaining_quantity', '>', 0)
            ->orderBy('expiry_date')
            ->get()
            ->map(
                fn (PurchaseItem $item) => $this->formatExpiringItem(
                    $item,
                    $today
                )
            )
            ->values();

        return Inertia::render('Admin/ExpiryReminder/Index', [
            'expiredProducts' => $expiredProducts,
            'expiringSoon' => $expiringSoon,
            'expiringWithin30Days' => $expiringWithin30Days,

            'summary' => [
                'expired' => $expiredProducts->count(),
                'expiring_7_days' => $expiringSoon->count(),
                'expiring_30_days' => $expiringWithin30Days->count(),
            ],
        ]);
    }

    /**
     * Mark an expired purchase batch as expired.
     */
    public function markExpired(
        PurchaseItem $purchaseItem,
        ExpiryService $expiryService
    ): RedirectResponse {
        $expiryService->markExpired($purchaseItem);

        return back()->with(
            'success',
            'Expired batch has been removed from inventory.'
        );
    }

    /**
     * Return part or all of a purchase batch to the supplier.
     */
    public function returnToSupplier(
        Request $request,
        PurchaseItem $purchaseItem,
        ExpiryService $expiryService
    ): RedirectResponse {
        $validated = $request->validate([
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $expiryService->returnToSupplier(
            $purchaseItem,
            (int) $validated['quantity']
        );

        return back()->with(
            'success',
            'Purchase batch has been returned to the supplier.'
        );
    }

    /**
     * Format an expired purchase item for the frontend.
     */
    private function formatExpiredItem(
        PurchaseItem $item,
        Carbon $today
    ): array {
        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product?->name ?? 'Unknown product',
            'sku' => $item->product?->sku,

            /*
             * Remaining quantity can legitimately be zero here because
             * expiredProducts contains the complete expiry history.
             */
            'quantity' => (int) $item->remaining_quantity,
            'original_quantity' => (int) $item->quantity,
            'returned_quantity' => $item->returned_quantity,

            'batch_number' => $item->batch_number,

            'expiry_date' => $item->expiry_date?->format('Y-m-d'),

            'days_expired' => $item->expiry_date
                ? $item->expiry_date->diffInDays($today)
                : null,

            'status' => $item->status,

            'purchase_reference' => $item->purchase?->reference,

            'purchase_date' => $item->purchase?->purchase_date?->format('Y-m-d'),

            'supplier_name' => $item->purchase?->supplier?->company_name
                ?? $item->purchase?->supplier?->name
                ?? 'Unknown supplier',
        ];
    }

    /**
     * Format an expiring purchase item for the frontend.
     */
    private function formatExpiringItem(
        PurchaseItem $item,
        Carbon $today
    ): array {
        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product?->name ?? 'Unknown product',
            'sku' => $item->product?->sku,

            'quantity' => (int) $item->remaining_quantity,
            'original_quantity' => (int) $item->quantity,
            'returned_quantity' => $item->returned_quantity,

            'batch_number' => $item->batch_number,

            'expiry_date' => $item->expiry_date?->format('Y-m-d'),

            'days_remaining' => $item->expiry_date
                ? $today->diffInDays($item->expiry_date)
                : null,

            'status' => $item->status,

            'purchase_reference' => $item->purchase?->reference,

            'purchase_date' => $item->purchase?->purchase_date?->format('Y-m-d'),

            'supplier_name' => $item->purchase?->supplier?->company_name
                ?? $item->purchase?->supplier?->name
                ?? 'Unknown supplier',
        ];
    }
}