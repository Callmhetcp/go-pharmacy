<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    /**
     * Display all payments.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $payments = Payment::query()
            ->with([
                'order',
            ])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where(
                            'payment_reference',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'transaction_reference',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'gateway_transaction_id',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas('order', function ($orderQuery) use ($search) {
                            $orderQuery
                                ->where(
                                    'order_number',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'customer_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'customer_email',
                                    'like',
                                    "%{$search}%"
                                );
                        });
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,

            'filters' => [
                'search' => $search,
                'status' => $status,
            ],

            'statuses' => [
                'pending',
                'processing',
                'successful',
                'failed',
                'cancelled',
                'refunded',
            ],
        ]);
    }

    /**
     * Display payment details.
     */
    public function show(Payment $payment): Response
    {
        $payment->load([
            'order.items.product',
        ]);

        return Inertia::render('Admin/Payments/Show', [
            'payment' => $payment,
        ]);
    }

    
   /**
     * Mark payment as successful.
     *
     * Payment approval also fulfills the order's reserved inventory.
     */
    public function markAsSuccessful(
        Payment $payment,
        InventoryService $inventoryService
    ): RedirectResponse {
        DB::transaction(function () use (
            $payment,
            $inventoryService
        ) {
            $payment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->with('order')
                ->firstOrFail();

            $order = $payment->order;

            if (!$order) {
                throw new \RuntimeException(
                    'The payment is not attached to an order.'
                );
            }

            /**
             * Already-successful payments require no action.
             */
            if ($payment->status === 'successful') {
                return;
            }

            /**
             * Do not process another payment if the order has
             * already been marked as paid.
             */
            if ($order->payment_status === 'paid') {
                return;
            }

           /**
             * Mark payment successful first.
             *
             * No real payment gateway is involved here.
             * This is still the admin/manual placeholder workflow.
             */
            $payment->update([
                'status' => 'successful',
                'paid_at' => $payment->paid_at ?? now(),
                'verified_at' => now(),
            ]);

            /**
             * Mark the order as paid.
             *
             * InventoryService::fulfillOrder() requires this.
             */
            $order->update([
                'payment_status' => 'paid',
            ]);

            /**
             * Fulfill inventory.
             *
             * InventoryService will:
             *
             * - verify payment
             * - lock the order
             * - deduct physical stock
             * - release reservation
             * - create inventory transaction
             * - move order to processing
             */
            $inventoryService->fulfillOrder($order);

            
        });

        return back()->with(
            'success',
            'Payment marked as successful and inventory fulfilled.'
        );
    }
    /**
     * Mark payment as failed.
     *
     * A failed payment must:
     *
     * - mark the payment as failed
     * - mark the order payment status as failed
     * - release any inventory reservation
     * - NOT deduct physical stock
     * - NOT create an inventory transaction
     */
    public function markAsFailed(
        Payment $payment,
        InventoryService $inventoryService
    ): RedirectResponse {
        DB::transaction(function () use (
            $payment,
            $inventoryService
        ) {
            $payment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->with('order')
                ->firstOrFail();

            $order = $payment->order;

            if (! $order) {
                throw new \RuntimeException(
                    'The payment is not attached to an order.'
                );
            }

            /**
             * Already-failed payments require no action.
             */
            if ($payment->status === 'failed') {
                return;
            }

            /**
             * A successful payment has already fulfilled the order
             * and must never be changed back to failed.
             */
            if ($payment->status === 'successful') {
                throw new \RuntimeException(
                    'A successful payment cannot be marked as failed.'
                );
            }

            /**
             * An order that has already been paid must not be changed
             * back to failed through this endpoint.
             */
            if ($order->payment_status === 'paid') {
                throw new \RuntimeException(
                    'This order has already been paid and cannot be marked as failed.'
                );
            }

            /**
             * Mark payment as failed.
             */
            $payment->update([
                'status' => 'failed',
            ]);

            /**
             * Mark order payment status as failed.
             */
            $order->update([
                'payment_status' => 'failed',
            ]);

            /**
             * Release the inventory reservation.
             *
             * Physical stock is NOT reduced.
             */
            $inventoryService->releaseOrder($order);
        });

        return back()->with(
            'success',
            'Payment marked as failed and inventory reservation released.'
        );
    }
}
