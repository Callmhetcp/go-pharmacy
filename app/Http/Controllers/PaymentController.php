<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    /**
     * Display the payment placeholder page.
     *
     * No payment gateway is initialized at this stage.
     */
    public function create(Order $order): Response
    {
        $this->authorizeOrder($order);

        $order->load([
            'items.product',
        ]);

        return Inertia::render('Payments/Create', [
            'order' => $order,
        ]);
    }

    /**
     * Ensure the authenticated customer owns the order.
     */
    protected function authorizeOrder(Order $order): void
    {
        if (
            $order->user_id !== null
            && (
                ! Auth::check()
                || $order->user_id !== Auth::id()
            )
        ) {
            abort(403);
        }
    }
}