<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Display all orders belonging to the authenticated customer.
     */
    public function index(Request $request)
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'items.product',
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Order/Index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Display one order belonging to the authenticated customer.
     */
    public function show(Request $request, Order $order)
    {
        abort_unless(
            $order->user_id === $request->user()->id,
            403
        );

        $order->load([
            'items.product',
            'payments',
        ]);

        return Inertia::render('Order/Show', [
            'order' => $order,
        ]);
    }
}
