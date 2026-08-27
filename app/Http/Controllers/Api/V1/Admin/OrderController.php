<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a paginated list of orders.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()
            ->with([
                'customer:id,name,email',
            ])
            ->withCount('items')
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();

            $query->where(function ($query) use ($search) {
                $query
                    ->where('order_number', 'like', "%{$search}%")
                    ->orWhere('receipt_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Order Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')->toString()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Payment Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('payment_status')) {
            $query->where(
                'payment_status',
                $request->string('payment_status')->toString()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $orders = $query
            ->paginate(
                $request->integer('per_page', 15)
            )
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Display a specific order.
     */
    public function show(Order $order): JsonResponse
    {
        $order->load([
            'customer:id,name,email',
            'cashier:id,name,email',
            'items.product:id,name,slug,sku,image',
            'payments',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'order' => $order,
            ],
        ]);
    }

    /**
     * Update an order.
     */
    public function update(
        Request $request,
        Order $order,
        InventoryService $inventoryService
    ): JsonResponse {
        $validated = $request->validate([
            'status' => [
                'sometimes',
                'string',
                'in:pending,confirmed,processing,ready,shipped,completed,cancelled',
            ],

            'notes' => [
                'sometimes',
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        DB::transaction(function () use (
            $validated,
            $order,
            $inventoryService
        ) {
            /*
            |--------------------------------------------------------------------------
            | Cancellation
            |--------------------------------------------------------------------------
            |
            | If an order is being cancelled for the first time, release its
            | inventory reservation.
            |
            | Physical stock is NOT changed here.
            |
            */

            $isBeingCancelled =
                isset($validated['status'])
                && $validated['status'] === 'cancelled'
                && $order->status !== 'cancelled';

            if ($isBeingCancelled) {
                $order->loadMissing('items');

                $inventoryService->releaseOrder($order);
            }

            $order->update($validated);
        });

        $order->load([
            'customer:id,name,email',
            'cashier:id,name,email',
            'items.product:id,name,slug,sku,image',
            'payments',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully.',
            'data' => [
                'order' => $order,
            ],
        ]);
    }
}
