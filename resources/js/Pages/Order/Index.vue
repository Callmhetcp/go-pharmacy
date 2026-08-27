<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const props = defineProps({
    orders: {
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

const getPaymentStatusLabel = (order) => {
    const status = order.payment_status ?? order.payment?.status ?? 'pending';

    const labels = {
        pending: 'Payment Pending',
        awaiting_payment: 'Awaiting Payment',
        processing: 'Payment Processing',
        successful: 'Payment Successful',
        paid: 'Paid',
        failed: 'Payment Failed',
        cancelled: 'Payment Cancelled',
        refunded: 'Refunded',
    };

    return labels[status] ?? 'Payment Pending';
};

const getPaymentStatusClass = (order) => {
    const status = order.payment_status ?? order.payment?.status ?? 'pending';

    const classes = {
        pending: 'bg-amber-50 text-amber-700',
        awaiting_payment: 'bg-amber-50 text-amber-700',
        processing: 'bg-blue-50 text-blue-700',
        successful: 'bg-green-50 text-green-700',
        paid: 'bg-green-50 text-green-700',
        failed: 'bg-red-50 text-red-700',
        cancelled: 'bg-slate-100 text-slate-600',
        refunded: 'bg-purple-50 text-purple-700',
    };

    return classes[status] ?? classes.pending;
};

const getOrderStatusLabel = (status) => {
    const labels = {
        pending: 'Order Pending',
        confirmed: 'Order Confirmed',
        processing: 'Processing',
        shipped: 'Shipped',
        delivered: 'Delivered',
        completed: 'Completed',
        cancelled: 'Cancelled',
    };

    return labels[status] ?? 'Order Pending';
};

const getOrderStatusClass = (status) => {
    const classes = {
        pending: 'bg-amber-50 text-amber-700',
        confirmed: 'bg-blue-50 text-blue-700',
        processing: 'bg-indigo-50 text-indigo-700',
        shipped: 'bg-purple-50 text-purple-700',
        delivered: 'bg-green-50 text-green-700',
        completed: 'bg-green-50 text-green-700',
        cancelled: 'bg-red-50 text-red-700',
    };

    return classes[status] ?? classes.pending;
};

const orderData = computed(() => {
    return props.orders?.data ?? [];
});
</script>

<template>
    <Head title="My Orders" />

    <CustomerLayout>
        <main class="min-h-screen bg-slate-50 dark:bg-slate-950">
            <!-- Header -->
            <section
                class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-[0.18em] text-green-600"
                    >
                        My Account
                    </p>

                    <h1
                        class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white"
                    >
                        My Orders
                    </h1>

                    <p
                        class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        View your previous orders, payment status and order
                        details.
                    </p>
                </div>
            </section>

            <!-- Orders -->
            <section
                class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10"
            >
                <!-- Empty -->
                <div
                    v-if="orderData.length === 0"
                    class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-50 text-green-600 dark:bg-green-950/30"
                    >
                        <svg
                            class="h-8 w-8"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M9 5h6M9 3h6a2 2 0 0 1 2 2v1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h1V5a2 2 0 0 1 2-2Z"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-width="1.8"
                                d="m9 13 2 2 4-4"
                            />
                        </svg>
                    </div>

                    <h2
                        class="mt-5 text-lg font-bold text-slate-950 dark:text-white"
                    >
                        You don't have any orders yet
                    </h2>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        Your completed and unpaid orders will appear here so
                        you can always come back and continue your purchase.
                    </p>

                    <Link
                        href="/shop"
                        class="mt-6 inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-green-700"
                    >
                        Start Shopping
                    </Link>
                </div>

                <!-- Order List -->
                <div v-else class="space-y-5">
                    <article
                        v-for="order in orderData"
                        :key="order.id"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <!-- Order Header -->
                        <div
                            class="border-b border-slate-100 px-5 py-5 dark:border-slate-800 sm:px-6"
                        >
                            <div
                                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                            >
                                <div>
                                    <p
                                        class="text-xs font-medium uppercase tracking-wider text-slate-400"
                                    >
                                        Order Number
                                    </p>

                                    <h2
                                        class="mt-1 text-base font-extrabold text-slate-950 dark:text-white"
                                    >
                                        {{ order.order_number }}
                                    </h2>

                                    <p
                                        v-if="order.created_at"
                                        class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        {{ order.created_at }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <span
                                        class="rounded-full px-3 py-1.5 text-xs font-bold"
                                        :class="
                                            getPaymentStatusClass(order)
                                        "
                                    >
                                        {{
                                            getPaymentStatusLabel(order)
                                        }}
                                    </span>

                                    <span
                                        class="rounded-full px-3 py-1.5 text-xs font-bold"
                                        :class="
                                            getOrderStatusClass(
                                                order.status
                                            )
                                        "
                                    >
                                        {{
                                            getOrderStatusLabel(
                                                order.status
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Order Body -->
                        <div class="px-5 py-5 sm:px-6">
                            <div
                                class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-green-50 text-green-600 dark:bg-green-950/30"
                                    >
                                        <svg
                                            class="h-6 w-6"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.8"
                                                d="M6 3h12v18H6z"
                                            />

                                            <path
                                                stroke-linecap="round"
                                                stroke-width="1.8"
                                                d="M9 7h6M9 11h6M9 15h4"
                                            />
                                        </svg>
                                    </div>

                                    <div>
                                        <p
                                            class="text-sm font-semibold text-slate-950 dark:text-white"
                                        >
                                            {{
                                                order.items_count ??
                                                order.items?.length ??
                                                0
                                            }}
                                            item(s)
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            Total order value
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="flex items-center justify-between gap-5 sm:justify-end"
                                >
                                    <div class="text-left sm:text-right">
                                        <p
                                            class="text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            Total
                                        </p>

                                        <p
                                            class="mt-1 text-lg font-extrabold text-green-600"
                                        >
                                            {{
                                                formatMoney(
                                                    order.total
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <Link
                                        :href="`/orders/${order.id}`"
                                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-green-300 hover:text-green-600 dark:border-slate-700 dark:text-slate-200 dark:hover:border-green-700 dark:hover:text-green-400"
                                    >
                                        View Order
                                    </Link>
                                </div>
                            </div>

                            <!-- Payment Action -->
                            <div
                                v-if="
                                    ['pending', 'awaiting_payment'].includes(
                                        order.payment_status ??
                                            order.payment?.status
                                    )
                                "
                                class="mt-5 flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/20 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div>
                                    <p
                                        class="text-sm font-bold text-amber-800 dark:text-amber-300"
                                    >
                                        Payment required
                                    </p>

                                    <p
                                        class="mt-1 text-xs leading-5 text-amber-700 dark:text-amber-400"
                                    >
                                        This order has not been paid for yet.
                                        You can return here and complete
                                        payment when the payment gateway is
                                        available.
                                    </p>
                                </div>

                                <Link
                                    :href="`/orders/${order.id}/payment`"
                                    class="inline-flex shrink-0 items-center justify-center rounded-xl bg-green-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-green-700"
                                >
                                    Pay Now
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- Pagination -->
                <div
                    v-if="orders?.links && orders.links.length > 3"
                    class="mt-8 flex flex-wrap items-center justify-center gap-2"
                >
                    <template
                        v-for="link in orders.links"
                        :key="link.label"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded-lg border px-3 py-2 text-sm font-semibold transition"
                            :class="
                                link.active
                                    ? 'border-green-600 bg-green-600 text-white'
                                    : 'border-slate-200 bg-white text-slate-600 hover:border-green-300 hover:text-green-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'
                            "
                            v-html="link.label"
                        />

                        <span
                            v-else
                            class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-sm text-slate-400 dark:border-slate-800 dark:bg-slate-900"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </section>
        </main>
    </CustomerLayout>
</template>
