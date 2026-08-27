<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },

    recentOrders: {
        type: Array,
        default: () => [],
    },

    lowStockProducts: {
        type: Array,
        default: () => [],
    },

    recentPayments: {
        type: Array,
        default: () => [],
    },

    orderStatuses: {
        type: Array,
        default: () => [],
    },

    paymentStatuses: {
        type: Array,
        default: () => [],
    },
});

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

const dashboardStats = computed(() => [
    {
        label: 'Products',
        value: Number(props.stats.total_products ?? 0),
        description: `${Number(props.stats.active_products ?? 0)} active products`,
        href: '/admin/products',
    },
    {
        label: 'Categories',
        value: Number(props.stats.total_categories ?? 0),
        description: `${Number(props.stats.active_categories ?? 0)} active categories`,
        href: '/admin/categories',
    },
    {
        label: 'Orders',
        value: Number(props.stats.total_orders ?? 0),
        description: `${Number(props.stats.pending_orders ?? 0)} pending orders`,
        href: '/admin/orders',
    },
    {
        label: 'Customers',
        value: Number(props.stats.total_customers ?? 0),
        description: 'Registered customers',
        href: '/admin/customers',
    },
]);

/*
|--------------------------------------------------------------------------
| Formatting
|--------------------------------------------------------------------------
*/

const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
    }).format(Number(amount ?? 0));
};

const formatNumber = (value) => {
    return new Intl.NumberFormat('en-NG').format(
        Number(value ?? 0)
    );
};

const formatDate = (date) => {
    if (!date) {
        return '—';
    }

    const parsed = new Date(date);

    if (Number.isNaN(parsed.getTime())) {
        return '—';
    }

    return new Intl.DateTimeFormat('en-NG', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(parsed);
};

const readableStatus = (status) => {
    if (!status) {
        return 'Unknown';
    }

    return String(status)
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
};

/*
|--------------------------------------------------------------------------
| Status Styling
|--------------------------------------------------------------------------
*/

const statusClass = (status) => {
    const classes = {
        pending:
            'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-400',

        confirmed:
            'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/30 dark:text-blue-400',

        processing:
            'border-indigo-200 bg-indigo-50 text-indigo-700 dark:border-indigo-900/50 dark:bg-indigo-950/30 dark:text-indigo-400',

        ready:
            'border-cyan-200 bg-cyan-50 text-cyan-700 dark:border-cyan-900/50 dark:bg-cyan-950/30 dark:text-cyan-400',

        shipped:
            'border-purple-200 bg-purple-50 text-purple-700 dark:border-purple-900/50 dark:bg-purple-950/30 dark:text-purple-400',

        completed:
            'border-green-200 bg-green-50 text-green-700 dark:border-green-900/50 dark:bg-green-950/30 dark:text-green-400',

        cancelled:
            'border-red-200 bg-red-50 text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400',

        unpaid:
            'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-400',

        pending_payment:
            'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-400',

        paid:
            'border-green-200 bg-green-50 text-green-700 dark:border-green-900/50 dark:bg-green-950/30 dark:text-green-400',

        successful:
            'border-green-200 bg-green-50 text-green-700 dark:border-green-900/50 dark:bg-green-950/30 dark:text-green-400',

        failed:
            'border-red-200 bg-red-50 text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400',

        refunded:
            'border-purple-200 bg-purple-50 text-purple-700 dark:border-purple-900/50 dark:bg-purple-950/30 dark:text-purple-400',
    };

    return (
        classes[String(status).toLowerCase()] ??
        'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'
    );
};

/*
|--------------------------------------------------------------------------
| Payment Method
|--------------------------------------------------------------------------
*/

const readablePaymentMethod = (method) => {
    if (!method) {
        return '—';
    }

    const labels = {
        cash: 'Cash',
        transfer: 'Bank Transfer',
        card: 'Card',
        pos: 'POS',
        flutterwave: 'Flutterwave',
        paystack: 'Paystack',
    };

    return (
        labels[String(method).toLowerCase()] ??
        readableStatus(method)
    );
};
</script>

<template>
    <AdminLayout>
        <div class="min-h-full bg-slate-50 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white">
            <div class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">

                <!-- =====================================================
                     HEADER
                ====================================================== -->

                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-green-600 dark:text-green-400">
                            Go Pharmacy Administration
                        </p>

                        <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-4xl">
                            Dashboard
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                            Overview of your pharmacy, products, inventory, orders,
                            customers and payments.
                        </p>
                    </div>

                    <a
                        href="/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-green-300 hover:text-green-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-green-700 dark:hover:text-green-400"
                    >
                        View Store
                    </a>
                </div>

                <!-- =====================================================
                     PRIMARY STATS
                ====================================================== -->

                <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    <Link
                        v-for="stat in dashboardStats"
                        :key="stat.label"
                        :href="stat.href"
                        class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-green-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-green-900"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                    {{ stat.label }}
                                </p>

                                <p class="mt-3 text-3xl font-bold tracking-tight text-slate-950 dark:text-white">
                                    {{ formatNumber(stat.value) }}
                                </p>

                                <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">
                                    {{ stat.description }}
                                </p>
                            </div>

                            <span class="text-slate-300 transition group-hover:translate-x-1 group-hover:text-green-600 dark:text-slate-700 dark:group-hover:text-green-400">
                                →
                            </span>
                        </div>
                    </Link>
                </div>

                <!-- =====================================================
                     REVENUE / INVENTORY
                ====================================================== -->

                <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">

                    <!-- Total Revenue -->

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                            Total Revenue
                        </p>

                        <p class="mt-3 text-2xl font-bold text-slate-950 dark:text-white">
                            {{ formatMoney(stats.total_revenue) }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">
                            From completed orders
                        </p>
                    </div>

                    <!-- Today -->

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                            Today's Revenue
                        </p>

                        <p class="mt-3 text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ formatMoney(stats.today_revenue) }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">
                            Completed orders today
                        </p>
                    </div>

                    <!-- Month -->

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                            This Month
                        </p>

                        <p class="mt-3 text-2xl font-bold text-slate-950 dark:text-white">
                            {{ formatMoney(stats.month_revenue) }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">
                            Completed orders this month
                        </p>
                    </div>

                    <!-- Available Stock -->

                    <Link
                        href="/admin/inventory"
                        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-green-200 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-green-900"
                    >
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                            Available Stock
                        </p>

                        <p class="mt-3 text-2xl font-bold text-slate-950 dark:text-white">
                            {{ formatNumber(stats.available_stock_units) }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">
                            Units currently available
                        </p>
                    </Link>
                </div>

                <!-- =====================================================
                     ORDER / INVENTORY ALERTS
                ====================================================== -->

                <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">

                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900/40 dark:bg-amber-950/20">
                        <p class="text-sm font-medium text-amber-700 dark:text-amber-400">
                            Pending Orders
                        </p>

                        <p class="mt-2 text-2xl font-bold text-amber-800 dark:text-amber-300">
                            {{ formatNumber(stats.pending_orders) }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5 dark:border-indigo-900/40 dark:bg-indigo-950/20">
                        <p class="text-sm font-medium text-indigo-700 dark:text-indigo-400">
                            Processing Orders
                        </p>

                        <p class="mt-2 text-2xl font-bold text-indigo-800 dark:text-indigo-300">
                            {{ formatNumber(stats.processing_orders) }}
                        </p>
                    </div>

                    <Link
                        href="/admin/inventory"
                        class="rounded-2xl border border-orange-200 bg-orange-50 p-5 dark:border-orange-900/40 dark:bg-orange-950/20"
                    >
                        <p class="text-sm font-medium text-orange-700 dark:text-orange-400">
                            Low Stock
                        </p>

                        <p class="mt-2 text-2xl font-bold text-orange-800 dark:text-orange-300">
                            {{ formatNumber(stats.low_stock_products) }}
                        </p>
                    </Link>

                    <Link
                        href="/admin/inventory"
                        class="rounded-2xl border border-red-200 bg-red-50 p-5 dark:border-red-900/40 dark:bg-red-950/20"
                    >
                        <p class="text-sm font-medium text-red-700 dark:text-red-400">
                            Out of Stock
                        </p>

                        <p class="mt-2 text-2xl font-bold text-red-800 dark:text-red-300">
                            {{ formatNumber(stats.out_of_stock_products) }}
                        </p>
                    </Link>
                </div>

                <!-- =====================================================
                     MAIN CONTENT
                ====================================================== -->

                <div class="mt-6 grid gap-6 xl:grid-cols-3">

                    <!-- =================================================
                         RECENT ORDERS
                    ================================================== -->

                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2 dark:border-slate-800 dark:bg-slate-900">

                        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                            <div>
                                <h2 class="text-lg font-bold text-slate-950 dark:text-white">
                                    Recent Orders
                                </h2>

                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    Latest orders recorded in the system.
                                </p>
                            </div>

                            <Link
                                href="/admin/orders"
                                class="text-sm font-semibold text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300"
                            >
                                View all
                            </Link>
                        </div>

                        <div
                            v-if="recentOrders.length"
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <Link
                                v-for="order in recentOrders"
                                :key="order.id"
                                :href="`/admin/orders/${order.id}`"
                                class="flex items-center justify-between gap-4 px-6 py-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/50"
                            >
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900 dark:text-white">
                                        {{ order.order_number }}
                                    </p>

                                    <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">
                                        {{ order.customer_name || 'Walk-in Customer' }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                        {{ formatDate(order.created_at) }}
                                    </p>
                                </div>

                                <div class="shrink-0 text-right">
                                    <p class="font-bold text-slate-900 dark:text-white">
                                        {{ formatMoney(order.total) }}
                                    </p>

                                    <span
                                        class="mt-2 inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold"
                                        :class="statusClass(order.status)"
                                    >
                                        {{ readableStatus(order.status) }}
                                    </span>
                                </div>
                            </Link>
                        </div>

                        <div
                            v-else
                            class="px-6 py-12 text-center"
                        >
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                                No orders yet
                            </p>

                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                Customer orders will appear here.
                            </p>
                        </div>
                    </section>

                    <!-- =================================================
                         ORDER STATUS
                    ================================================== -->

                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

                        <h2 class="text-lg font-bold text-slate-950 dark:text-white">
                            Order Status
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Current order distribution.
                        </p>

                        <div
                            v-if="orderStatuses.length"
                            class="mt-6 space-y-4"
                        >
                            <div
                                v-for="item in orderStatuses"
                                :key="item.status"
                                class="flex items-center justify-between gap-4"
                            >
                                <span
                                    class="rounded-full border px-2.5 py-1 text-xs font-semibold"
                                    :class="statusClass(item.status)"
                                >
                                    {{ readableStatus(item.status) }}
                                </span>

                                <span class="font-bold text-slate-900 dark:text-white">
                                    {{ formatNumber(item.total) }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-else
                            class="mt-8 text-center text-sm text-slate-400"
                        >
                            No order data available.
                        </div>
                    </section>
                </div>

                <!-- =====================================================
                     SECONDARY CONTENT
                ====================================================== -->

                <div class="mt-6 grid gap-6 xl:grid-cols-2">

                    <!-- =================================================
                         LOW STOCK
                    ================================================== -->

                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                            <div>
                                <h2 class="text-lg font-bold text-slate-950 dark:text-white">
                                    Low Stock Products
                                </h2>

                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    Products that need inventory attention.
                                </p>
                            </div>

                            <Link
                                href="/admin/inventory"
                                class="text-sm font-semibold text-green-600 hover:text-green-700 dark:text-green-400"
                            >
                                Inventory
                            </Link>
                        </div>

                        <div
                            v-if="lowStockProducts.length"
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <div
                                v-for="product in lowStockProducts"
                                :key="product.id"
                                class="flex items-center justify-between gap-4 px-6 py-4"
                            >
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900 dark:text-white">
                                        {{ product.name }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                        SKU: {{ product.sku || '—' }}
                                    </p>
                                </div>

                                <div class="shrink-0 text-right">
                                    <p
                                        class="font-bold"
                                        :class="
                                            product.quantity <= 0
                                                ? 'text-red-600 dark:text-red-400'
                                                : 'text-orange-600 dark:text-orange-400'
                                        "
                                    >
                                        {{ formatNumber(product.quantity) }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                        Minimum:
                                        {{ formatNumber(product.minimum_stock) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="px-6 py-12 text-center"
                        >
                            <p class="text-sm font-semibold text-green-600 dark:text-green-400">
                                Inventory looks good
                            </p>

                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                No products are currently below their minimum stock level.
                            </p>
                        </div>
                    </section>

                    <!-- =================================================
                         RECENT PAYMENTS
                    ================================================== -->

                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                            <div>
                                <h2 class="text-lg font-bold text-slate-950 dark:text-white">
                                    Recent Payments
                                </h2>

                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    Latest payment transactions.
                                </p>
                            </div>

                            <Link
                                href="/admin/payments"
                                class="text-sm font-semibold text-green-600 hover:text-green-700 dark:text-green-400"
                            >
                                View all
                            </Link>
                        </div>

                        <div
                            v-if="recentPayments.length"
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <div
                                v-for="payment in recentPayments"
                                :key="payment.id"
                                class="flex items-center justify-between gap-4 px-6 py-4"
                            >
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900 dark:text-white">
                                        {{ payment.order_number }}
                                    </p>

                                    <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">
                                        {{ payment.customer_name || 'Walk-in Customer' }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                        {{ readablePaymentMethod(payment.payment_method) }}
                                    </p>
                                </div>

                                <div class="shrink-0 text-right">
                                    <p class="font-bold text-slate-900 dark:text-white">
                                        {{ formatMoney(payment.amount) }}
                                    </p>

                                    <span
                                        class="mt-2 inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold"
                                        :class="statusClass(payment.status)"
                                    >
                                        {{ readableStatus(payment.status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="px-6 py-12 text-center"
                        >
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                                No payments yet
                            </p>

                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                Payment transactions will appear here.
                            </p>
                        </div>
                    </section>
                </div>

                <!-- =====================================================
                     PAYMENT STATUS
                ====================================================== -->

                <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
                        <div>
                            <h2 class="text-lg font-bold text-slate-950 dark:text-white">
                                Payment Status
                            </h2>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                Current payment status across orders.
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="paymentStatuses.length"
                        class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <div
                            v-for="item in paymentStatuses"
                            :key="item.status"
                            class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/50"
                        >
                            <span
                                class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold"
                                :class="statusClass(item.status)"
                            >
                                {{ readableStatus(item.status) }}
                            </span>

                            <p class="mt-3 text-2xl font-bold text-slate-950 dark:text-white">
                                {{ formatNumber(item.total) }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                Orders
                            </p>
                        </div>
                    </div>

                    <div
                        v-else
                        class="mt-8 text-center text-sm text-slate-400"
                    >
                        No payment status data available.
                    </div>
                </section>

                <!-- =====================================================
                     FOOTER
                ====================================================== -->

                <div class="mt-8 pb-4 text-center">
                    <p class="text-xs text-slate-400 dark:text-slate-500">
                        Go Pharmacy Administration
                    </p>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
