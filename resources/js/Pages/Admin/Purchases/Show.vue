<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    purchase: {
        type: Object,
        required: true,
    },
});

const formatPrice = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
    }).format(Number(amount || 0));
};

const formatDate = (date) => {
    if (!date) {
        return '—';
    }

    return new Date(date).toLocaleDateString('en-NG', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const statusLabel = (status) => {
    const labels = {
        draft: 'Draft',
        ordered: 'Ordered',
        received: 'Received',
        cancelled: 'Cancelled',
        active: 'Active',
        depleted: 'Depleted',
        expired: 'Expired',
    };

    return labels[status] ?? status ?? '—';
};

const statusClass = (status) => {
    const classes = {
        draft: 'bg-slate-100 text-slate-700',
        ordered: 'bg-blue-50 text-blue-700',
        received: 'bg-green-50 text-green-700',
        cancelled: 'bg-red-50 text-red-700',
        active: 'bg-green-50 text-green-700',
        depleted: 'bg-slate-100 text-slate-600',
        expired: 'bg-red-50 text-red-700',
    };

    return classes[status] ?? 'bg-slate-100 text-slate-700';
};

const totalItems = () => {
    return (props.purchase.items ?? []).reduce((total, item) => {
        return total + Number(item.quantity || 0);
    }, 0);
};

const totalRemaining = () => {
    return (props.purchase.items ?? []).reduce((total, item) => {
        return total + Number(
            item.remaining_quantity ?? item.quantity ?? 0
        );
    }, 0);
};

const isExpired = (date) => {
    if (!date) {
        return false;
    }

    const expiry = new Date(date);
    const today = new Date();

    expiry.setHours(0, 0, 0, 0);
    today.setHours(0, 0, 0, 0);

    return expiry < today;
};

const expiryClass = (item) => {
    if (!item.expiry_date) {
        return 'text-slate-600';
    }

    if (isExpired(item.expiry_date)) {
        return 'font-semibold text-red-600';
    }

    return 'text-slate-600';
};

const receivePurchase = () => {
    if (props.purchase.status !== 'ordered') {
        return;
    }

    if (
        !window.confirm(
            'Are you sure you want to receive this purchase? The purchased quantities will be added to inventory.'
        )
    ) {
        return;
    }

    router.post(
        route('admin.purchases.receive', props.purchase.id),
        {},
        {
            preserveScroll: true,
        }
    );
};
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            <!-- Header -->
            <div
                class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between"
            >
                <div class="flex items-start gap-4">
                    <Link
                        :href="route('admin.purchases.index')"
                        class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
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
                    </Link>

                    <div>
                        <p class="text-sm font-semibold text-green-600">
                            Procurement Management
                        </p>

                        <div
                            class="mt-1 flex flex-wrap items-center gap-3"
                        >
                            <h1
                                class="text-3xl font-bold tracking-tight text-slate-950"
                            >
                                {{ purchase.reference }}
                            </h1>

                            <span
                                class="rounded-full px-3 py-1 text-xs font-bold"
                                :class="statusClass(purchase.status)"
                            >
                                {{ statusLabel(purchase.status) }}
                            </span>
                        </div>

                        <p class="mt-2 text-sm text-slate-500">
                            Purchase order details and inventory information.
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap gap-3">
                    <Link
                        :href="route('admin.purchases.index')"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        All Purchases
                    </Link>

                    <Link
                        v-if="purchase.status === 'draft'"
                        :href="route('admin.purchases.edit', purchase.id)"
                        class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700"
                    >
                        Edit Purchase
                    </Link>

                    <button
                        v-if="purchase.status === 'ordered'"
                        type="button"
                        @click="receivePurchase"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700"
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
                                d="M5 12h14m-7-7 7 7-7 7"
                            />
                        </svg>

                        Receive Purchase
                    </button>
                </div>
            </div>

            <!-- Summary -->
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">

                <!-- Total Amount -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-sm font-medium text-slate-500">
                        Total Amount
                    </p>

                    <p class="mt-3 text-2xl font-bold text-slate-950">
                        {{ formatPrice(purchase.total_amount) }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Final purchase cost
                    </p>
                </div>

                <!-- Purchased Units -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-sm font-medium text-slate-500">
                        Purchased Units
                    </p>

                    <p class="mt-3 text-2xl font-bold text-slate-950">
                        {{ totalItems() }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Across {{ purchase.items?.length ?? 0 }} products
                    </p>
                </div>

                <!-- Remaining Units -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-sm font-medium text-slate-500">
                        Remaining Units
                    </p>

                    <p class="mt-3 text-2xl font-bold text-green-600">
                        {{ totalRemaining() }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Available from purchase batches
                    </p>
                </div>

                <!-- Purchase Date -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-sm font-medium text-slate-500">
                        Purchase Date
                    </p>

                    <p class="mt-3 text-lg font-bold text-slate-950">
                        {{ formatDate(purchase.purchase_date) }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Date recorded
                    </p>
                </div>
            </div>

            <!-- Supplier + Purchase Information -->
            <div class="mt-6 grid gap-6 lg:grid-cols-2">

                <!-- Supplier -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50 text-green-600"
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
                                    d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 10h.01M12 10h.01M15 10h.01"
                                />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-lg font-bold text-slate-900">
                                Supplier
                            </h2>

                            <p class="text-sm text-slate-500">
                                Supplier information
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">

                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                            >
                                Supplier Name
                            </p>

                            <p
                                class="mt-1 text-sm font-semibold text-slate-900"
                            >
                                {{ purchase.supplier?.name ?? '—' }}
                            </p>
                        </div>

                        <div v-if="purchase.supplier?.company_name">
                            <p
                                class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                            >
                                Company
                            </p>

                            <p class="mt-1 text-sm text-slate-700">
                                {{ purchase.supplier.company_name }}
                            </p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                                >
                                    Phone
                                </p>

                                <p class="mt-1 text-sm text-slate-700">
                                    {{ purchase.supplier?.phone ?? '—' }}
                                </p>
                            </div>

                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                                >
                                    Email
                                </p>

                                <p
                                    class="mt-1 break-all text-sm text-slate-700"
                                >
                                    {{ purchase.supplier?.email ?? '—' }}
                                </p>
                            </div>
                        </div>

                        <div v-if="purchase.supplier?.address">
                            <p
                                class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                            >
                                Address
                            </p>

                            <p class="mt-1 text-sm text-slate-700">
                                {{ purchase.supplier.address }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Purchase Information -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600"
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
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-lg font-bold text-slate-900">
                                Purchase Information
                            </h2>

                            <p class="text-sm text-slate-500">
                                Order and record details
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">

                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                            >
                                Reference
                            </p>

                            <p
                                class="mt-1 font-mono text-sm font-semibold text-slate-900"
                            >
                                {{ purchase.reference }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                            >
                                Purchase Date
                            </p>

                            <p class="mt-1 text-sm text-slate-700">
                                {{ formatDate(purchase.purchase_date) }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                            >
                                Created By
                            </p>

                            <p class="mt-1 text-sm text-slate-700">
                                {{ purchase.user?.name ?? 'System' }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                            >
                                Created
                            </p>

                            <p class="mt-1 text-sm text-slate-700">
                                {{ formatDate(purchase.created_at) }}
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Purchased Products -->
            <section
                class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex flex-col gap-2 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            Purchased Products
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Products, batches, expiry dates and remaining stock
                            from this purchase.
                        </p>
                    </div>

                    <span
                        class="rounded-lg bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-500"
                    >
                        {{ purchase.items?.length ?? 0 }} products
                    </span>
                </div>

                <!-- Desktop -->
                <div
                    v-if="purchase.items?.length"
                    class="hidden overflow-x-auto md:block"
                >
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Product
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Batch
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Expiry
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Purchased
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Remaining
                                </th>

                                <th
                                    class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Unit Cost
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Total
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="item in purchase.items"
                                :key="item.id"
                                class="transition hover:bg-slate-50"
                            >
                                <td class="px-6 py-5">
                                    <p
                                        class="text-sm font-semibold text-slate-900"
                                    >
                                        {{
                                            item.product?.name ??
                                            'Unknown Product'
                                        }}
                                    </p>

                                    <p
                                        v-if="item.product?.sku"
                                        class="mt-1 font-mono text-xs text-slate-400"
                                    >
                                        {{ item.product.sku }}
                                    </p>
                                </td>

                                <td
                                    class="whitespace-nowrap px-6 py-5 text-sm text-slate-600"
                                >
                                    {{ item.batch_number ?? '—' }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-6 py-5 text-sm"
                                    :class="expiryClass(item)"
                                >
                                    <span>
                                        {{ formatDate(item.expiry_date) }}
                                    </span>

                                    <span
                                        v-if="isExpired(item.expiry_date)"
                                        class="ml-2 rounded-full bg-red-50 px-2 py-1 text-xs font-bold text-red-600"
                                    >
                                        Expired
                                    </span>
                                </td>

                                <td
                                    class="whitespace-nowrap px-6 py-5 text-right text-sm font-semibold text-slate-900"
                                >
                                    {{ item.quantity }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-6 py-5 text-right text-sm font-bold"
                                    :class="
                                        Number(
                                            item.remaining_quantity ?? 0
                                        ) > 0
                                            ? 'text-green-600'
                                            : 'text-slate-400'
                                    "
                                >
                                    {{ item.remaining_quantity ?? 0 }}
                                </td>

                                <td class="px-6 py-5 text-center">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-bold"
                                        :class="statusClass(item.status)"
                                    >
                                        {{ statusLabel(item.status) }}
                                    </span>
                                </td>

                                <td
                                    class="whitespace-nowrap px-6 py-5 text-right text-sm text-slate-600"
                                >
                                    {{ formatPrice(item.unit_cost) }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-6 py-5 text-right text-sm font-bold text-slate-900"
                                >
                                    {{ formatPrice(item.total_cost) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile -->
                <div
                    v-if="purchase.items?.length"
                    class="divide-y divide-slate-100 md:hidden"
                >
                    <div
                        v-for="item in purchase.items"
                        :key="item.id"
                        class="p-5"
                    >
                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <div class="min-w-0">
                                <p
                                    class="font-semibold text-slate-900"
                                >
                                    {{
                                        item.product?.name ??
                                        'Unknown Product'
                                    }}
                                </p>

                                <p
                                    v-if="item.product?.sku"
                                    class="mt-1 font-mono text-xs text-slate-400"
                                >
                                    {{ item.product.sku }}
                                </p>
                            </div>

                            <span
                                class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold"
                                :class="statusClass(item.status)"
                            >
                                {{ statusLabel(item.status) }}
                            </span>
                        </div>

                        <div
                            class="mt-4 grid grid-cols-2 gap-4 rounded-xl bg-slate-50 p-4"
                        >
                            <div>
                                <p class="text-xs text-slate-400">
                                    Purchased
                                </p>

                                <p
                                    class="mt-1 text-sm font-semibold text-slate-900"
                                >
                                    {{ item.quantity }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Remaining
                                </p>

                                <p
                                    class="mt-1 text-sm font-bold"
                                    :class="
                                        Number(
                                            item.remaining_quantity ?? 0
                                        ) > 0
                                            ? 'text-green-600'
                                            : 'text-slate-400'
                                    "
                                >
                                    {{ item.remaining_quantity ?? 0 }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Unit Cost
                                </p>

                                <p
                                    class="mt-1 text-sm font-semibold text-slate-900"
                                >
                                    {{ formatPrice(item.unit_cost) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Total Cost
                                </p>

                                <p
                                    class="mt-1 text-sm font-bold text-slate-900"
                                >
                                    {{ formatPrice(item.total_cost) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Batch Number
                                </p>

                                <p class="mt-1 text-sm text-slate-700">
                                    {{ item.batch_number ?? '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Expiry Date
                                </p>

                                <p
                                    class="mt-1 text-sm"
                                    :class="expiryClass(item)"
                                >
                                    {{ formatDate(item.expiry_date) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="px-6 py-16 text-center"
                >
                    <p class="text-sm font-semibold text-slate-700">
                        No products in this purchase
                    </p>

                    <p class="mt-1 text-sm text-slate-400">
                        This purchase order does not contain any items.
                    </p>
                </div>
            </section>

            <!-- Notes + Totals -->
            <div class="mt-6 grid gap-6 lg:grid-cols-3">

                <!-- Notes -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2"
                >
                    <h2 class="text-lg font-bold text-slate-900">
                        Notes
                    </h2>

                    <div
                        v-if="purchase.notes"
                        class="mt-4 rounded-xl bg-slate-50 p-4"
                    >
                        <p
                            class="whitespace-pre-line text-sm leading-6 text-slate-600"
                        >
                            {{ purchase.notes }}
                        </p>
                    </div>

                    <div
                        v-else
                        class="mt-4 rounded-xl bg-slate-50 p-4"
                    >
                        <p class="text-sm text-slate-400">
                            No notes were added to this purchase.
                        </p>
                    </div>
                </section>

                <!-- Totals -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <h2 class="text-lg font-bold text-slate-900">
                        Purchase Summary
                    </h2>

                    <div class="mt-5 space-y-4">
                        <div
                            class="flex items-center justify-between gap-4"
                        >
                            <span class="text-sm text-slate-500">
                                Subtotal
                            </span>

                            <span
                                class="text-sm font-semibold text-slate-900"
                            >
                                {{ formatPrice(purchase.subtotal) }}
                            </span>
                        </div>

                        <div
                            class="flex items-center justify-between gap-4"
                        >
                            <span class="text-sm text-slate-500">
                                Discount
                            </span>

                            <span
                                class="text-sm font-semibold text-slate-900"
                            >
                                -{{ formatPrice(purchase.discount) }}
                            </span>
                        </div>

                        <div
                            class="border-t border-slate-200 pt-4"
                        >
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <span
                                    class="text-base font-bold text-slate-900"
                                >
                                    Total
                                </span>

                                <span
                                    class="text-xl font-bold text-green-600"
                                >
                                    {{
                                        formatPrice(
                                            purchase.total_amount
                                        )
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Bottom Actions -->
            <div
                class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
            >
                <Link
                    :href="route('admin.purchases.index')"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Back to Purchases
                </Link>

                <Link
                    v-if="purchase.status === 'draft'"
                    :href="route('admin.purchases.edit', purchase.id)"
                    class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700"
                >
                    Edit Purchase
                </Link>

                <button
                    v-if="purchase.status === 'ordered'"
                    type="button"
                    @click="receivePurchase"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700"
                >
                    Receive Purchase
                </button>
            </div>
        </div>
    </AdminLayout>
</template> 