<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    inventory: {
        type: Object,
        required: true,
    },

    transactions: {
        type: Object,
        required: true,
    },
});

const formatDate = (date) => {
    if (!date) return '—';

    return new Date(date).toLocaleString('en-NG', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
};

const transactionLabel = (type) => {
    const labels = {
        purchase: 'Purchase',
        sale: 'Sale',
        online_sale: 'Online Sale',
        return: 'Return',
        adjustment: 'Adjustment',
        damaged: 'Damaged',
        expired: 'Expired',
        correction: 'Correction',
    };

    return labels[type] ?? type;
};

const transactionClass = (quantity) => {
    return Number(quantity) >= 0
        ? 'text-green-600'
        : 'text-red-600';
};
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                <div class="flex items-start gap-4">
                    <Link
                        href="/admin/inventory"
                        class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-900"
                    >
                        ←
                    </Link>

                    <div>
                        <p class="text-sm font-semibold text-green-600">
                            Inventory
                        </p>

                        <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">
                            {{ inventory.product?.name ?? 'Inventory' }}
                        </h1>

                        <p class="mt-2 text-sm text-slate-500">
                            Stock details and transaction history.
                        </p>
                    </div>
                </div>

                <Link
                    href="/admin/inventory/create"
                    class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700"
                >
                    + Adjust Stock
                </Link>
            </div>

            <!-- Stock Summary -->
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <p class="text-sm font-medium text-slate-500">
                        Current Stock
                    </p>

                    <p class="mt-3 text-3xl font-bold text-slate-950">
                        {{ inventory.quantity }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Total physical units
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <p class="text-sm font-medium text-slate-500">
                        Reserved
                    </p>

                    <p class="mt-3 text-3xl font-bold text-slate-950">
                        {{ inventory.reserved_quantity }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Units reserved for orders
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <p class="text-sm font-medium text-slate-500">
                        Available
                    </p>

                    <p class="mt-3 text-3xl font-bold text-green-600">
                        {{
                            Math.max(
                                0,
                                Number(inventory.quantity) -
                                Number(inventory.reserved_quantity),
                            )
                        }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Available for sale
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <p class="text-sm font-medium text-slate-500">
                        Minimum Stock
                    </p>

                    <p class="mt-3 text-3xl font-bold text-slate-950">
                        {{ inventory.minimum_stock }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Low-stock threshold
                    </p>
                </div>
            </div>

            <!-- Product Information -->
            <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="text-lg font-bold text-slate-900">
                    Product Information
                </h2>

                <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                            Product
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ inventory.product?.name ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                            Category
                        </p>

                        <p class="mt-1 text-sm text-slate-700">
                            {{ inventory.product?.category?.name ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                            SKU
                        </p>

                        <p class="mt-1 text-sm text-slate-700">
                            {{ inventory.product?.sku ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                            Barcode
                        </p>

                        <p class="mt-1 text-sm text-slate-700">
                            {{ inventory.product?.barcode ?? '—' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Transactions -->
            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">

                <div class="border-b border-slate-200 px-6 py-5">
                    <h2 class="text-lg font-bold text-slate-900">
                        Transaction History
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Every stock movement recorded for this product.
                    </p>
                </div>

                <div
                    v-if="transactions.data?.length"
                    class="overflow-x-auto"
                >
                    <table class="min-w-full divide-y divide-slate-200">

                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Date
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Type
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Quantity
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Before
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    After
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Reference
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    User
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            <tr
                                v-for="transaction in transactions.data"
                                :key="transaction.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-600">
                                    {{ formatDate(transaction.created_at) }}
                                </td>

                                <td class="px-6 py-5">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                        {{ transactionLabel(transaction.type) }}
                                    </span>
                                </td>

                                <td
                                    class="px-6 py-5 text-sm font-bold"
                                    :class="transactionClass(transaction.quantity)"
                                >
                                    {{
                                        Number(transaction.quantity) > 0
                                            ? `+${transaction.quantity}`
                                            : transaction.quantity
                                    }}
                                </td>

                                <td class="px-6 py-5 text-sm text-slate-600">
                                    {{ transaction.quantity_before }}
                                </td>

                                <td class="px-6 py-5 text-sm font-semibold text-slate-900">
                                    {{ transaction.quantity_after }}
                                </td>

                                <td class="px-6 py-5 text-sm text-slate-600">
                                    {{ transaction.reference ?? '—' }}
                                </td>

                                <td class="px-6 py-5 text-sm text-slate-600">
                                    {{ transaction.user?.name ?? 'System' }}
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                <!-- Empty -->
                <div
                    v-else
                    class="px-6 py-16 text-center"
                >
                    <p class="text-sm font-semibold text-slate-700">
                        No transactions yet
                    </p>

                    <p class="mt-1 text-sm text-slate-400">
                        Stock movements will appear here.
                    </p>
                </div>

                <!-- Pagination -->
                <div
                    v-if="transactions.links && transactions.links.length > 3"
                    class="flex flex-wrap items-center justify-center gap-2 border-t border-slate-200 px-6 py-5"
                >
                    <Link
                        v-for="link in transactions.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        class="rounded-lg px-3 py-2 text-sm"
                        :class="
                            link.active
                                ? 'bg-green-600 font-semibold text-white'
                                : link.url
                                    ? 'text-slate-600 hover:bg-slate-100'
                                    : 'cursor-not-allowed text-slate-300'
                        "
                    />
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
