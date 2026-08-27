<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | PRODUCT STATISTICS
        |--------------------------------------------------------------------------
        */

        $totalProducts = Product::count();

        $activeProducts = Product::where('is_active', true)->count();

        $inactiveProducts = Product::where('is_active', false)->count();

        /*
        |--------------------------------------------------------------------------
        | CATEGORY STATISTICS
        |--------------------------------------------------------------------------
        */

        $totalCategories = Category::count();

        $activeCategories = Category::where('is_active', true)->count();

        /*
        |--------------------------------------------------------------------------
        | CUSTOMER STATISTICS
        |--------------------------------------------------------------------------
        */

        $totalCustomers = User::where('is_admin', false)->count();

        /*
        |--------------------------------------------------------------------------
        | ORDER STATISTICS
        |--------------------------------------------------------------------------
        */

        $totalOrders = Order::count();

        $pendingOrders = Order::where('status', 'pending')->count();

        $processingOrders = Order::whereIn('status', [
            'confirmed',
            'processing',
        ])->count();

        $completedOrders = Order::where('status', 'completed')->count();

        $cancelledOrders = Order::where('status', 'cancelled')->count();

        /*
        |--------------------------------------------------------------------------
        | REVENUE
        |--------------------------------------------------------------------------
        |
        | Revenue is calculated from completed orders.
        |
        */

        $totalRevenue = Order::where('status', 'completed')
            ->sum('total');

        $todayRevenue = Order::where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum('total');

        $monthRevenue = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        /*
        |--------------------------------------------------------------------------
        | INVENTORY
        |--------------------------------------------------------------------------
        */

        $totalStockUnits = DB::table('inventories')
            ->sum('quantity');

        $reservedStockUnits = DB::table('inventories')
            ->sum('reserved_quantity');

        $availableStockUnits = DB::table('inventories')
            ->selectRaw(
                'COALESCE(SUM(quantity - reserved_quantity), 0) as total'
            )
            ->value('total');

        $lowStockProductsCount = DB::table('inventories')
            ->whereColumn('quantity', '<=', 'minimum_stock')
            ->where('quantity', '>', 0)
            ->count();

        $outOfStockProductsCount = DB::table('inventories')
            ->where('quantity', '<=', 0)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | RECENT ORDERS
        |--------------------------------------------------------------------------
        */

        $recentOrders = Order::query()
            ->latest()
            ->take(8)
            ->get([
                'id',
                'order_number',
                'customer_name',
                'customer_email',
                'total',
                'status',
                'payment_status',
                'cashier_id',
                'created_at',
            ]);

        /*
        |--------------------------------------------------------------------------
        | LOW STOCK PRODUCTS
        |--------------------------------------------------------------------------
        */

        $lowStockProducts = Product::query()
            ->select([
                'products.id',
                'products.name',
                'products.sku',
                'products.image',
                'products.is_active',
                'inventories.quantity',
                'inventories.reserved_quantity',
                'inventories.minimum_stock',
            ])
            ->join(
                'inventories',
                'inventories.product_id',
                '=',
                'products.id'
            )
            ->whereColumn(
                'inventories.quantity',
                '<=',
                'inventories.minimum_stock'
            )
            ->orderBy('inventories.quantity')
            ->take(8)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'image' => $product->image,
                    'is_active' => (bool) $product->is_active,
                    'quantity' => (int) $product->quantity,
                    'reserved_quantity' => (int) $product->reserved_quantity,
                    'minimum_stock' => (int) $product->minimum_stock,
                    'available_quantity' => max(
                        0,
                        (int) $product->quantity -
                            (int) $product->reserved_quantity
                    ),
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | RECENT PAYMENTS
        |--------------------------------------------------------------------------
        */

        $recentPayments = DB::table('payments')
            ->join(
                'orders',
                'orders.id',
                '=',
                'payments.order_id'
            )
            ->select([
                'payments.id',
                'payments.amount',
                'payments.status',
                'payments.payment_method',
                'payments.gateway',
                'payments.created_at',
                'orders.order_number',
                'orders.customer_name',
            ])
            ->orderByDesc('payments.created_at')
            ->take(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ORDER STATUS SUMMARY
        |--------------------------------------------------------------------------
        */

        $orderStatuses = Order::query()
            ->select(
                'status',
                DB::raw('COUNT(*) as total')
            )
            ->whereNotNull('status')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                return [
                    'status' => $row->status,
                    'total' => (int) $row->total,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | PAYMENT STATUS SUMMARY
        |--------------------------------------------------------------------------
        */

        $paymentStatuses = Order::query()
            ->select(
                'payment_status',
                DB::raw('COUNT(*) as total')
            )
            ->whereNotNull('payment_status')
            ->groupBy('payment_status')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                return [
                    'status' => $row->payment_status,
                    'total' => (int) $row->total,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'inactive_products' => $inactiveProducts,

                'total_categories' => $totalCategories,
                'active_categories' => $activeCategories,

                'total_customers' => $totalCustomers,

                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'processing_orders' => $processingOrders,
                'completed_orders' => $completedOrders,
                'cancelled_orders' => $cancelledOrders,

                'total_revenue' => (float) $totalRevenue,
                'today_revenue' => (float) $todayRevenue,
                'month_revenue' => (float) $monthRevenue,

                'total_stock_units' => (int) $totalStockUnits,
                'reserved_stock_units' => (int) $reservedStockUnits,
                'available_stock_units' => (int) $availableStockUnits,

                'low_stock_products' => $lowStockProductsCount,
                'out_of_stock_products' => $outOfStockProductsCount,
            ],

            'recentOrders' => $recentOrders,

            'lowStockProducts' => $lowStockProducts,

            'recentPayments' => $recentPayments,

            'orderStatuses' => $orderStatuses,

            'paymentStatuses' => $paymentStatuses,
        ]);
    }
}
