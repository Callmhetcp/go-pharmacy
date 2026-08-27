<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\InventoryService;
use RuntimeException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Display orders.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $status = $request->string('status')->trim()->toString();

        $paymentStatus = $request
            ->string('payment_status')
            ->trim()
            ->toString();

        $orders = Order::query()
            ->withCount('items')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->when(
                $status,
                fn ($query) => $query->where('status', $status)
            )
            ->when(
                $paymentStatus,
                fn ($query) => $query->where(
                    'payment_status',
                    $paymentStatus
                )
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,

            'filters' => [
                'search' => $search,
                'status' => $status,
                'payment_status' => $paymentStatus,
            ],

            'statuses' => [
                'pending',
                'confirmed',
                'processing',
                'ready',
                'shipped',
                'completed',
                'cancelled',
            ],

            'paymentStatuses' => [
                'unpaid',
                'pending',
                'paid',
                'failed',
                'refunded',
            ],
        ]);
    }

    /**
     * Display order details.
     */
    public function show(Order $order): Response
    {
        $order->load([
            'user',
            'items.product.category',
        ]);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
        ]);
    }

    /**
     * Display order edit page.
     */
    public function edit(Order $order): Response
    {
        $order->load([
            'items.product',
        ]);

        return Inertia::render('Admin/Orders/Edit', [
            'order' => $order,
        ]);
    }

    /**
     * Update order.
     *
     * Payment gateway has not been implemented yet.
     *
     * Therefore order status is intentionally NOT changed manually
     * from the admin order details page.
     */
    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => [
                'required',
                'string',
                'max:255',
            ],

            'customer_email' => [
                'required',
                'email',
                'max:255',
            ],

            'customer_phone' => [
                'required',
                'string',
                'max:50',
            ],

            'delivery_address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $order->update($validated);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order details updated successfully.');
    }

    /**
     * Cancel an order and release its inventory reservation.
     */
    public function cancel(
        Order $order,
        OrderService $orderService
    ): RedirectResponse {
        try {
            $orderService->cancelOrder($order);
        } catch (RuntimeException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with(
                'success',
                'Order cancelled successfully and inventory reservation released.'
            );
    }

    /**
     * Fulfill an order and deduct physical inventory.
     */
    public function fulfill(
        Order $order,
        InventoryService $inventoryService
    ): RedirectResponse {
        try {
            $inventoryService->fulfillOrder($order);
        } catch (RuntimeException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with(
                'success',
                'Order fulfilled successfully and inventory deducted.'
            );
    }
}