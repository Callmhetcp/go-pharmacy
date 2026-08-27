<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display business reports.
     */
    public function index(Request $request): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | REPORT PERIOD
        |--------------------------------------------------------------------------
        */

        $period = (int) $request->integer('period', 30);

        if (! in_array($period, [7, 30, 90], true)) {
            $period = 30;
        }

        $today = Carbon::today();

        $startDate = $today
            ->copy()
            ->subDays($period - 1)
            ->startOfDay();

        $endDate = $today
            ->copy()
            ->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | COMPLETED SALES
        |--------------------------------------------------------------------------
        */

        $completedSales = Order::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ]);

        /*
        |--------------------------------------------------------------------------
        | SALES SUMMARY
        |--------------------------------------------------------------------------
        */

        $totalSales = (clone $completedSales)
            ->sum('total');

        $posSales = (clone $completedSales)
            ->whereNotNull('cashier_id')
            ->sum('total');

        $onlineSales = (clone $completedSales)
            ->whereNull('cashier_id')
            ->sum('total');

        /*
        |--------------------------------------------------------------------------
        | TODAY'S SALES
        |--------------------------------------------------------------------------
        */

        $todaySalesQuery = Order::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [
                $today->copy()->startOfDay(),
                $today->copy()->endOfDay(),
            ]);

        $todaySales = (clone $todaySalesQuery)
            ->sum('total');

        $todayPosSales = (clone $todaySalesQuery)
            ->whereNotNull('cashier_id')
            ->sum('total');

        $todayOnlineSales = (clone $todaySalesQuery)
            ->whereNull('cashier_id')
            ->sum('total');

        /*
        |--------------------------------------------------------------------------
        | ORDER SUMMARY
        |--------------------------------------------------------------------------
        */

        $completedOrders = (clone $completedSales)
            ->count();

        $totalOrders = Order::query()
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ])
            ->count();

        $pendingOrders = Order::query()
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ])
            ->where('status', 'pending')
            ->count();

        $cancelledOrders = Order::query()
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ])
            ->where('status', 'cancelled')
            ->count();

        $averageOrderValue = $completedOrders > 0
            ? $totalSales / $completedOrders
            : 0;

        /*
        |--------------------------------------------------------------------------
        | CURRENT MONTH SALES
        |--------------------------------------------------------------------------
        */

        $monthSales = Order::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->sum('total');

        /*
        |--------------------------------------------------------------------------
        | DAILY SALES
        |--------------------------------------------------------------------------
        */

        $dailySales = (clone $completedSales)
            ->select(
                DB::raw('DATE(created_at) as sale_date'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        $salesByDay = collect();

        for (
            $date = $startDate->copy()->startOfDay();
            $date->lte($endDate);
            $date->addDay()
        ) {
            $dateKey = $date->format('Y-m-d');

            $salesByDay->push([
                'date' => $dateKey,
                'label' => $date->format('d M'),
                'total' => (float) (
                    $dailySales->get($dateKey)->total ?? 0
                ),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ORDER STATUSES
        |--------------------------------------------------------------------------
        */

        $orderStatuses = Order::query()
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ])
            ->select(
                'status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($item) => [
                'status' => $item->status,
                'total' => (int) $item->total,
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | PAYMENT STATUSES
        |--------------------------------------------------------------------------
        */

        $paymentStatuses = Order::query()
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ])
            ->select(
                'payment_status as status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('payment_status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($item) => [
                'status' => $item->status,
                'total' => (int) $item->total,
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | BEST-SELLING PRODUCTS
        |--------------------------------------------------------------------------
        */

        $topProducts = OrderItem::query()
            ->whereHas('order', function ($query) use (
                $startDate,
                $endDate
            ) {
                $query
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [
                        $startDate,
                        $endDate,
                    ]);
            })
            ->select(
                'product_id',
                'product_name',
                DB::raw('SUM(quantity) as quantity'),
                DB::raw('SUM(subtotal) as revenue')
            )
            ->groupBy(
                'product_id',
                'product_name'
            )
            ->orderByDesc('quantity')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->product_id,
                'name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'revenue' => (float) $item->revenue,
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | INVENTORY SUMMARY
        |--------------------------------------------------------------------------
        */

        $totalInventoryProducts = Inventory::query()
            ->count();

        $outOfStockProducts = Inventory::query()
            ->where('quantity', '<=', 0)
            ->count();

        $lowStockCount = Inventory::query()
            ->whereColumn(
                'quantity',
                '<=',
                'minimum_stock'
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | LOW STOCK PRODUCTS
        |--------------------------------------------------------------------------
        */

        $lowStockProducts = Inventory::query()
            ->with([
                'product:id,name,sku',
            ])
            ->whereColumn(
                'quantity',
                '<=',
                'minimum_stock'
            )
            ->orderBy('quantity')
            ->limit(10)
            ->get()
            ->map(function ($inventory) {
                return [
                    'id' => $inventory->product?->id,
                    'name' => $inventory->product?->name
                        ?? 'Unknown product',
                    'sku' => $inventory->product?->sku,
                    'quantity' => (int) $inventory->quantity,
                    'reserved_quantity' => (int) $inventory->reserved_quantity,
                    'available_quantity' => $inventory->available_quantity,
                    'minimum_stock' => (int) $inventory->minimum_stock,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | EXPIRED PRODUCTS
        |--------------------------------------------------------------------------
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
            ->limit(50)
            ->get()
            ->map(function ($item) use ($today) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name
                        ?? 'Unknown product',
                    'sku' => $item->product?->sku,
                    'quantity' => (int) $item->quantity,
                    'batch_number' => $item->batch_number,
                    'expiry_date' => $item->expiry_date?->format('Y-m-d'),
                    'days_expired' => $item->expiry_date
                        ? $item->expiry_date->diffInDays($today)
                        : null,
                    'purchase_reference' => $item->purchase?->reference,
                    'supplier_name' => $item->purchase?->supplier?->company_name
                        ?? $item->purchase?->supplier?->name,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | EXPIRING WITHIN 7 DAYS
        |--------------------------------------------------------------------------
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
            ->orderBy('expiry_date')
            ->limit(50)
            ->get()
            ->map(function ($item) use ($today) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name
                        ?? 'Unknown product',
                    'sku' => $item->product?->sku,
                    'quantity' => (int) $item->quantity,
                    'batch_number' => $item->batch_number,
                    'expiry_date' => $item->expiry_date?->format('Y-m-d'),
                    'days_remaining' => $item->expiry_date
                        ? $today->diffInDays($item->expiry_date)
                        : null,
                    'purchase_reference' => $item->purchase?->reference,
                    'supplier_name' => $item->purchase?->supplier?->company_name
                        ?? $item->purchase?->supplier?->name,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | EXPIRING WITHIN 30 DAYS
        |--------------------------------------------------------------------------
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
            ->orderBy('expiry_date')
            ->limit(100)
            ->get()
            ->map(function ($item) use ($today) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name
                        ?? 'Unknown product',
                    'sku' => $item->product?->sku,
                    'quantity' => (int) $item->quantity,
                    'batch_number' => $item->batch_number,
                    'expiry_date' => $item->expiry_date?->format('Y-m-d'),
                    'days_remaining' => $item->expiry_date
                        ? $today->diffInDays($item->expiry_date)
                        : null,
                    'purchase_reference' => $item->purchase?->reference,
                    'supplier_name' => $item->purchase?->supplier?->company_name
                        ?? $item->purchase?->supplier?->name,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | EXPIRY COUNTS
        |--------------------------------------------------------------------------
        */

        $expiredCount = PurchaseItem::query()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', $today)
            ->count();

        $expiring7DaysCount = PurchaseItem::query()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $today)
            ->whereDate(
                'expiry_date',
                '<=',
                $today->copy()->addDays(7)
            )
            ->count();

        $expiring30DaysCount = PurchaseItem::query()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $today)
            ->whereDate(
                'expiry_date',
                '<=',
                $today->copy()->addDays(30)
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | PURCHASE SUMMARY
        |--------------------------------------------------------------------------
        */

        $purchaseQuery = Purchase::query()
            ->whereBetween('purchase_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ]);

        $totalPurchases = (clone $purchaseQuery)
            ->count();

        $purchaseSpending = (clone $purchaseQuery)
            ->sum('total_amount');

        $purchaseDiscount = (clone $purchaseQuery)
            ->sum('discount');

        /*
        |--------------------------------------------------------------------------
        | PURCHASE STATUS
        |--------------------------------------------------------------------------
        */

        $purchaseStatuses = Purchase::query()
            ->whereBetween('purchase_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->select(
                'status',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(total_amount) as amount')
            )
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($item) => [
                'status' => $item->status,
                'total' => (int) $item->total,
                'amount' => (float) $item->amount,
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | RECENT PURCHASES
        |--------------------------------------------------------------------------
        */

        $recentPurchases = Purchase::query()
            ->with([
                'supplier:id,name,company_name',
                'user:id,name',
            ])
            ->withCount('items')
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn ($purchase) => [
                'id' => $purchase->id,
                'reference' => $purchase->reference,
                'supplier_name' => $purchase->supplier?->company_name
                    ?? $purchase->supplier?->name
                    ?? 'Unknown supplier',
                'purchase_date' => $purchase->purchase_date?->format('Y-m-d'),
                'items_count' => (int) $purchase->items_count,
                'subtotal' => (float) $purchase->subtotal,
                'discount' => (float) $purchase->discount,
                'total_amount' => (float) $purchase->total_amount,
                'status' => $purchase->status,
                'created_by' => $purchase->user?->name,
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | SUPPLIER SUMMARY
        |--------------------------------------------------------------------------
        */

        $totalSuppliers = Supplier::query()
            ->count();

        $activeSuppliers = Supplier::query()
            ->where('is_active', true)
            ->count();

        $inactiveSuppliers = Supplier::query()
            ->where('is_active', false)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | TOP SUPPLIERS
        |--------------------------------------------------------------------------
        */

        $topSuppliers = Supplier::query()
            ->leftJoin(
                'purchases',
                'suppliers.id',
                '=',
                'purchases.supplier_id'
            )
            ->where(function ($query) use ($startDate, $endDate) {
                $query
                    ->whereNull('purchases.id')
                    ->orWhereBetween('purchases.purchase_date', [
                        $startDate->toDateString(),
                        $endDate->toDateString(),
                    ]);
            })
            ->select(
                'suppliers.id',
                'suppliers.name',
                'suppliers.company_name',
                'suppliers.is_active',
                DB::raw('COUNT(purchases.id) as purchase_count'),
                DB::raw(
                    'COALESCE(SUM(purchases.total_amount), 0) as purchase_amount'
                )
            )
            ->groupBy(
                'suppliers.id',
                'suppliers.name',
                'suppliers.company_name',
                'suppliers.is_active'
            )
            ->orderByDesc('purchase_amount')
            ->limit(10)
            ->get()
            ->map(fn ($supplier) => [
                'id' => $supplier->id,
                'name' => $supplier->company_name
                    ?: $supplier->name,
                'is_active' => (bool) $supplier->is_active,
                'purchase_count' => (int) $supplier->purchase_count,
                'purchase_amount' => (float) $supplier->purchase_amount,
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | INVENTORY TRANSACTION SUMMARY
        |--------------------------------------------------------------------------
        */

        $inventoryTransactions = InventoryTransaction::query()
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ]);

        $inventoryTransactionCount = (clone $inventoryTransactions)
            ->count();

        $inventoryTransactionTypes = (clone $inventoryTransactions)
            ->select(
                'type',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(quantity) as quantity')
            )
            ->groupBy('type')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($item) => [
                'type' => $item->type,
                'total' => (int) $item->total,
                'quantity' => (int) $item->quantity,
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | API RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'data' => [
                'summary' => [
                    'total_sales' => (float) $totalSales,
                    'pos_sales' => (float) $posSales,
                    'online_sales' => (float) $onlineSales,
                    'today_sales' => (float) $todaySales,
                    'today_pos_sales' => (float) $todayPosSales,
                    'today_online_sales' => (float) $todayOnlineSales,
                    'month_sales' => (float) $monthSales,

                    'total_orders' => $totalOrders,
                    'completed_orders' => $completedOrders,
                    'pending_orders' => $pendingOrders,
                    'cancelled_orders' => $cancelledOrders,
                    'average_order_value' => (float) $averageOrderValue,

                    'total_inventory_products' => $totalInventoryProducts,
                    'out_of_stock_products' => $outOfStockProducts,
                    'low_stock_count' => $lowStockCount,

                    'expired_count' => $expiredCount,
                    'expiring_7_days_count' => $expiring7DaysCount,
                    'expiring_30_days_count' => $expiring30DaysCount,

                    'total_purchases' => $totalPurchases,
                    'purchase_spending' => (float) $purchaseSpending,
                    'purchase_discount' => (float) $purchaseDiscount,

                    'total_suppliers' => $totalSuppliers,
                    'active_suppliers' => $activeSuppliers,
                    'inactive_suppliers' => $inactiveSuppliers,

                    'inventory_transaction_count' => $inventoryTransactionCount,
                ],

                'sales_by_day' => $salesByDay,

                'order_statuses' => $orderStatuses,

                'payment_statuses' => $paymentStatuses,

                'top_products' => $topProducts,

                'low_stock_products' => $lowStockProducts,

                'expired_products' => $expiredProducts,

                'expiring_soon' => $expiringSoon,

                'expiring_within_30_days' => $expiringWithin30Days,

                'purchase_statuses' => $purchaseStatuses,

                'recent_purchases' => $recentPurchases,

                'top_suppliers' => $topSuppliers,

                'inventory_transaction_types' => $inventoryTransactionTypes,

                'period' => $period,

                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
            ],
        ]);
    }
}
