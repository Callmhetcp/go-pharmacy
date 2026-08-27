<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrderResource;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {
    }

    /**
     * Display the authenticated customer's orders.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'items',
                'payments',
            ])
            ->latest()
            ->paginate(10);

        return OrderResource::collection($orders);
    }

    /**
     * Display a single authenticated customer's order.
     */
    public function show(
        Request $request,
        Order $order
    ): OrderResource {
        abort_unless(
            $order->user_id === $request->user()->id,
            404
        );

        $order->load([
            'items',
            'payments',
        ]);

        return new OrderResource($order);
    }

    /**
     * Cancel an authenticated customer's order.
     *
     * Cancelling an order releases all inventory reservations
     * belonging to that order.
     */
    public function cancel(
        Request $request,
        Order $order
    ): JsonResponse {
        abort_unless(
            $order->user_id === $request->user()->id,
            404
        );

        if ($order->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'This order has already been cancelled.',
            ], 422);
        }

        if (in_array($order->status, [
            'completed',
            'delivered',
            'fulfilled',
        ], true)) {
            return response()->json([
                'success' => false,
                'message' => 'This order can no longer be cancelled.',
            ], 422);
        }

        DB::transaction(function () use ($order) {
            $order->load('items');

            $this->inventoryService->releaseOrder($order);

            $order->update([
                'status' => 'cancelled',
            ]);
        });

        $order->load([
            'items',
            'payments',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully.',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Look up a guest order using order number and email.
     */
    public function lookup(Request $request): OrderResource
    {
        $validated = $request->validate([
            'order_number' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ]);

        $order = Order::query()
            ->where(
                'order_number',
                $validated['order_number']
            )
            ->where(
                'customer_email',
                $validated['email']
            )
            ->with([
                'items',
                'payments',
            ])
            ->first();

        abort_unless($order, 404);

        return new OrderResource($order);
    }

    /**
     * Create a non-functional payment placeholder.
     *
     * IMPORTANT:
     * No payment gateway is initialized here.
     */
    public function createPayment(
        Request $request,
        Order $order
    ): PaymentResource {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'payment_method' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Verify customer
        |--------------------------------------------------------------------------
        */

        abort_unless(
            strtolower($order->customer_email)
                === strtolower($validated['email']),
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Check order status
        |--------------------------------------------------------------------------
        */

        if ($order->status === 'cancelled') {
            abort(
                422,
                'Cancelled orders cannot be paid.'
            );
        }

        if (in_array($order->status, [
            'completed',
            'delivered',
            'fulfilled',
        ], true)) {
            abort(
                422,
                'This order has already been fulfilled.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Check payment status
        |--------------------------------------------------------------------------
        */

        if ($order->payment_status === 'paid') {
            abort(
                422,
                'This order has already been paid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Return existing pending payment
        |--------------------------------------------------------------------------
        */

        $existingPayment = $order->payments()
            ->whereIn('status', [
                'pending',
                'processing',
            ])
            ->latest()
            ->first();

        if ($existingPayment) {
            return new PaymentResource($existingPayment);
        }

        /*
        |--------------------------------------------------------------------------
        | Create payment placeholder
        |--------------------------------------------------------------------------
        |
        | This is intentionally NOT connected to Flutterwave, Paystack,
        | OPay, or any other real payment gateway.
        |
        */

        $payment = DB::transaction(function () use (
            $order,
            $validated
        ) {
            $payment = $order->payments()->create([
                'payment_reference' =>
                    $this->generatePaymentReference(),

                'transaction_reference' => null,

                'gateway' => 'placeholder',

                'gateway_transaction_id' => null,

                'amount' => $order->total,

                'currency' => 'NGN',

                'status' => 'pending',

                'payment_method' =>
                    $validated['payment_method'],

                'gateway_message' =>
                    'Payment gateway integration is not active yet.',

                'gateway_response' => null,

                'paid_at' => null,

                'verified_at' => null,

                'refunded_amount' => 0,

                'refunded_at' => null,
            ]);

            $order->update([
                'payment_status' => 'pending',
            ]);

            return $payment;
        });

        return new PaymentResource($payment);
    }

    /**
     * Display payments belonging to a guest order.
     */
    public function payments(
        Request $request,
        Order $order
    ): AnonymousResourceCollection {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ]);

        abort_unless(
            strtolower($order->customer_email)
                === strtolower($validated['email']),
            404
        );

        $payments = $order->payments()
            ->latest()
            ->get();

        return PaymentResource::collection($payments);
    }

    /**
     * Generate a unique payment reference.
     */
    protected function generatePaymentReference(): string
    {
        do {
            $reference =
                'GP-PAY-'
                . now()->format('Ymd')
                . '-'
                . strtoupper(Str::random(8));
        } while (
            Payment::where(
                'payment_reference',
                $reference
            )->exists()
        );

        return $reference;
    }
}