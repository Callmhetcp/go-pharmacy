<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const order = computed(() => props.order);

const formatMoney = (value) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: order.value?.currency ?? 'NGN',
        minimumFractionDigits: 2,
    }).format(Number(value ?? 0));
};

const formatDate = (value) => {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat('en-NG', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(new Date(value));
};

/*
|--------------------------------------------------------------------------
| Items
|--------------------------------------------------------------------------
*/

const items = computed(() => {
    return order.value?.items ?? [];
});

/*
|--------------------------------------------------------------------------
| Payments
|--------------------------------------------------------------------------
*/

const payments = computed(() => {
    return order.value?.payments ?? [];
});

const latestPayment = computed(() => {
    return payments.value.length
        ? payments.value[payments.value.length - 1]
        : null;
});

const paymentStatus = computed(() => {
    if (!latestPayment.value) {
        return 'unpaid';
    }

    return latestPayment.value.status ?? 'pending';
});

const paymentLabel = computed(() => {
    const labels = {
        unpaid: 'Payment Required',
        pending: 'Payment Pending',
        processing: 'Payment Processing',
        successful: 'Paid',
        failed: 'Payment Failed',
        cancelled: 'Payment Cancelled',
        refunded: 'Refunded',
    };

    return labels[paymentStatus.value] ?? 'Payment Pending';
});

const paymentClass = computed(() => {
    const classes = {
        unpaid:
            'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',

        pending:
            'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',

        processing:
            'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400',

        successful:
            'bg-green-100 text-green-700 dark:bg-green-950/40 dark:text-green-400',

        failed:
            'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-400',

        cancelled:
            'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-400',

        refunded:
            'bg-purple-100 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400',
    };

    return (
        classes[paymentStatus.value] ??
        'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
    );
});

/*
|--------------------------------------------------------------------------
| Order Status
|--------------------------------------------------------------------------
*/

const orderStatusLabel = computed(() => {
    const labels = {
        pending: 'Pending',
        confirmed: 'Confirmed',
        processing: 'Processing',
        completed: 'Completed',
        cancelled: 'Cancelled',
    };

    return labels[order.value?.status] ?? 'Pending';
});

const orderStatusClass = computed(() => {
    const classes = {
        pending:
            'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',

        confirmed:
            'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400',

        processing:
            'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400',

        completed:
            'bg-green-100 text-green-700 dark:bg-green-950/40 dark:text-green-400',

        cancelled:
            'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-400',
    };

    return (
        classes[order.value?.status] ??
        'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
    );
});

/*
|--------------------------------------------------------------------------
| Payment Actions
|--------------------------------------------------------------------------
*/

const needsPayment = computed(() => {
    return ['unpaid', 'pending', 'failed', 'cancelled'].includes(
        paymentStatus.value
    );
});

const isPaid = computed(() => {
    return paymentStatus.value === 'successful';
});
</script>

<template>
    <CustomerLayout>
        <Head :title="`Order ${order.order_number}`" />

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
                    <Link
                        :href="route('orders.index')"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-green-600 transition hover:text-green-700"
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
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>

                        Back to My Orders
                    </Link>

                    <div
                        class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.2em] text-green-600"
                            >
                                Go Pharmacy
                            </p>

                            <h1
                                class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white"
                            >
                                Order {{ order.order_number }}
                            </h1>

                            <p
                                class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Placed {{ formatDate(order.created_at) }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span
                                class="rounded-full px-3 py-1.5 text-xs font-bold"
                                :class="orderStatusClass"
                            >
                                {{ orderStatusLabel }}
                            </span>

                            <span
                                class="rounded-full px-3 py-1.5 text-xs font-bold"
                                :class="paymentClass"
                            >
                                {{ paymentLabel }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =========================================================
                 CONTENT
            ========================================================== -->

            <main
                class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10"
            >
                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- =================================================
                         LEFT
                    ================================================== -->

                    <div class="space-y-6 lg:col-span-2">
                        <!-- Order Items -->

                        <section
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div
                                class="border-b border-slate-100 px-5 py-4 dark:border-slate-800"
                            >
                                <h2
                                    class="text-lg font-extrabold text-slate-950 dark:text-white"
                                >
                                    Order Items
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    {{ items.length }}
                                    {{ items.length === 1 ? 'item' : 'items' }}
                                </p>
                            </div>

                            <div
                                v-if="items.length"
                                class="divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <div
                                    v-for="item in items"
                                    :key="item.id"
                                    class="flex gap-4 p-5"
                                >
                                    <!-- Product Image -->

                                    <div
                                        class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800"
                                    >
                                        <img
                                            v-if="
                                                item.product?.image_url ||
                                                item.product?.image
                                            "
                                            :src="
                                                item.product?.image_url ??
                                                item.product?.image
                                            "
                                            :alt="
                                                item.product?.name ??
                                                'Product'
                                            "
                                            class="h-full w-full object-cover"
                                        />

                                        <svg
                                            v-else
                                            class="h-8 w-8 text-slate-300 dark:text-slate-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                            />
                                        </svg>
                                    </div>

                                    <!-- Details -->

                                    <div class="min-w-0 flex-1">
                                        <h3
                                            class="font-bold text-slate-900 dark:text-white"
                                        >
                                            {{
                                                item.product?.name ??
                                                item.product_name ??
                                                'Product'
                                            }}
                                        </h3>

                                        <p
                                            v-if="item.variant_name"
                                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{ item.variant_name }}
                                        </p>

                                        <p
                                            class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                                        >
                                            Qty: {{ item.quantity }}
                                        </p>
                                    </div>

                                    <!-- Price -->

                                    <div class="shrink-0 text-right">
                                        <p
                                            class="text-sm text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                formatMoney(
                                                    item.price ??
                                                        item.unit_price ??
                                                        0
                                                )
                                            }}
                                            × {{ item.quantity }}
                                        </p>

                                        <p
                                            class="mt-1 font-extrabold text-slate-950 dark:text-white"
                                        >
                                            {{
                                                formatMoney(
                                                    (item.price ??
                                                        item.unit_price ??
                                                        0) *
                                                        Number(
                                                            item.quantity ?? 0
                                                        )
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-else
                                class="px-5 py-12 text-center text-sm text-slate-500 dark:text-slate-400"
                            >
                                No order items were found.
                            </div>
                        </section>

                        <!-- Payment Information -->

                        <section
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div
                                class="border-b border-slate-100 px-5 py-4 dark:border-slate-800"
                            >
                                <h2
                                    class="text-lg font-extrabold text-slate-950 dark:text-white"
                                >
                                    Payment Information
                                </h2>
                            </div>

                            <div
                                v-if="latestPayment"
                                class="space-y-4 p-5"
                            >
                                <div
                                    class="flex items-center justify-between gap-4"
                                >
                                    <span
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Payment status
                                    </span>

                                    <span
                                        class="rounded-full px-3 py-1.5 text-xs font-bold"
                                        :class="paymentClass"
                                    >
                                        {{ paymentLabel }}
                                    </span>
                                </div>

                                <div
                                    v-if="latestPayment.payment_reference"
                                    class="flex items-center justify-between gap-4"
                                >
                                    <span
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Payment reference
                                    </span>

                                    <span
                                        class="font-mono text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            latestPayment.payment_reference
                                        }}
                                    </span>
                                </div>

                                <div
                                    v-if="latestPayment.transaction_reference"
                                    class="flex items-center justify-between gap-4"
                                >
                                    <span
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Transaction reference
                                    </span>

                                    <span
                                        class="font-mono text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            latestPayment.transaction_reference
                                        }}
                                    </span>
                                </div>

                                <div
                                    v-if="latestPayment.payment_method"
                                    class="flex items-center justify-between gap-4"
                                >
                                    <span
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Payment method
                                    </span>

                                    <span
                                        class="text-sm font-semibold capitalize text-slate-900 dark:text-white"
                                    >
                                        {{
                                            latestPayment.payment_method
                                        }}
                                    </span>
                                </div>

                                <div
                                    v-if="latestPayment.gateway"
                                    class="flex items-center justify-between gap-4"
                                >
                                    <span
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Gateway
                                    </span>

                                    <span
                                        class="text-sm font-semibold capitalize text-slate-900 dark:text-white"
                                    >
                                        {{ latestPayment.gateway }}
                                    </span>
                                </div>

                                <div
                                    v-if="latestPayment.paid_at"
                                    class="flex items-center justify-between gap-4"
                                >
                                    <span
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Paid on
                                    </span>

                                    <span
                                        class="text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ formatDate(latestPayment.paid_at) }}
                                    </span>
                                </div>

                                <div
                                    v-if="latestPayment.gateway_message"
                                    class="rounded-xl bg-slate-50 p-4 text-sm text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    {{ latestPayment.gateway_message }}
                                </div>
                            </div>

                            <div
                                v-else
                                class="p-5"
                            >
                                <div
                                    class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/20"
                                >
                                    <p
                                        class="text-sm font-bold text-amber-800 dark:text-amber-300"
                                    >
                                        Payment required
                                    </p>

                                    <p
                                        class="mt-1 text-xs leading-5 text-amber-700 dark:text-amber-400"
                                    >
                                        No completed payment has been recorded
                                        for this order yet.
                                    </p>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- =================================================
                         RIGHT SIDEBAR
                    ================================================== -->

                    <aside class="space-y-6">
                        <!-- Order Summary -->

                        <section
                            class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"
                        >
                            <h2
                                class="text-lg font-extrabold text-slate-950 dark:text-white"
                            >
                                Order Summary
                            </h2>

                            <div class="mt-5 space-y-3">
                                <div
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span
                                        class="text-slate-500 dark:text-slate-400"
                                    >
                                        Subtotal
                                    </span>

                                    <span
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ formatMoney(order.subtotal) }}
                                    </span>
                                </div>

                                <div
                                    v-if="order.delivery_fee"
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span
                                        class="text-slate-500 dark:text-slate-400"
                                    >
                                        Delivery
                                    </span>

                                    <span
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ formatMoney(order.delivery_fee) }}
                                    </span>
                                </div>

                                <div
                                    class="border-t border-slate-100 pt-3 dark:border-slate-800"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="font-bold text-slate-900 dark:text-white"
                                        >
                                            Total
                                        </span>

                                        <span
                                            class="text-xl font-extrabold text-green-600"
                                        >
                                            {{ formatMoney(order.total) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Payment Action -->

                        <section
                            v-if="needsPayment"
                            class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900/50 dark:bg-amber-950/20"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400"
                            >
                                $
                            </div>

                            <h2
                                class="mt-4 font-extrabold text-amber-900 dark:text-amber-300"
                            >
                                Payment Required
                            </h2>

                            <p
                                class="mt-2 text-sm leading-6 text-amber-800 dark:text-amber-400"
                            >
                                Your order has been saved, but payment has not
                                been completed.
                            </p>

                            <!--
                                Replace this route with your actual payment
                                route when the gateway is connected.
                            -->

                            <Link
                                v-if="route().has('payment.create')"
                                :href="
                                    route(
                                        'payment.create',
                                        order.id
                                    )
                                "
                                class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-green-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-green-700"
                            >
                                Continue to Payment
                            </Link>

                            <div
                                v-else
                                class="mt-5 rounded-xl bg-white/70 p-3 text-xs leading-5 text-amber-700 dark:bg-slate-900/50 dark:text-amber-400"
                            >
                                Payment gateway is currently unavailable.
                                Your order is safely saved and will remain
                                available in My Orders.
                            </div>
                        </section>

                        <!-- Paid -->

                        <section
                            v-else-if="isPaid"
                            class="rounded-2xl border border-green-200 bg-green-50 p-5 dark:border-green-900/50 dark:bg-green-950/20"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400"
                            >
                                ✓
                            </div>

                            <h2
                                class="mt-4 font-extrabold text-green-900 dark:text-green-300"
                            >
                                Payment Successful
                            </h2>

                            <p
                                class="mt-2 text-sm leading-6 text-green-800 dark:text-green-400"
                            >
                                Your payment has been successfully recorded
                                for this order.
                            </p>
                        </section>

                        <!-- Customer Information -->

                        <section
                            class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"
                        >
                            <h2
                                class="text-lg font-extrabold text-slate-950 dark:text-white"
                            >
                                Delivery Information
                            </h2>

                            <div class="mt-5 space-y-3 text-sm">
                                <div
                                    v-if="order.customer_name"
                                >
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                    >
                                        Name
                                    </p>

                                    <p
                                        class="mt-1 font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ order.customer_name }}
                                    </p>
                                </div>

                                <div
                                    v-if="order.customer_phone"
                                >
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                    >
                                        Phone
                                    </p>

                                    <p
                                        class="mt-1 font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ order.customer_phone }}
                                    </p>
                                </div>

                                <div
                                    v-if="order.customer_email"
                                >
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                    >
                                        Email
                                    </p>

                                    <p
                                        class="mt-1 break-all font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ order.customer_email }}
                                    </p>
                                </div>

                                <div
                                    v-if="order.delivery_address"
                                >
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                    >
                                        Delivery Address
                                    </p>

                                    <p
                                        class="mt-1 leading-6 text-slate-700 dark:text-slate-300"
                                    >
                                        {{ order.delivery_address }}
                                    </p>
                                </div>
                            </div>
                        </section>
                    </aside>
                </div>
            </main>
        </div>
    </CustomerLayout>
</template>
