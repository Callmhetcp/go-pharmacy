<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentService
{
    /**
     * Initialize payment for an order.
     */
    public function initialize(Order $order): array
    {
        if ($order->total <= 0) {
            throw new RuntimeException(
                'This order does not require payment.'
            );
        }

        if ($order->payment_status === 'paid') {
            throw new RuntimeException(
                'This order has already been paid.'
            );
        }

        $payment = $order->payments()
            ->whereIn('status', [
                'pending',
                'processing',
            ])
            ->latest()
            ->first();

        if (!$payment) {
            $payment = $this->createPayment($order);
        }

        /*
        |--------------------------------------------------------------------------
        | Gateway
        |--------------------------------------------------------------------------
        |
        | For now the gateway is intentionally isolated here.
        |
        | When Flutterwave is connected, this method will call the
        | Flutterwave API and return its checkout URL.
        |
        */

        $gateway = config(
            'payment.default_gateway',
            'flutterwave'
        );

        return match ($gateway) {
            'flutterwave' => $this->initializeFlutterwave(
                $order,
                $payment
            ),

            default => throw new RuntimeException(
                "Unsupported payment gateway: {$gateway}"
            ),
        };
    }

    /**
     * Create a payment record when none exists.
     */
    protected function createPayment(Order $order): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'payment_reference' => $this->generateReference(),
            'gateway' => 'pending',
            'amount' => $order->total,
            'currency' => 'NGN',
            'status' => 'pending',
            'payment_method' => null,
        ]);
    }

    /**
     * Initialize Flutterwave payment.
     */
    protected function initializeFlutterwave(
        Order $order,
        Payment $payment
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Flutterwave integration
        |--------------------------------------------------------------------------
        |
        | We will connect the actual API here next.
        |
        */

        throw new RuntimeException(
            'Flutterwave payment gateway is not configured yet.'
        );
    }

    /**
     * Generate a unique payment reference.
     */
    protected function generateReference(): string
    {
        do {
            $reference =
                'GP-PAY-'
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