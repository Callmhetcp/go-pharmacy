<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    summary: {
        type: Object,
        default: () => ({
            total_sales: 0,
            pos_sales: 0,
            online_sales: 0,
            today_sales: 0,
            today_pos_sales: 0,
            today_online_sales: 0,
            month_sales: 0,

            total_orders: 0,
            completed_orders: 0,
            pending_orders: 0,
            cancelled_orders: 0,
            average_order_value: 0,

            total_purchases: 0,
            purchase_value: 0,
            purchased_items: 0,

            total_suppliers: 0,
            active_suppliers: 0,

            expired_products: 0,
            expiring_products: 0,
            low_stock_products: 0,
        }),
    },

    salesByDay: {
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

    topProducts: {
        type: Array,
        default: () => [],
    },

    lowStockProducts: {
        type: Array,
        default: () => [],
    },

    expiredProducts: {
        type: Array,
        default: () => [],
    },

    expiringProducts: {
        type: Array,
        default: () => [],
    },

    topSuppliers: {
        type: Array,
        default: () => [],
    },

    recentPurchases: {
        type: Array,
        default: () => [],
    },

    purchaseSummary: {
        type: Object,
        default: () => ({
            total: 0,
            value: 0,
            items: 0,
        }),
    },

    supplierSummary: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
        }),
    },

    period: {
        type: Number,
        default: 30,
    },
});

/*
|--------------------------------------------------------------------------
| STATE
|--------------------------------------------------------------------------
*/

const selectedPeriod = ref(String(props.period));
const exporting = ref(false);

/*
|--------------------------------------------------------------------------
| FORMATTING
|--------------------------------------------------------------------------
*/

const formatMoney = (value) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        maximumFractionDigits: 0,
    }).format(Number(value || 0));
};

const formatNumber = (value) => {
    return new Intl.NumberFormat('en-NG').format(
        Number(value || 0),
    );
};

const readableStatus = (status) => {
    if (!status) {
        return 'Unknown';
    }

    return String(status)
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
};

const formatDate = (date) => {
    if (!date) {
        return '—';
    }

    const parsed = new Date(date);

    if (Number.isNaN(parsed.getTime())) {
        return date;
    }

    return new Intl.DateTimeFormat('en-NG', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(parsed);
};

/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

const statusClass = (status) => {
    const classes = {
        completed:
            'bg-green-100 text-green-700 dark:bg-green-950/40 dark:text-green-400',

        confirmed:
            'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400',

        processing:
            'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400',

        ready:
            'bg-cyan-100 text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-400',

        shipped:
            'bg-purple-100 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400',

        pending:
            'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',

        cancelled:
            'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-400',

        paid:
            'bg-green-100 text-green-700 dark:bg-green-950/40 dark:text-green-400',

        failed:
            'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-400',

        rejected:
            'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-400',

        awaiting_payment:
            'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',

        unpaid:
            'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
    };

    return (
        classes[status] ??
        'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
    );
};

/*
|--------------------------------------------------------------------------
| PERIOD
|--------------------------------------------------------------------------
*/

const selectedPeriodLabel = computed(() => {
    const labels = {
        7: 'Last 7 days',
        30: 'Last 30 days',
        90: 'Last 90 days',
    };

    return labels[selectedPeriod.value] ?? 'Last 30 days';
});

const changePeriod = (period) => {
    selectedPeriod.value = String(period);

    router.get(
        route('admin.reports.index'),
        {
            period,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

/*
|--------------------------------------------------------------------------
| SALES CHART
|--------------------------------------------------------------------------
*/

const maxSales = computed(() => {
    if (!props.salesByDay.length) {
        return 1;
    }

    return Math.max(
        ...props.salesByDay.map(
            (item) => Number(item.total || 0),
        ),
        1,
    );
});

/*
|--------------------------------------------------------------------------
| SALES CHANNEL PERCENTAGES
|--------------------------------------------------------------------------
*/

const posPercentage = computed(() => {
    if (!Number(props.summary.total_sales)) {
        return 0;
    }

    return Math.round(
        (Number(props.summary.pos_sales || 0) /
            Number(props.summary.total_sales)) *
            100,
    );
});

const onlinePercentage = computed(() => {
    if (!Number(props.summary.total_sales)) {
        return 0;
    }

    return Math.round(
        (Number(props.summary.online_sales || 0) /
            Number(props.summary.total_sales)) *
            100,
    );
});

/*
|--------------------------------------------------------------------------
| EXPIRY HELPERS
|--------------------------------------------------------------------------
*/

const expiryClass = (product) => {
    if (
        Number(product.days_remaining) <= 0 ||
        product.expired
    ) {
        return 'text-red-600 dark:text-red-400';
    }

    return 'text-amber-600 dark:text-amber-400';
};

/*
|--------------------------------------------------------------------------
| EXPORT
|--------------------------------------------------------------------------
|
| Export endpoint will be connected later.
|
*/

const exportReport = () => {
    exporting.value = true;

    window.setTimeout(() => {
        exporting.value = false;
    }, 500);
};
</script>

<template>
    <AdminLayout>
        <div
            class="min-h-screen bg-slate-50 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
        >
            <!-- =========================================================
                 HEADER
            ========================================================== -->

            <section
                class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
                >
                    <div
                        class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
                    >
                        <div>
                            <div
                                class="mb-3 flex items-center gap-2 text-sm"
                            >
                                <Link
                                    :href="route('admin.dashboard')"
                                    class="font-medium text-slate-500 transition hover:text-green-600 dark:text-slate-400 dark:hover:text-green-400"
                                >
                                    Dashboard
                                </Link>

                                <svg
                                    class="h-4 w-4 text-slate-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="m9 18 6-6-6-6"
                                    />
                                </svg>

                                <span
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    Reports
                                </span>
                            </div>

                            <p
                                class="text-sm font-semibold uppercase tracking-[0.18em] text-green-600 dark:text-green-400"
                            >
                                Business intelligence
                            </p>

                            <h1
                                class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white sm:text-4xl"
                            >
                                Reports
                            </h1>

                            <p
                                class="mt-3 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400 sm:text-base"
                            >
                                Monitor sales, purchases, suppliers,
                                products, inventory and pharmacy
                                performance from one place.
                            </p>
                        </div>

                        <!-- Period -->

                        <div
                            class="flex flex-wrap items-center gap-3"
                        >
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-1 dark:border-slate-700 dark:bg-slate-800"
                            >
                                <button
                                    type="button"
                                    @click="changePeriod(7)"
                                    class="rounded-lg px-3 py-2 text-xs font-semibold transition"
                                    :class="
                                        selectedPeriod === '7'
                                            ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white'
                                            : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                    "
                                >
                                    7 Days
                                </button>

                                <button
                                    type="button"
                                    @click="changePeriod(30)"
                                    class="rounded-lg px-3 py-2 text-xs font-semibold transition"
                                    :class="
                                        selectedPeriod === '30'
                                            ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white'
                                            : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                    "
                                >
                                    30 Days
                                </button>

                                <button
                                    type="button"
                                    @click="changePeriod(90)"
                                    class="rounded-lg px-3 py-2 text-xs font-semibold transition"
                                    :class="
                                        selectedPeriod === '90'
                                            ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white'
                                            : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                    "
                                >
                                    90 Days
                                </button>
                            </div>

                            <button
                                type="button"
                                @click="exportReport"
                                :disabled="exporting"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"
                                    />
                                </svg>

                                {{
                                    exporting
                                        ? 'Preparing...'
                                        : 'Export'
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =========================================================
                 CONTENT
            ========================================================== -->

            <main
                class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
            >
                <!-- =====================================================
                     KPI CARDS
                ====================================================== -->

                <div
                    class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <!-- Total Sales -->

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex items-start justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                >
                                    Total Sales
                                </p>

                                <p
                                    class="mt-2 text-2xl font-extrabold text-slate-950 dark:text-white"
                                >
                                    {{
                                        formatMoney(
                                            summary.total_sales,
                                        )
                                    }}
                                </p>

                                <p
                                    class="mt-2 text-xs text-slate-400 dark:text-slate-500"
                                >
                                    {{ selectedPeriodLabel }}
                                </p>
                            </div>

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-100 font-bold text-green-700 dark:bg-green-950/40 dark:text-green-400"
                            >
                                ₦
                            </div>
                        </div>
                    </div>

                    <!-- POS -->

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex items-start justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                >
                                    POS Sales
                                </p>

                                <p
                                    class="mt-2 text-2xl font-extrabold text-slate-950 dark:text-white"
                                >
                                    {{
                                        formatMoney(
                                            summary.pos_sales,
                                        )
                                    }}
                                </p>

                                <p
                                    class="mt-2 text-xs text-slate-400 dark:text-slate-500"
                                >
                                    {{ posPercentage }}% of sales
                                </p>
                            </div>

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-950/40 dark:text-blue-400"
                            >
                                POS
                            </div>
                        </div>
                    </div>

                    <!-- Online -->

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex items-start justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                >
                                    Online Sales
                                </p>

                                <p
                                    class="mt-2 text-2xl font-extrabold text-slate-950 dark:text-white"
                                >
                                    {{
                                        formatMoney(
                                            summary.online_sales,
                                        )
                                    }}
                                </p>

                                <p
                                    class="mt-2 text-xs text-slate-400 dark:text-slate-500"
                                >
                                    {{ onlinePercentage }}% of sales
                                </p>
                            </div>

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-100 text-xs font-bold text-purple-700 dark:bg-purple-950/40 dark:text-purple-400"
                            >
                                WEB
                            </div>
                        </div>
                    </div>

                    <!-- Today -->

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex items-start justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                >
                                    Today's Sales
                                </p>

                                <p
                                    class="mt-2 text-2xl font-extrabold text-slate-950 dark:text-white"
                                >
                                    {{
                                        formatMoney(
                                            summary.today_sales,
                                        )
                                    }}
                                </p>

                                <p
                                    class="mt-2 text-xs text-slate-400 dark:text-slate-500"
                                >
                                    Current business day
                                </p>
                            </div>

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-xs font-bold text-amber-700 dark:bg-amber-950/40 dark:text-amber-400"
                            >
                                TODAY
                            </div>
                        </div>
                    </div>
                </div>

                <!-- =====================================================
                     PURCHASE + SUPPLIER + INVENTORY KPI
                ====================================================== -->

                <div
                    class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <!-- Purchases -->

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-sm font-medium text-slate-500 dark:text-slate-400"
                        >
                            Purchase Value
                        </p>

                        <p
                            class="mt-2 text-2xl font-extrabold text-slate-950 dark:text-white"
                        >
                            {{
                                formatMoney(
                                    summary.purchase_value ??
                                        purchaseSummary.value,
                                )
                            }}
                        </p>

                        <p
                            class="mt-2 text-xs text-slate-400 dark:text-slate-500"
                        >
                            {{ formatNumber(
                                summary.total_purchases ??
                                purchaseSummary.total
                            ) }}
                            purchase records
                        </p>
                    </div>

                    <!-- Purchased Items -->

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-sm font-medium text-slate-500 dark:text-slate-400"
                        >
                            Items Purchased
                        </p>

                        <p
                            class="mt-2 text-2xl font-extrabold text-slate-950 dark:text-white"
                        >
                            {{
                                formatNumber(
                                    summary.purchased_items ??
                                        purchaseSummary.items,
                                )
                            }}
                        </p>

                        <p
                            class="mt-2 text-xs text-slate-400 dark:text-slate-500"
                        >
                            Stock received
                        </p>
                    </div>

                    <!-- Suppliers -->

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-sm font-medium text-slate-500 dark:text-slate-400"
                        >
                            Suppliers
                        </p>

                        <p
                            class="mt-2 text-2xl font-extrabold text-slate-950 dark:text-white"
                        >
                            {{
                                formatNumber(
                                    summary.total_suppliers ??
                                        supplierSummary.total,
                                )
                            }}
                        </p>

                        <p
                            class="mt-2 text-xs text-slate-400 dark:text-slate-500"
                        >
                            {{
                                formatNumber(
                                    summary.active_suppliers ??
                                        supplierSummary.active,
                                )
                            }}
                            active suppliers
                        </p>
                    </div>

                    <!-- Expiry -->

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-sm font-medium text-slate-500 dark:text-slate-400"
                        >
                            Expiry Alerts
                        </p>

                        <p
                            class="mt-2 text-2xl font-extrabold text-red-600 dark:text-red-400"
                        >
                            {{
                                formatNumber(
                                    summary.expired_products,
                                )
                            }}
                        </p>

                        <p
                            class="mt-2 text-xs text-amber-600 dark:text-amber-400"
                        >
                            {{
                                formatNumber(
                                    summary.expiring_products,
                                )
                            }}
                            expiring soon
                        </p>
                    </div>
                </div>

                <!-- =====================================================
                     SALES CHANNELS
                ====================================================== -->

                <section
                    class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2
                                class="text-lg font-bold text-slate-950 dark:text-white"
                            >
                                Sales Channels
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Completed sales split between
                                your physical POS and online
                                pharmacy.
                            </p>
                        </div>

                        <span
                            class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"
                        >
                            {{ selectedPeriodLabel }}
                        </span>
                    </div>

                    <div
                        class="mt-6 grid gap-4 md:grid-cols-2"
                    >
                        <div
                            class="rounded-xl border border-blue-100 bg-blue-50/70 p-5 dark:border-blue-900/50 dark:bg-blue-950/20"
                        >
                            <div
                                class="flex items-center justify-between"
                            >
                                <div>
                                    <p
                                        class="text-sm font-semibold text-blue-700 dark:text-blue-400"
                                    >
                                        POS Sales
                                    </p>

                                    <p
                                        class="mt-2 text-2xl font-extrabold text-slate-950 dark:text-white"
                                    >
                                        {{
                                            formatMoney(
                                                summary.pos_sales,
                                            )
                                        }}
                                    </p>
                                </div>

                                <span
                                    class="text-xs font-bold text-blue-600 dark:text-blue-400"
                                >
                                    {{ posPercentage }}%
                                </span>
                            </div>

                            <div
                                class="mt-4 h-2 overflow-hidden rounded-full bg-blue-100 dark:bg-blue-950"
                            >
                                <div
                                    class="h-full rounded-full bg-blue-500"
                                    :style="{
                                        width: `${posPercentage}%`,
                                    }"
                                ></div>
                            </div>
                        </div>

                        <div
                            class="rounded-xl border border-purple-100 bg-purple-50/70 p-5 dark:border-purple-900/50 dark:bg-purple-950/20"
                        >
                            <div
                                class="flex items-center justify-between"
                            >
                                <div>
                                    <p
                                        class="text-sm font-semibold text-purple-700 dark:text-purple-400"
                                    >
                                        Online Sales
                                    </p>

                                    <p
                                        class="mt-2 text-2xl font-extrabold text-slate-950 dark:text-white"
                                    >
                                        {{
                                            formatMoney(
                                                summary.online_sales,
                                            )
                                        }}
                                    </p>
                                </div>

                                <span
                                    class="text-xs font-bold text-purple-600 dark:text-purple-400"
                                >
                                    {{ onlinePercentage }}%
                                </span>
                            </div>

                            <div
                                class="mt-4 h-2 overflow-hidden rounded-full bg-purple-100 dark:bg-purple-950"
                            >
                                <div
                                    class="h-full rounded-full bg-purple-500"
                                    :style="{
                                        width: `${onlinePercentage}%`,
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- =====================================================
                     SALES PERFORMANCE
                ====================================================== -->

                <section
                    class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2
                                class="text-lg font-bold text-slate-950 dark:text-white"
                            >
                                Sales Performance
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Combined daily sales from POS
                                and online orders.
                            </p>
                        </div>

                        <span
                            class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"
                        >
                            {{ selectedPeriodLabel }}
                        </span>
                    </div>

                    <div
                        v-if="salesByDay.length"
                        class="mt-8 overflow-x-auto"
                    >
                        <div
                            class="flex min-w-[700px] items-end gap-2"
                            style="height: 280px"
                        >
                            <div
                                v-for="(item, index) in salesByDay"
                                :key="item.date ?? index"
                                class="group flex h-full min-w-[28px] flex-1 flex-col items-center justify-end"
                            >
                                <div
                                    class="relative flex h-56 w-full items-end justify-center"
                                >
                                    <div
                                        class="w-full max-w-[34px] rounded-t-lg bg-green-500 transition hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-500"
                                        :style="{
                                            height: `${Math.max(
                                                5,
                                                (Number(item.total || 0) /
                                                    maxSales) *
                                                    100,
                                            )}%`,
                                        }"
                                        :title="`${formatMoney(item.total)} — ${item.date}`"
                                    ></div>
                                </div>

                                <span
                                    class="mt-2 text-[10px] text-slate-400 dark:text-slate-500"
                                >
                                    {{ item.label ?? item.date }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="flex h-64 items-center justify-center text-sm text-slate-400 dark:text-slate-500"
                    >
                        No completed sales data available.
                    </div>
                </section>

                <!-- =====================================================
                     TODAY + ORDER SUMMARY
                ====================================================== -->

                <div
                    class="mt-6 grid gap-5 md:grid-cols-4"
                >
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-sm text-slate-500 dark:text-slate-400"
                        >
                            Today's POS
                        </p>

                        <p
                            class="mt-2 text-xl font-extrabold text-slate-950 dark:text-white"
                        >
                            {{
                                formatMoney(
                                    summary.today_pos_sales,
                                )
                            }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-sm text-slate-500 dark:text-slate-400"
                        >
                            Today's Online
                        </p>

                        <p
                            class="mt-2 text-xl font-extrabold text-slate-950 dark:text-white"
                        >
                            {{
                                formatMoney(
                                    summary.today_online_sales,
                                )
                            }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-sm text-slate-500 dark:text-slate-400"
                        >
                            Completed Orders
                        </p>

                        <p
                            class="mt-2 text-xl font-extrabold text-green-600 dark:text-green-400"
                        >
                            {{
                                formatNumber(
                                    summary.completed_orders,
                                )
                            }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-sm text-slate-500 dark:text-slate-400"
                        >
                            Average Order
                        </p>

                        <p
                            class="mt-2 text-xl font-extrabold text-slate-950 dark:text-white"
                        >
                            {{
                                formatMoney(
                                    summary.average_order_value,
                                )
                            }}
                        </p>
                    </div>
                </div>

                <!-- =====================================================
                     ORDERS + PAYMENTS
                ====================================================== -->

                <div
                    class="mt-6 grid gap-6 lg:grid-cols-2"
                >
                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2
                            class="text-lg font-bold text-slate-950 dark:text-white"
                        >
                            Order Status
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Order status distribution.
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
                                    class="rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="
                                        statusClass(item.status)
                                    "
                                >
                                    {{
                                        readableStatus(
                                            item.status,
                                        )
                                    }}
                                </span>

                                <span
                                    class="text-sm font-bold text-slate-900 dark:text-white"
                                >
                                    {{
                                        formatNumber(
                                            item.total,
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-else
                            class="py-10 text-center text-sm text-slate-400"
                        >
                            No order status data available.
                        </div>
                    </section>

                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2
                            class="text-lg font-bold text-slate-950 dark:text-white"
                        >
                            Payment Status
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Payment status across orders.
                        </p>

                        <div
                            v-if="paymentStatuses.length"
                            class="mt-6 space-y-4"
                        >
                            <div
                                v-for="item in paymentStatuses"
                                :key="item.status"
                                class="flex items-center justify-between gap-4"
                            >
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="
                                        statusClass(item.status)
                                    "
                                >
                                    {{
                                        readableStatus(
                                            item.status,
                                        )
                                    }}
                                </span>

                                <span
                                    class="text-sm font-bold text-slate-900 dark:text-white"
                                >
                                    {{
                                        formatNumber(
                                            item.total,
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-else
                            class="py-10 text-center text-sm text-slate-400"
                        >
                            No payment data available.
                        </div>
                    </section>
                </div>

                <!-- =====================================================
                     BEST SELLING PRODUCTS
                ====================================================== -->

                <section
                    class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="border-b border-slate-200 px-6 py-5 dark:border-slate-800"
                    >
                        <h2
                            class="text-lg font-bold text-slate-950 dark:text-white"
                        >
                            Best-Selling Products
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Products with the highest completed
                            sales volume.
                        </p>
                    </div>

                    <div
                        v-if="topProducts.length"
                        class="divide-y divide-slate-100 dark:divide-slate-800"
                    >
                        <div
                            v-for="(product, index) in topProducts"
                            :key="product.id ?? index"
                            class="flex items-center justify-between gap-4 px-6 py-4"
                        >
                            <div
                                class="flex min-w-0 items-center gap-3"
                            >
                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    {{ index + 1 }}
                                </span>

                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ product.name }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                    >
                                        {{
                                            formatNumber(
                                                product.quantity,
                                            )
                                        }}
                                        units sold
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="product.revenue !== undefined"
                                class="shrink-0 text-right"
                            >
                                <p
                                    class="text-sm font-bold text-slate-900 dark:text-white"
                                >
                                    {{
                                        formatMoney(
                                            product.revenue,
                                        )
                                    }}
                                </p>

                                <p
                                    class="text-[11px] text-slate-400"
                                >
                                    Revenue
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="px-6 py-12 text-center text-sm text-slate-400"
                    >
                        No product sales data available.
                    </div>
                </section>

                <!-- =====================================================
                     PURCHASES + SUPPLIERS
                ====================================================== -->

                <div
                    class="mt-6 grid gap-6 lg:grid-cols-2"
                >
                    <!-- Purchases -->

                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="border-b border-slate-200 px-6 py-5 dark:border-slate-800"
                        >
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <div>
                                    <h2
                                        class="text-lg font-bold text-slate-950 dark:text-white"
                                    >
                                        Recent Purchases
                                    </h2>

                                    <p
                                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Recent stock purchases and
                                        their suppliers.
                                    </p>
                                </div>

                                <Link
                                    :href="
                                        route(
                                            'admin.purchases.index',
                                        )
                                    "
                                    class="text-xs font-bold text-green-600 hover:text-green-700 dark:text-green-400"
                                >
                                    Purchases
                                </Link>
                            </div>
                        </div>

                        <div
                            v-if="recentPurchases.length"
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <div
                                v-for="purchase in recentPurchases"
                                :key="purchase.id"
                                class="flex items-center justify-between gap-4 px-6 py-4"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            purchase.reference ??
                                            purchase.purchase_number ??
                                            `Purchase #${purchase.id}`
                                        }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                    >
                                        {{
                                            purchase.supplier_name ??
                                            purchase.supplier?.name ??
                                            'Unknown supplier'
                                        }}

                                        <span class="mx-1">
                                            •
                                        </span>

                                        {{
                                            formatDate(
                                                purchase.purchase_date ??
                                                    purchase.created_at,
                                            )
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="shrink-0 text-right"
                                >
                                    <p
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            formatMoney(
                                                purchase.total ??
                                                    purchase.total_amount ??
                                                    purchase.amount,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="px-6 py-12 text-center text-sm text-slate-400"
                        >
                            No purchase data available.
                        </div>
                    </section>

                    <!-- Suppliers -->

                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="border-b border-slate-200 px-6 py-5"
                        >
                            <h2
                                class="text-lg font-bold text-slate-950 dark:text-white"
                            >
                                Top Suppliers
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Suppliers ranked by purchase value.
                            </p>
                        </div>

                        <div
                            v-if="topSuppliers.length"
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <div
                                v-for="(supplier, index) in topSuppliers"
                                :key="supplier.id ?? index"
                                class="flex items-center justify-between gap-4 px-6 py-4"
                            >
                                <div
                                    class="flex min-w-0 items-center gap-3"
                                >
                                    <span
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                    >
                                        {{ index + 1 }}
                                    </span>

                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            {{ supplier.name }}
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                        >
                                            {{
                                                formatNumber(
                                                    supplier.purchase_count ??
                                                        supplier.purchases_count,
                                                )
                                            }}
                                            purchases
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="shrink-0 text-right"
                                >
                                    <p
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            formatMoney(
                                                supplier.total ??
                                                    supplier.total_purchase_value ??
                                                    supplier.purchase_value,
                                            )
                                        }}
                                    </p>

                                    <p
                                        class="text-[11px] text-slate-400 dark:text-slate-500"
                                    >
                                        Purchase value
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="px-6 py-12 text-center text-sm text-slate-400"
                        >
                            No supplier purchase data available.
                        </div>
                    </section>
                </div>

                <!-- =====================================================
                     EXPIRY REPORT
                ====================================================== -->

                <div
                    class="mt-6 grid gap-6 lg:grid-cols-2"
                >
                    <!-- Expired -->

                    <section
                        class="overflow-hidden rounded-2xl border border-red-200 bg-white shadow-sm dark:border-red-900/50 dark:bg-slate-900"
                    >
                        <div
                            class="border-b border-red-100 bg-red-50/70 px-6 py-5 dark:border-red-900/40 dark:bg-red-950/20"
                        >
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <div>
                                    <h2
                                        class="text-lg font-bold text-red-700 dark:text-red-400"
                                    >
                                        Expired Products
                                    </h2>

                                    <p
                                        class="mt-1 text-sm text-red-600/70 dark:text-red-400/70"
                                    >
                                        Products that should not be
                                        available for sale.
                                    </p>
                                </div>

                                <span
                                    class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700 dark:bg-red-950/50 dark:text-red-400"
                                >
                                    {{
                                        formatNumber(
                                            expiredProducts.length,
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="expiredProducts.length"
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <div
                                v-for="product in expiredProducts"
                                :key="product.id ?? product.batch_id"
                                class="flex items-center justify-between gap-4 px-6 py-4"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ product.name }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                    >
                                        Batch:
                                        {{
                                            product.batch_number ??
                                            '—'
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="shrink-0 text-right"
                                >
                                    <p
                                        class="text-sm font-bold text-red-600 dark:text-red-400"
                                    >
                                        {{
                                            formatDate(
                                                product.expiry_date,
                                            )
                                        }}
                                    </p>

                                    <p
                                        class="mt-1 text-[11px] text-slate-400"
                                    >
                                        Expired
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="px-6 py-12 text-center text-sm font-medium text-green-600 dark:text-green-400"
                        >
                            No expired products found.
                        </div>
                    </section>

                    <!-- Expiring Soon -->

                    <section
                        class="overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm dark:border-amber-900/50 dark:bg-slate-900"
                    >
                        <div
                            class="border-b border-amber-100 bg-amber-50/70 px-6 py-5 dark:border-amber-900/40 dark:bg-amber-950/20"
                        >
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <div>
                                    <h2
                                        class="text-lg font-bold text-amber-700 dark:text-amber-400"
                                    >
                                        Expiring Soon
                                    </h2>

                                    <p
                                        class="mt-1 text-sm text-amber-600/70 dark:text-amber-400/70"
                                    >
                                        Products approaching their
                                        expiry date.
                                    </p>
                                </div>

                                <span
                                    class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700 dark:bg-amber-950/50 dark:text-amber-400"
                                >
                                    {{
                                        formatNumber(
                                            expiringProducts.length,
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="expiringProducts.length"
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <div
                                v-for="product in expiringProducts"
                                :key="product.id ?? product.batch_id"
                                class="flex items-center justify-between gap-4 px-6 py-4"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ product.name }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                    >
                                        Batch:
                                        {{
                                            product.batch_number ??
                                            '—'
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="shrink-0 text-right"
                                >
                                    <p
                                        class="text-sm font-bold"
                                        :class="
                                            expiryClass(product)
                                        "
                                    >
                                        {{
                                            product.days_remaining !==
                                            undefined
                                                ? `${product.days_remaining} days`
                                                : formatDate(
                                                      product.expiry_date,
                                                  )
                                        }}
                                    </p>

                                    <p
                                        class="mt-1 text-[11px] text-slate-400"
                                    >
                                        {{
                                            formatDate(
                                                product.expiry_date,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="px-6 py-12 text-center text-sm font-medium text-green-600 dark:text-green-400"
                        >
                            No products are approaching expiry.
                        </div>
                    </section>
                </div>

                <!-- =====================================================
                     LOW STOCK
                ====================================================== -->

                <section
                    class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="border-b border-slate-200 px-6 py-5 dark:border-slate-800"
                    >
                        <div
                            class="flex items-center justify-between gap-4"
                        >
                            <div>
                                <h2
                                    class="text-lg font-bold text-slate-950 dark:text-white"
                                >
                                    Low Stock
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Products requiring inventory
                                    attention.
                                </p>
                            </div>

                            <Link
                                :href="
                                    route(
                                        'admin.inventory.index',
                                    )
                                "
                                class="text-xs font-bold text-green-600 hover:text-green-700 dark:text-green-400"
                            >
                                Inventory
                            </Link>
                        </div>
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
                                <p
                                    class="truncate text-sm font-semibold text-slate-900 dark:text-white"
                                >
                                    {{ product.name }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                >
                                    SKU:
                                    {{ product.sku ?? '—' }}
                                </p>
                            </div>

                            <div
                                class="shrink-0 text-right"
                            >
                                <p
                                    class="text-sm font-bold"
                                    :class="
                                        Number(product.quantity) <=
                                        0
                                            ? 'text-red-600 dark:text-red-400'
                                            : 'text-amber-600 dark:text-amber-400'
                                    "
                                >
                                    {{
                                        formatNumber(
                                            product.quantity,
                                        )
                                    }}
                                </p>

                                <p
                                    class="mt-1 text-[11px] text-slate-400 dark:text-slate-500"
                                >
                                    Minimum:
                                    {{
                                        formatNumber(
                                            product.minimum_stock,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="px-6 py-12 text-center text-sm font-medium text-green-600 dark:text-green-400"
                    >
                        Inventory levels look healthy.
                    </div>
                </section>

                <!-- =====================================================
                     DETAILED REPORTS
                ====================================================== -->

                <section
                    class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="text-lg font-bold text-slate-950 dark:text-white"
                    >
                        Detailed Reports
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                    >
                        Open the operational areas for more detailed
                        information.
                    </p>

                    <div
                        class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <Link
                            :href="
                                route(
                                    'admin.orders.index',
                                )
                            "
                            class="rounded-xl border border-slate-200 p-4 transition hover:border-green-300 hover:bg-green-50 dark:border-slate-700 dark:hover:border-green-700 dark:hover:bg-green-950/20"
                        >
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                Sales & Orders
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                Review online order activity.
                            </p>
                        </Link>

                        <Link
                            :href="
                                route(
                                    'admin.pos.history',
                                )
                            "
                            class="rounded-xl border border-slate-200 p-4 transition hover:border-green-300 hover:bg-green-50 dark:border-slate-700 dark:hover:border-green-700 dark:hover:bg-green-950/20"
                        >
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                POS Sales
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                View POS sales history.
                            </p>
                        </Link>

                        <Link
                            :href="
                                route(
                                    'admin.inventory.index',
                                )
                            "
                            class="rounded-xl border border-slate-200 p-4 transition hover:border-green-300 hover:bg-green-50 dark:border-slate-700 dark:hover:border-green-700 dark:hover:bg-green-950/20"
                        >
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                Inventory
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                Review stock, batches and expiry.
                            </p>
                        </Link>

                        <Link
                            :href="
                                route(
                                    'admin.products.index',
                                )
                            "
                            class="rounded-xl border border-slate-200 p-4 transition hover:border-green-300 hover:bg-green-50 dark:border-slate-700 dark:hover:border-green-700 dark:hover:bg-green-950/20"
                        >
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                Products
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                Review products and performance.
                            </p>
                        </Link>
                    </div>
                </section>
            </main>
        </div>
    </AdminLayout>
</template>