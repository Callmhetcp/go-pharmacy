<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const settings = computed(() => page.props.settings ?? {});

const primaryColor = computed(
    () => settings.value.primary_color ?? '#16a34a'
);

const accentColor = computed(
    () =>
        settings.value.accent_color ??
        settings.value.primary_color ??
        '#16a34a'
);

const siteName = computed(
    () => settings.value.site_name ?? 'Go Pharmacy'
);

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
    }).format(Number(amount ?? 0));
};

const formatDate = (date) => {
    if (!date) return '—';

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
    if (!status) return 'Unknown';

    return String(status)
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
};

const readablePaymentMethod = (method) => {
    if (!method) return '—';

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
        classes[status] ??
        'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'
    );
};

/*
|--------------------------------------------------------------------------
| Order Type
|--------------------------------------------------------------------------
*/

const isPosOrder = computed(() => {
    return (
        String(props.order.order_number ?? '')
            .toUpperCase()
            .startsWith('GP-POS-') ||
        props.order.payments?.some(
            (payment) => payment.gateway === 'pos'
        )
    );
});

/*
|--------------------------------------------------------------------------
| Payment
|--------------------------------------------------------------------------
*/

const latestPayment = computed(() => {
    const payments = props.order.payments ?? [];

    if (!payments.length) {
        return null;
    }

    return [...payments].sort((a, b) => {
        return (
            new Date(b.created_at ?? 0).getTime() -
            new Date(a.created_at ?? 0).getTime()
        );
    })[0];
});

const paymentStatus = computed(() => {
    return (
        latestPayment.value?.status ??
        props.order.payment_status ??
        'unpaid'
    );
});

const paymentMethod = computed(() => {
    return (
        latestPayment.value?.payment_method ??
        props.order.payment_method ??
        null
    );
});

const cashierId = computed(() => {
    const response = latestPayment.value?.gateway_response;

    if (!response) {
        return null;
    }

    if (typeof response === 'string') {
        try {
            const parsed = JSON.parse(response);
            return parsed?.cashier_id ?? null;
        } catch {
            return null;
        }
    }

    return response?.cashier_id ?? null;
});

/*
|--------------------------------------------------------------------------
| Customer
|--------------------------------------------------------------------------
*/

const customerName = computed(
    () => props.order.customer_name ?? 'Walk-in Customer'
);

const customerInitial = computed(() =>
    customerName.value.charAt(0).toUpperCase()
);

const itemCount = computed(() => {
    return (props.order.items ?? []).reduce(
        (total, item) => total + Number(item.quantity ?? 0),
        0
    );
});
</script>

<template>
    <div
        class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white sm:px-6 lg:px-8"
        :style="{
            '--admin-primary': primaryColor,
            '--admin-accent': accentColor,
        }"
    >
        <!-- Header -->
        <div
            class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <div
                    class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400"
                >
                    <Link
                        href="/admin/orders"
                        class="transition hover:text-[var(--admin-primary)]"
                    >
                        Orders
                    </Link>

                    <span>/</span>

                    <span class="text-slate-700 dark:text-slate-300">
                        {{ order.order_number }}
                    </span>
                </div>

                <h1
                    class="text-2xl font-bold text-slate-950 dark:text-white"
                >
                    Order {{ order.order_number }}
                </h1>

                <p
                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                >
                    Placed {{ formatDate(order.created_at) }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Link
                    href="/admin/orders"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    Back to Orders
                </Link>

                <Link
                    :href="`/admin/orders/${order.id}/edit`"
                    class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
                    :style="{ backgroundColor: primaryColor }"
                >
                    Edit Order
                </Link>
            </div>
        </div>

        <!-- Status -->
        <div
            class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <div
                class="flex flex-wrap items-center gap-3"
            >
                <span
                    class="rounded-full border px-3 py-1.5 text-sm font-semibold"
                    :class="statusClass(order.status)"
                >
                    {{ readableStatus(order.status) }}
                </span>

                <span
                    class="rounded-full border px-3 py-1.5 text-sm font-semibold"
                    :class="statusClass(paymentStatus)"
                >
                    Payment:
                    {{ readableStatus(paymentStatus) }}
                </span>

                <span
                    v-if="isPosOrder"
                    class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                >
                    POS Sale
                </span>
            </div>
        </div>

        <!-- Main -->
        <div class="grid gap-6 xl:grid-cols-3">
            <!-- Left -->
            <div class="space-y-6 xl:col-span-2">
                <!-- Order Items -->
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="border-b border-slate-200 px-5 py-4 dark:border-slate-800"
                    >
                        <h2
                            class="font-bold text-slate-950 dark:text-white"
                        >
                            Order Items
                        </h2>

                        <p
                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                        >
                            {{ itemCount }} item(s)
                        </p>
                    </div>

                    <div
                        v-if="order.items?.length"
                        class="divide-y divide-slate-100 dark:divide-slate-800"
                    >
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex gap-4 p-5"
                        >
                            <!-- Image -->
                            <div
                                class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800"
                            >
                                <img
                                    v-if="item.product?.image"
                                    :src="`/storage/${item.product.image}`"
                                    :alt="
                                        item.product?.name ??
                                        item.product_name ??
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
                                        stroke-width="1.6"
                                        d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 0v9m8-4.5-8 4.5m0 0-8-4.5"
                                    />
                                </svg>
                            </div>

                            <!-- Details -->
                            <div class="min-w-0 flex-1">
                                <h3
                                    class="font-semibold text-slate-950 dark:text-white"
                                >
                                    {{
                                        item.product?.name ??
                                        item.product_name ??
                                        'Product'
                                    }}
                                </h3>

                                <p
                                    v-if="
                                        item.product?.sku ??
                                        item.product_sku
                                    "
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    SKU:
                                    {{
                                        item.product?.sku ??
                                        item.product_sku
                                    }}
                                </p>

                                <div
                                    class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    <span>
                                        Qty:
                                        <strong
                                            class="text-slate-700 dark:text-slate-200"
                                        >
                                            {{ item.quantity }}
                                        </strong>
                                    </span>

                                    <span>
                                        Unit:
                                        <strong
                                            class="text-slate-700 dark:text-slate-200"
                                        >
                                            {{
                                                formatMoney(
                                                    item.unit_price ??
                                                    item.price ??
                                                    0
                                                )
                                            }}
                                        </strong>
                                    </span>

                                    <span v-if="item.selling_unit">
                                        Unit:
                                        <strong
                                            class="text-slate-700 dark:text-slate-200"
                                        >
                                            {{ item.selling_unit }}
                                        </strong>
                                    </span>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="text-right">
                                <p
                                    class="text-sm font-bold text-slate-950 dark:text-white"
                                >
                                    {{
                                        formatMoney(
                                            item.subtotal ??
                                            item.total ??
                                            Number(
                                                item.unit_price ??
                                                item.price ??
                                                0
                                            ) *
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
                        class="p-10 text-center text-sm text-slate-500 dark:text-slate-400"
                    >
                        No items found for this order.
                    </div>
                </section>

                <!-- Order Summary -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="mb-5 font-bold text-slate-950 dark:text-white"
                    >
                        Order Summary
                    </h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">
                                Subtotal
                            </span>

                            <span
                                class="font-medium text-slate-800 dark:text-slate-200"
                            >
                                {{ formatMoney(order.subtotal) }}
                            </span>
                        </div>

                        <div
                            v-if="
                                !isPosOrder ||
                                Number(order.delivery_fee ?? 0) > 0
                            "
                            class="flex justify-between"
                        >
                            <span class="text-slate-500 dark:text-slate-400">
                                Delivery
                            </span>

                            <span
                                class="font-medium text-slate-800 dark:text-slate-200"
                            >
                                {{ formatMoney(order.delivery_fee) }}
                            </span>
                        </div>

                        <div
                            v-if="Number(order.discount ?? 0) > 0"
                            class="flex justify-between"
                        >
                            <span class="text-slate-500 dark:text-slate-400">
                                Discount
                            </span>

                            <span class="font-medium text-red-600">
                                -{{ formatMoney(order.discount) }}
                            </span>
                        </div>

                        <div
                            class="border-t border-slate-200 pt-4 dark:border-slate-800"
                        >
                            <div class="flex justify-between">
                                <span
                                    class="text-base font-bold text-slate-950 dark:text-white"
                                >
                                    Total
                                </span>

                                <span
                                    class="text-xl font-extrabold"
                                    :style="{ color: primaryColor }"
                                >
                                    {{ formatMoney(order.total) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Right -->
            <div class="space-y-6">
                <!-- Customer -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h2
                            class="font-bold text-slate-950 dark:text-white"
                        >
                            Customer
                        </h2>

                        <span
                            v-if="isPosOrder"
                            class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                        >
                            Walk-in
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full font-bold text-white"
                            :style="{ backgroundColor: accentColor }"
                        >
                            {{ customerInitial }}
                        </div>

                        <div class="min-w-0">
                            <p
                                class="truncate font-semibold text-slate-950 dark:text-white"
                            >
                                {{ customerName }}
                            </p>

                            <p
                                v-if="order.customer_email"
                                class="truncate text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{ order.customer_email }}
                            </p>

                            <p
                                v-else
                                class="text-sm text-slate-400"
                            >
                                No email provided
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-5 border-t border-slate-100 pt-5 dark:border-slate-800"
                    >
                        <p class="text-xs text-slate-400">
                            Phone
                        </p>

                        <p
                            class="mt-1 font-medium text-slate-700 dark:text-slate-200"
                        >
                            {{ order.customer_phone ?? '—' }}
                        </p>
                    </div>
                </section>

                <!-- Delivery -->
                <section
                    v-if="!isPosOrder"
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="mb-4 font-bold text-slate-950 dark:text-white"
                    >
                        Delivery Information
                    </h2>

                    <div class="space-y-4 text-sm">
                        <div>
                            <p class="text-xs text-slate-400">
                                Delivery Address
                            </p>

                            <p
                                class="mt-1 font-medium leading-6 text-slate-700 dark:text-slate-200"
                            >
                                {{ order.delivery_address ?? '—' }}
                            </p>
                        </div>

                        <div v-if="order.delivery_city">
                            <p class="text-xs text-slate-400">
                                City
                            </p>

                            <p
                                class="mt-1 font-medium text-slate-700 dark:text-slate-200"
                            >
                                {{ order.delivery_city }}
                            </p>
                        </div>

                        <div v-if="order.delivery_state">
                            <p class="text-xs text-slate-400">
                                State
                            </p>

                            <p
                                class="mt-1 font-medium text-slate-700 dark:text-slate-200"
                            >
                                {{ order.delivery_state }}
                            </p>
                        </div>

                        <div v-if="order.delivery_notes">
                            <p class="text-xs text-slate-400">
                                Delivery Notes
                            </p>

                            <p
                                class="mt-1 leading-6 text-slate-700 dark:text-slate-200"
                            >
                                {{ order.delivery_notes }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- POS -->
                <section
                    v-if="isPosOrder"
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="mb-4 font-bold text-slate-950 dark:text-white"
                    >
                        POS Transaction
                    </h2>

                    <div class="space-y-4 text-sm">
                        <div>
                            <p class="text-xs text-slate-400">
                                Cashier ID
                            </p>

                            <p
                                class="mt-1 font-medium text-slate-700 dark:text-slate-200"
                            >
                                {{ cashierId ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                Payment Method
                            </p>

                            <p
                                class="mt-1 font-semibold text-slate-800 dark:text-slate-200"
                            >
                                {{ readablePaymentMethod(paymentMethod) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                Transaction Type
                            </p>

                            <p
                                class="mt-1 font-medium text-slate-700 dark:text-slate-200"
                            >
                                Walk-in POS Sale
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Payment -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h2
                            class="font-bold text-slate-950 dark:text-white"
                        >
                            Payment
                        </h2>

                        <span
                            class="rounded-full border px-2.5 py-1 text-xs font-semibold"
                            :class="statusClass(paymentStatus)"
                        >
                            {{ readableStatus(paymentStatus) }}
                        </span>
                    </div>

                    <div
                        v-if="latestPayment"
                        class="space-y-4 text-sm"
                    >
                        <div>
                            <p class="text-xs text-slate-400">
                                Payment Reference
                            </p>

                            <p
                                class="mt-1 break-all font-mono font-semibold text-slate-800 dark:text-slate-200"
                            >
                                {{
                                    latestPayment.payment_reference ??
                                    latestPayment.transaction_reference ??
                                    '—'
                                }}
                            </p>
                        </div>

                        <div
                            v-if="latestPayment.transaction_reference"
                        >
                            <p class="text-xs text-slate-400">
                                Transaction Reference
                            </p>

                            <p
                                class="mt-1 break-all font-mono text-slate-700 dark:text-slate-300"
                            >
                                {{ latestPayment.transaction_reference }}
                            </p>
                        </div>

                        <div v-if="latestPayment.gateway">
                            <p class="text-xs text-slate-400">
                                Payment Gateway
                            </p>

                            <p
                                class="mt-1 font-medium text-slate-700 dark:text-slate-200"
                            >
                                {{ readableStatus(latestPayment.gateway) }}
                            </p>
                        </div>

                        <div v-if="paymentMethod">
                            <p class="text-xs text-slate-400">
                                Payment Method
                            </p>

                            <p
                                class="mt-1 font-medium text-slate-700 dark:text-slate-200"
                            >
                                {{ readablePaymentMethod(paymentMethod) }}
                            </p>
                        </div>

                        <div
                            v-if="latestPayment.amount !== null"
                        >
                            <p class="text-xs text-slate-400">
                                Amount Paid
                            </p>

                            <p
                                class="mt-1 text-lg font-extrabold"
                                :style="{ color: primaryColor }"
                            >
                                {{ formatMoney(latestPayment.amount) }}
                            </p>
                        </div>

                        <div v-if="latestPayment.paid_at">
                            <p class="text-xs text-slate-400">
                                Paid At
                            </p>

                            <p
                                class="mt-1 font-medium text-slate-700 dark:text-slate-200"
                            >
                                {{ formatDate(latestPayment.paid_at) }}
                            </p>
                        </div>

                        <div v-if="latestPayment.verified_at">
                            <p class="text-xs text-slate-400">
                                Verified At
                            </p>

                            <p
                                class="mt-1 font-medium text-slate-700 dark:text-slate-200"
                            >
                                {{ formatDate(latestPayment.verified_at) }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-else
                        class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5 text-center dark:border-slate-700 dark:bg-slate-800"
                    >
                        <p
                            class="text-sm font-semibold text-slate-600 dark:text-slate-300"
                        >
                            No payment recorded
                        </p>

                        <p
                            class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                        >
                            No payment transaction has been associated
                            with this order yet.
                        </p>
                    </div>
                </section>

                <!-- Order Information -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="mb-4 font-bold text-slate-950 dark:text-white"
                    >
                        Order Information
                    </h2>

                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500 dark:text-slate-400">
                                Order Number
                            </span>

                            <span
                                class="font-mono font-semibold text-slate-800 dark:text-slate-200"
                            >
                                {{ order.order_number }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500 dark:text-slate-400">
                                Order Type
                            </span>

                            <span
                                class="font-medium text-slate-700 dark:text-slate-200"
                            >
                                {{ isPosOrder ? 'POS Sale' : 'Online Order' }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500 dark:text-slate-400">
                                Created
                            </span>

                            <span
                                class="text-right font-medium text-slate-700 dark:text-slate-200"
                            >
                                {{ formatDate(order.created_at) }}
                            </span>
                        </div>

                        <div
                            v-if="order.updated_at"
                            class="flex justify-between gap-4"
                        >
                            <span class="text-slate-500 dark:text-slate-400">
                                Last Updated
                            </span>

                            <span
                                class="text-right font-medium text-slate-700 dark:text-slate-200"
                            >
                                {{ formatDate(order.updated_at) }}
                            </span>
                        </div>

                        <div
                            v-if="order.notes"
                            class="border-t border-slate-100 pt-4 dark:border-slate-800"
                        >
                            <p class="text-xs text-slate-400">
                                Order Notes
                            </p>

                            <p
                                class="mt-2 whitespace-pre-line leading-6 text-slate-700 dark:text-slate-300"
                            >
                                {{ order.notes }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Branding -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <p
                        class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                    >
                        {{ siteName }}
                    </p>

                    <p
                        class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                    >
                        Order Management
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
