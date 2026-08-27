<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },

    settings: {
        type: Object,
        required: true,
    },
});

const formatMoney = (value) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
    }).format(Number(value || 0));
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

const printReceipt = () => {
    window.print();
};

const general = props.settings?.general ?? {};
const receipt = props.settings?.receipt ?? {};
</script>

<template>
    <AdminLayout>
        <div
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 transition-colors dark:bg-slate-950 dark:text-white print:min-h-0 print:bg-white print:p-0 print:text-black"
        >
            <div class="mx-auto max-w-5xl">

                <!-- Breadcrumb / Actions -->
                <div
                    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden"
                >
                    <div>
                        <div class="flex items-center gap-2 text-sm">
                            <Link
                                :href="route('admin.dashboard')"
                                class="text-slate-500 transition hover:text-green-600 dark:text-slate-400 dark:hover:text-green-400"
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
                                    stroke-width="2"
                                    d="m9 5 7 7-7 7"
                                />
                            </svg>

                            <Link
                                :href="route('admin.pos.history')"
                                class="text-slate-500 transition hover:text-green-600 dark:text-slate-400 dark:hover:text-green-400"
                            >
                                Sales History
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
                                    stroke-width="2"
                                    d="m9 5 7 7-7 7"
                                />
                            </svg>

                            <span
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                Receipt
                            </span>
                        </div>

                        <h1
                            class="mt-4 text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                        >
                            Sale Receipt
                        </h1>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            View or print the receipt for this POS sale.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Link
                            :href="route('admin.pos.history')"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
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
                                    stroke-width="2"
                                    d="M15 19l-7-7 7-7"
                                />
                            </svg>

                            Sales History
                        </Link>

                        <Link
                            :href="route('admin.pos.index')"
                            class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700"
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
                                    stroke-width="2"
                                    d="M12 5v14m-7-7h14"
                                />
                            </svg>

                            New POS Sale
                        </Link>

                        <button
                            type="button"
                            @click="printReceipt"
                            class="inline-flex items-center gap-2 rounded-xl border border-green-600 bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700"
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
                                    d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2m-10 0h8v3H8v-3Z"
                                />
                            </svg>

                            Print Receipt
                        </button>
                    </div>
                </div>

                <!-- Receipt -->
                <div class="flex justify-center">
                    <div
                        id="pos-receipt"
                        class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 print:max-w-[80mm] print:rounded-none print:border-0 print:p-0 print:shadow-none"
                    >

                        <!-- Header -->
                        <div class="text-center">

                            <img
                                v-if="receipt['receipt.show_logo'] && settings.logo"
                                :src="settings.logo"
                                alt="Logo"
                                class="mx-auto mb-3 h-14 w-auto object-contain print:max-h-12"
                            />

                            <h2
                                class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white print:text-black"
                            >
                                {{ general['business.name'] }}
                            </h2>

                            <p
                                v-if="general['business.tagline']"
                                class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400 print:text-gray-600"
                            >
                                {{ general['business.tagline'] }}
                            </p>

                            <div
                                v-if="
                                    general['business.address'] ||
                                    general['business.city'] ||
                                    general['business.state'] ||
                                    general['business.phone'] ||
                                    general['business.email']
                                "
                                class="mt-3 space-y-1 text-xs text-slate-500 dark:text-slate-400 print:text-gray-600"
                            >
                                <p v-if="general['business.address']">
                                    {{ general['business.address'] }}
                                </p>

                                <p
                                    v-if="
                                        general['business.city'] ||
                                        general['business.state']
                                    "
                                >
                                    {{ general['business.city'] }}
                                    <span
                                        v-if="
                                            general['business.city'] &&
                                            general['business.state']
                                        "
                                    >
                                        ,
                                    </span>
                                    {{ general['business.state'] }}
                                </p>

                                <p v-if="general['business.phone']">
                                    {{ general['business.phone'] }}
                                </p>

                                <p v-if="general['business.email']">
                                    {{ general['business.email'] }}
                                </p>
                            </div>

                            <p
                                class="mt-4 text-xs font-semibold text-slate-600 dark:text-slate-300 print:text-gray-700"
                            >
                                {{ receipt['receipt.title'] }}
                            </p>

                            <div
                                class="mt-4 space-y-1 text-xs text-slate-500 dark:text-slate-400 print:text-gray-600"
                            >
                                <p>
                                    <span class="font-semibold">
                                        {{ receipt['receipt.order_label'] }}:
                                    </span>

                                    {{ order.order_number }}
                                </p>

                                <p>
                                    <span class="font-semibold">
                                        {{ receipt['receipt.date_label'] }}:
                                    </span>

                                    {{ formatDate(order.created_at) }}
                                </p>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div
                            class="my-6 border-t border-dashed border-slate-300 dark:border-slate-700 print:border-gray-400"
                        />

                        <!-- Customer -->
                        <div
                            v-if="receipt['receipt.show_customer']"
                            class="mb-5 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60 print:bg-transparent print:p-0"
                        >
                            <p
                                class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 print:text-gray-500"
                            >
                                Customer
                            </p>

                            <p
                                class="mt-1 text-sm font-semibold text-slate-900 dark:text-white print:text-black"
                            >
                                {{ order.customer_name || '—' }}
                            </p>

                            <p
                                v-if="order.customer_phone"
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400 print:text-gray-600"
                            >
                                {{ order.customer_phone }}
                            </p>
                        </div>

                        <!-- Items -->
                        <div class="space-y-4">
                            <div
                                v-for="item in order.items"
                                :key="item.id"
                                class="flex items-start justify-between gap-4"
                            >
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="text-sm font-semibold text-slate-900 dark:text-white print:text-black"
                                    >
                                        {{ item.product_name }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-500 dark:text-slate-400 print:text-gray-600"
                                    >
                                        {{ item.quantity }}
                                        ×
                                        {{ formatMoney(item.unit_price) }}

                                        <span v-if="item.selling_unit">
                                            / {{ item.selling_unit }}
                                        </span>
                                    </p>
                                </div>

                                <p
                                    class="shrink-0 text-sm font-semibold text-slate-900 dark:text-white print:text-black"
                                >
                                    {{ formatMoney(item.subtotal) }}
                                </p>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div
                            class="my-6 border-t border-dashed border-slate-300 dark:border-slate-700 print:border-gray-400"
                        />

                        <!-- Totals -->
                        <div class="space-y-2">
                            <div
                                class="flex justify-between text-sm text-slate-600 dark:text-slate-300 print:text-gray-700"
                            >
                                <span>Subtotal</span>

                                <span>
                                    {{ formatMoney(order.subtotal) }}
                                </span>
                            </div>

                            <div
                                v-if="Number(order.discount) > 0"
                                class="flex justify-between text-sm text-slate-600 dark:text-slate-300 print:text-gray-700"
                            >
                                <span>Discount</span>

                                <span>
                                    -
                                    {{ formatMoney(order.discount) }}
                                </span>
                            </div>

                            <div
                                v-if="
                                    receipt['receipt.show_delivery_fee'] &&
                                    Number(order.delivery_fee) > 0
                                "
                                class="flex justify-between text-sm text-slate-600 dark:text-slate-300 print:text-gray-700"
                            >
                                <span>Delivery</span>

                                <span>
                                    {{ formatMoney(order.delivery_fee) }}
                                </span>
                            </div>

                            <div
                                class="flex items-center justify-between border-t border-slate-200 pt-3 dark:border-slate-700 print:border-gray-400"
                            >
                                <span
                                    class="text-base font-extrabold text-slate-900 dark:text-white print:text-black"
                                >
                                    TOTAL
                                </span>

                                <span
                                    class="text-xl font-extrabold text-green-600 dark:text-green-400 print:text-black"
                                >
                                    {{ formatMoney(order.total) }}
                                </span>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div
                            class="my-6 border-t border-dashed border-slate-300 dark:border-slate-700 print:border-gray-400"
                        />

                        <!-- Payment -->
                        <div class="space-y-2 text-xs">
                            <div
                                v-if="receipt['receipt.show_payment_method']"
                                class="flex justify-between gap-4 text-slate-500 dark:text-slate-400 print:text-gray-600"
                            >
                                <span>Payment Method</span>

                                <span
                                    class="font-semibold uppercase text-slate-900 dark:text-white print:text-black"
                                >
                                    {{
                                        order.payments?.[0]?.payment_method ??
                                        '—'
                                    }}
                                </span>
                            </div>

                            <div
                                v-if="order.payment_status"
                                class="flex justify-between gap-4 text-slate-500 dark:text-slate-400 print:text-gray-600"
                            >
                                <span>Payment Status</span>

                                <span
                                    class="font-semibold capitalize text-slate-900 dark:text-white print:text-black"
                                >
                                    {{ order.payment_status }}
                                </span>
                            </div>

                            <div
                                v-if="
                                    receipt['receipt.show_cashier'] &&
                                    order.cashier
                                "
                                class="flex justify-between gap-4 text-slate-500 dark:text-slate-400 print:text-gray-600"
                            >
                                <span>Cashier</span>

                                <span
                                    class="font-semibold text-slate-900 dark:text-white print:text-black"
                                >
                                    {{ order.cashier.name }}
                                </span>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div
                            v-if="receipt['receipt.footer']"
                            class="mt-8 text-center text-xs text-slate-500 dark:text-slate-400 print:text-gray-600"
                        >
                            <p class="whitespace-pre-line">
                                {{ receipt['receipt.footer'] }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style>
@media print {
    @page {
        size: 80mm auto;
        margin: 5mm;
    }

    html,
    body {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    body * {
        visibility: hidden;
    }

    #pos-receipt,
    #pos-receipt * {
        visibility: visible;
    }

    #pos-receipt {
        position: absolute;
        left: 0;
        top: 0;
        width: 80mm;
        max-width: 80mm;
        margin: 0;
        padding: 0;
    }
}
</style>