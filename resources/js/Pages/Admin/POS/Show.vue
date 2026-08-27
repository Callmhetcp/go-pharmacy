<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const formatMoney = (value) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
    }).format(Number(value ?? 0));
};

const formatDate = (value) => {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat('en-NG', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const payment = computed(() => {
    return props.order.payments?.[0] ?? null;
});

const paymentLabel = computed(() => {
    const method = payment.value?.payment_method;

    if (method === 'cash') {
        return 'Cash';
    }

    if (method === 'transfer') {
        return 'Bank Transfer';
    }

    if (method === 'card') {
        return 'Card';
    }

    return method ?? '—';
});

const statusClass = computed(() => {
    if (props.order.status === 'completed') {
        return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400';
    }

    if (props.order.status === 'cancelled') {
        return 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400';
    }

    return 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400';
});

const paymentStatusClass = computed(() => {
    if (props.order.payment_status === 'paid') {
        return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400';
    }

    return 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400';
});
</script>

<template>
    <AdminLayout>
        <div
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 transition-colors dark:bg-slate-950 dark:text-white sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-7xl">

                <!-- Breadcrumbs -->
                <div class="mb-6 flex items-center gap-2 text-sm">
                    <Link
                        :href="route('admin.dashboard')"
                        class="text-slate-500 hover:text-green-600 dark:text-slate-400 dark:hover:text-green-400"
                    >
                        Dashboard
                    </Link>

                    <span class="text-slate-400">
                        /
                    </span>

                    <Link
                        :href="route('admin.pos.history')"
                        class="text-slate-500 hover:text-green-600 dark:text-slate-400 dark:hover:text-green-400"
                    >
                        Sales History
                    </Link>

                    <span class="text-slate-400">
                        /
                    </span>

                    <span
                        class="font-semibold text-slate-900 dark:text-white"
                    >
                        Sale Details
                    </span>
                </div>

                <!-- Header -->
                <div
                    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h1
                            class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                        >
                            Sale Details
                        </h1>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            {{ order.order_number }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Link
                            :href="route('admin.pos.history')"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        >
                            ← Sales History
                        </Link>

                        <Link
                            :href="
                                route(
                                    'admin.pos.receipt',
                                    order.id
                                )
                            "
                            class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700"
                        >
                            Receipt
                        </Link>
                    </div>
                </div>

                <!-- Sale Overview -->
                <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            Sale Number
                        </p>

                        <p
                            class="mt-2 font-bold text-slate-900 dark:text-white"
                        >
                            {{ order.order_number }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            Date
                        </p>

                        <p
                            class="mt-2 font-semibold text-slate-900 dark:text-white"
                        >
                            {{ formatDate(order.created_at) }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            Sale Status
                        </p>

                        <span
                            class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize"
                            :class="statusClass"
                        >
                            {{ order.status }}
                        </span>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            Payment Status
                        </p>

                        <span
                            class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize"
                            :class="paymentStatusClass"
                        >
                            {{ order.payment_status }}
                        </span>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">

                    <!-- Main -->
                    <div class="space-y-6 lg:col-span-2">

                        <!-- Products -->
                        <div
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div
                                class="border-b border-slate-200 px-5 py-4 dark:border-slate-800"
                            >
                                <h2
                                    class="font-bold text-slate-900 dark:text-white"
                                >
                                    Products
                                </h2>
                            </div>

                            <div
                                class="divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="flex items-center justify-between gap-4 px-5 py-4"
                                >
                                    <div class="min-w-0">
                                        <p
                                            class="font-semibold text-slate-900 dark:text-white"
                                        >
                                            {{ item.product_name }}
                                        </p>

                                        <div
                                            class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            <span v-if="item.sku">
                                                SKU: {{ item.sku }}
                                            </span>

                                            <span>
                                                Qty:
                                                {{ item.quantity }}
                                            </span>

                                            <span>
                                                {{
                                                    item.selling_unit
                                                }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="shrink-0 text-right">
                                        <p
                                            class="font-semibold text-slate-900 dark:text-white"
                                        >
                                            {{
                                                formatMoney(
                                                    item.subtotal
                                                )
                                            }}
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                formatMoney(
                                                    item.unit_price
                                                )
                                            }}
                                            each
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment -->
                        <div
                            class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div
                                class="border-b border-slate-200 px-5 py-4 dark:border-slate-800"
                            >
                                <h2
                                    class="font-bold text-slate-900 dark:text-white"
                                >
                                    Payment
                                </h2>
                            </div>

                            <div class="grid gap-4 p-5 sm:grid-cols-2">
                                <div>
                                    <p
                                        class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                    >
                                        Method
                                    </p>

                                    <p
                                        class="mt-1 font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ paymentLabel }}
                                    </p>
                                </div>

                                <div>
                                    <p
                                        class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                    >
                                        Amount
                                    </p>

                                    <p
                                        class="mt-1 font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            formatMoney(
                                                payment?.amount ??
                                                    order.total
                                            )
                                        }}
                                    </p>
                                </div>

                                <div
                                    v-if="payment?.reference"
                                >
                                    <p
                                        class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                    >
                                        Reference
                                    </p>

                                    <p
                                        class="mt-1 break-all font-medium text-slate-900 dark:text-white"
                                    >
                                        {{ payment.reference }}
                                    </p>
                                </div>

                                <div
                                    v-if="payment?.created_at"
                                >
                                    <p
                                        class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                    >
                                        Paid At
                                    </p>

                                    <p
                                        class="mt-1 font-medium text-slate-900 dark:text-white"
                                    >
                                        {{
                                            formatDate(
                                                payment.created_at
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div
                            v-if="order.notes"
                            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                        >
                            <h2
                                class="font-bold text-slate-900 dark:text-white"
                            >
                                Notes
                            </h2>

                            <p
                                class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                            >
                                {{ order.notes }}
                            </p>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">

                        <!-- Cashier -->
                        <div
                            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-100 text-green-600 dark:bg-green-500/15 dark:text-green-400"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M15 19a6 6 0 0 0-12 0m6-8a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6-3v6m3-3h-6"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <p
                                        class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                    >
                                        Cashier
                                    </p>

                                    <p
                                        class="font-bold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            order.cashier?.name ??
                                            'Unknown'
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="order.cashier?.email"
                                class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800"
                            >
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    {{ order.cashier.email }}
                                </p>
                            </div>
                        </div>

                        <!-- Customer -->
                        <div
                            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                        >
                            <h2
                                class="font-bold text-slate-900 dark:text-white"
                            >
                                Customer
                            </h2>

                            <div class="mt-4 space-y-3">
                                <div>
                                    <p
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Name
                                    </p>

                                    <p
                                        class="mt-1 font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ order.customer_name }}
                                    </p>
                                </div>

                                <div v-if="order.customer_phone">
                                    <p
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Phone
                                    </p>

                                    <p
                                        class="mt-1 font-medium text-slate-900 dark:text-white"
                                    >
                                        {{ order.customer_phone }}
                                    </p>
                                </div>

                                <div v-if="order.customer_email">
                                    <p
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Email
                                    </p>

                                    <p
                                        class="mt-1 break-all font-medium text-slate-900 dark:text-white"
                                    >
                                        {{ order.customer_email }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div
                            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                        >
                            <h2
                                class="font-bold text-slate-900 dark:text-white"
                            >
                                Sale Summary
                            </h2>

                            <div
                                class="mt-4 space-y-3 text-sm"
                            >
                                <div
                                    class="flex justify-between gap-4"
                                >
                                    <span
                                        class="text-slate-500 dark:text-slate-400"
                                    >
                                        Subtotal
                                    </span>

                                    <span
                                        class="font-medium text-slate-900 dark:text-white"
                                    >
                                        {{
                                            formatMoney(
                                                order.subtotal
                                            )
                                        }}
                                    </span>
                                </div>

                                <div
                                    v-if="Number(order.delivery_fee) > 0"
                                    class="flex justify-between gap-4"
                                >
                                    <span
                                        class="text-slate-500 dark:text-slate-400"
                                    >
                                        Delivery
                                    </span>

                                    <span
                                        class="font-medium text-slate-900 dark:text-white"
                                    >
                                        {{
                                            formatMoney(
                                                order.delivery_fee
                                            )
                                        }}
                                    </span>
                                </div>

                                <div
                                    v-if="Number(order.discount) > 0"
                                    class="flex justify-between gap-4"
                                >
                                    <span
                                        class="text-slate-500 dark:text-slate-400"
                                    >
                                        Discount
                                    </span>

                                    <span
                                        class="font-medium text-red-600 dark:text-red-400"
                                    >
                                        -
                                        {{
                                            formatMoney(
                                                order.discount
                                            )
                                        }}
                                    </span>
                                </div>

                                <div
                                    class="border-t border-slate-200 pt-4 dark:border-slate-800"
                                >
                                    <div
                                        class="flex justify-between gap-4"
                                    >
                                        <span
                                            class="font-bold text-slate-900 dark:text-white"
                                        >
                                            Total
                                        </span>

                                        <span
                                            class="text-xl font-extrabold text-green-600 dark:text-green-400"
                                        >
                                            {{
                                                formatMoney(
                                                    order.total
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>