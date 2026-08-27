<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentController extends Controller
{
    /**
     * Display a paginated list of payments.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $payments = Payment::query()
            ->with([
                'order:id,order_number,customer_name,customer_email,total,status,payment_status',
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
            ->paginate(
                $request->integer('per_page', 20)
            )
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Display a specific payment.
     */
    public function show(Payment $payment): JsonResponse
    {
        $payment->load([
            'order.items.product',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'payment' => $payment,
            ],
        ]);
    }

    /**
     * Mark payment as successful and fulfill inventory.
     */
    public function markAsSuccessful(
        Payment $payment,
        InventoryService $inventoryService
    ): JsonResponse {
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
                throw new RuntimeException(
                    'The payment is not attached to an order.'
                );
            }

            if ($payment->status === 'successful') {
                return;
            }

            if ($order->payment_status === 'paid') {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Mark Payment Successful
            |--------------------------------------------------------------------------
            |
            | No real payment gateway is involved here.
            | This is the admin/manual placeholder workflow.
            |
            */

            $payment->update([
                'status' => 'successful',
                'paid_at' => $payment->paid_at ?? now(),
                'verified_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Mark Order Paid
            |--------------------------------------------------------------------------
            |
            | InventoryService::fulfillOrder() requires the order
            | to already have a paid payment status.
            |
            */

            $order->update([
                'payment_status' => 'paid',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Fulfill Inventory
            |--------------------------------------------------------------------------
            |
            | The order already reserved the required stock when it was created.
            |
            | Fulfillment:
            |
            | 1. Removes physical stock.
            | 2. Removes the reservation.
            | 3. Creates inventory transactions.
            |
            */

            $order->loadMissing('items');

            $inventoryService->fulfillOrder($order);

            /*
            |--------------------------------------------------------------------------
            | Move Order To Processing
            |--------------------------------------------------------------------------
            */

            $order->update([
                'status' => 'processing',
            ]);
        });

        $payment->refresh();

        $payment->load([
            'order.items.product',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment marked as successful and inventory fulfilled.',
            'data' => [
                'payment' => $payment,
            ],
        ]);
    }

    /**
     * Mark payment as failed and release the order's
     * inventory reservation.
     */
    public function markAsFailed(
        Payment $payment,
        InventoryService $inventoryService
    ): JsonResponse {
        DB::transaction(function () use (
            $payment,
            $inventoryService
        ) {
            $payment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->with('order.items')
                ->firstOrFail();

            $order = $payment->order;

            /*
            |--------------------------------------------------------------------------
            | Payment must be attached to an order
            |--------------------------------------------------------------------------
            */

            if (! $order) {
                throw new RuntimeException(
                    'The payment is not attached to an order.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent duplicate failure processing
            |--------------------------------------------------------------------------
            |
            | If the payment has already failed, its reservation should
            | not be released again.
            |
            */

            if ($payment->status === 'failed') {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Mark payment as failed
            |--------------------------------------------------------------------------
            */

            $payment->update([
                'status' => 'failed',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Mark order payment as failed
            |--------------------------------------------------------------------------
            */

            $order->update([
                'payment_status' => 'failed',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Release reserved inventory
            |--------------------------------------------------------------------------
            |
            | The physical stock is NOT reduced.
            |
            | Example:
            |
            | quantity           = 10
            | reserved_quantity  = 1
            |
            | After failure:
            |
            | quantity           = 10
            | reserved_quantity  = 0
            |
            */

            $inventoryService->releaseOrder($order);
        });

        $payment->refresh();

        $payment->load([
            'order.items.product',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment marked as failed and inventory reservation released.',
            'data' => [
                'payment' => $payment,
            ],
        ]);
    }
}