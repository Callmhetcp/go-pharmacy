<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    expiredProducts: {
        type: Array,
        default: () => [],
    },

    expiringSoon: {
        type: Array,
        default: () => [],
    },

    expiringWithin30Days: {
        type: Array,
        default: () => [],
    },

    summary: {
        type: Object,
        default: () => ({
            expired: 0,
            expiring_7_days: 0,
            expiring_30_days: 0,
        }),
    },
});

const activeTab = ref('expired');

const products = computed(() => {
    switch (activeTab.value) {
        case '7-days':
            return props.expiringSoon;

        case '30-days':
            return props.expiringWithin30Days;

        default:
            return props.expiredProducts;
    }
});

const pageTitle = computed(() => {
    switch (activeTab.value) {
        case '7-days':
            return 'Expiring Within 7 Days';

        case '30-days':
            return 'Expiring Within 30 Days';

        default:
            return 'Expired Products';
    }
});

const formatDate = (date) => {
    if (!date) {
        return '—';
    }

    const parsed = new Date(`${date}T00:00:00`);

    if (Number.isNaN(parsed.getTime())) {
        return date;
    }

    return parsed.toLocaleDateString('en-NG', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.reports.index')"
                        class="text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                    >
                        Reports
                    </Link>

                    <span class="text-gray-400">/</span>

                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Expiry Reminder
                    </span>
                </div>

                <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                    Expiry Reminder
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Monitor expired products and products approaching their expiry dates.
                </p>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                <!-- Expired -->
                <button
                    type="button"
                    @click="activeTab = 'expired'"
                    class="rounded-2xl border p-5 text-left transition"
                    :class="
                        activeTab === 'expired'
                            ? 'border-red-300 bg-red-50 dark:border-red-900 dark:bg-red-950/30'
                            : 'border-gray-200 bg-white hover:border-red-200 dark:border-gray-800 dark:bg-gray-900'
                    "
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Expired
                        </span>

                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-900/40 dark:text-red-300">
                            Action required
                        </span>
                    </div>

                    <div class="mt-3 text-3xl font-bold text-red-600">
                        {{ summary.expired }}
                    </div>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Products already past their expiry date
                    </p>
                </button>

                <!-- 7 Days -->
                <button
                    type="button"
                    @click="activeTab = '7-days'"
                    class="rounded-2xl border p-5 text-left transition"
                    :class="
                        activeTab === '7-days'
                            ? 'border-amber-300 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/30'
                            : 'border-gray-200 bg-white hover:border-amber-200 dark:border-gray-800 dark:bg-gray-900'
                    "
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Next 7 Days
                        </span>

                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                            Urgent
                        </span>
                    </div>

                    <div class="mt-3 text-3xl font-bold text-amber-600">
                        {{ summary.expiring_7_days }}
                    </div>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Products expiring within 7 days
                    </p>
                </button>

                <!-- 30 Days -->
                <button
                    type="button"
                    @click="activeTab = '30-days'"
                    class="rounded-2xl border p-5 text-left transition"
                    :class="
                        activeTab === '30-days'
                            ? 'border-blue-300 bg-blue-50 dark:border-blue-900 dark:bg-blue-950/30'
                            : 'border-gray-200 bg-white hover:border-blue-200 dark:border-gray-800 dark:bg-gray-900'
                    "
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Next 30 Days
                        </span>

                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                            Monitor
                        </span>
                    </div>

                    <div class="mt-3 text-3xl font-bold text-blue-600">
                        {{ summary.expiring_30_days }}
                    </div>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Products expiring within 30 days
                    </p>
                </button>

            </div>

            <!-- Products -->
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

                <!-- Table Header -->
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="font-semibold text-gray-900 dark:text-white">
                                {{ pageTitle }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Exact inventory batches requiring attention.
                            </p>
                        </div>

                        <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ products.length }}
                        </span>
                    </div>
                </div>

                <!-- Empty -->
                <div
                    v-if="products.length === 0"
                    class="px-6 py-16 text-center"
                >
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                        ✓
                    </div>

                    <h3 class="mt-4 font-semibold text-gray-900 dark:text-white">
                        No products found
                    </h3>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        There are no products in this expiry category.
                    </p>
                </div>

                <!-- Desktop -->
                <div
                    v-else
                    class="hidden overflow-x-auto md:block"
                >
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-950">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Product
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Batch
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Quantity
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Expiry
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Supplier
                                </th>

                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            <tr
                                v-for="product in products"
                                :key="product.id"
                                class="transition hover:bg-gray-50 dark:hover:bg-gray-800/50"
                            >
                                <!-- Product -->
                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ product.product_name }}
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500">
                                        SKU: {{ product.sku || '—' }}
                                    </div>
                                </td>

                                <!-- Batch -->
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ product.batch_number || '—' }}
                                </td>

                                <!-- Quantity -->
                                <td class="px-5 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ product.quantity }}
                                </td>

                                <!-- Expiry -->
                                <td class="px-5 py-4">
                                    <div
                                        class="text-sm font-medium"
                                        :class="
                                            activeTab === 'expired'
                                                ? 'text-red-600'
                                                : 'text-amber-600'
                                        "
                                    >
                                        {{ formatDate(product.expiry_date) }}
                                    </div>

                                    <div
                                        v-if="activeTab === 'expired'"
                                        class="mt-1 text-xs text-red-500"
                                    >
                                        {{ product.days_expired }} day{{ product.days_expired === 1 ? '' : 's' }} expired
                                    </div>

                                    <div
                                        v-else
                                        class="mt-1 text-xs text-amber-600"
                                    >
                                        {{ product.days_remaining }} day{{ product.days_remaining === 1 ? '' : 's' }} remaining
                                    </div>
                                </td>

                                <!-- Supplier -->
                                <td class="px-5 py-4">
                                    <div class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ product.supplier_name || '—' }}
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ product.purchase_reference || '—' }}
                                    </div>
                                </td>

                                <!-- Action -->
                                <td class="px-5 py-4 text-right">
                                    <Link
                                        :href="route(
                                            'admin.products.edit',
                                            product.product_id
                                        )"
                                        class="inline-flex rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                    >
                                        View Product
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile -->
                <div class="space-y-3 p-4 md:hidden">
                    <div
                        v-for="product in products"
                        :key="product.id"
                        class="rounded-xl border border-gray-200 p-4 dark:border-gray-800"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-semibold text-gray-900 dark:text-white">
                                    {{ product.product_name }}
                                </h3>

                                <p class="mt-1 text-xs text-gray-500">
                                    SKU: {{ product.sku || '—' }}
                                </p>
                            </div>

                            <span
                                class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="
                                    activeTab === 'expired'
                                        ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                                        : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
                                "
                            >
                                {{ activeTab === 'expired' ? 'Expired' : 'Expiring' }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <div class="text-xs text-gray-500">
                                    Batch
                                </div>

                                <div class="mt-1 font-medium text-gray-900 dark:text-white">
                                    {{ product.batch_number || '—' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">
                                    Quantity
                                </div>

                                <div class="mt-1 font-medium text-gray-900 dark:text-white">
                                    {{ product.quantity }}
                                </div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">
                                    Expiry Date
                                </div>

                                <div
                                    class="mt-1 font-medium"
                                    :class="
                                        activeTab === 'expired'
                                            ? 'text-red-600'
                                            : 'text-amber-600'
                                    "
                                >
                                    {{ formatDate(product.expiry_date) }}
                                </div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">
                                    Supplier
                                </div>

                                <div class="mt-1 font-medium text-gray-900 dark:text-white">
                                    {{ product.supplier_name || '—' }}
                                </div>
                            </div>
                        </div>

                        <Link
                            :href="route(
                                'admin.products.edit',
                                product.product_id
                            )"
                            class="mt-4 block w-full rounded-lg bg-gray-100 px-4 py-2.5 text-center text-sm font-semibold text-gray-700 transition hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
                        >
                            View Exact Product
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>