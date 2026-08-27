<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    inventory: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({
            search: '',
        }),
    },
});

const search = ref(props.filters?.search ?? '');

const searchProducts = () => {
    router.get(
        '/admin/inventory',
        {
            search: search.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const clearSearch = () => {
    search.value = '';

    router.get(
        '/admin/inventory',
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
    }).format(Number(price || 0));
};

const getAvailable = (item) => {
    const quantity = Number(item.quantity || 0);
    const reserved = Number(item.reserved_quantity || 0);

    return Math.max(quantity - reserved, 0);
};

const getStatus = (item) => {
    const quantity = Number(item.quantity || 0);
    const minimum = Number(item.minimum_stock || 0);

    if (quantity <= 0) {
        return {
            label: 'Out of Stock',
            classes: 'bg-red-50 text-red-700',
            dot: 'bg-red-500',
        };
    }

    if (quantity <= minimum) {
        return {
            label: 'Low Stock',
            classes: 'bg-amber-50 text-amber-700',
            dot: 'bg-amber-500',
        };
    }

    return {
        label: 'In Stock',
        classes: 'bg-green-50 text-green-700',
        dot: 'bg-green-500',
    };
};

const showAdjustment = ref(false);
const selectedInventory = ref(null);

const form = useForm({
    product_id: '',
    type: 'purchase',
    quantity: '',
    reference: '',
    notes: '',
    minimum_stock: '',
});

const openAdjustment = (item) => {
    selectedInventory.value = item;

    form.reset();

    form.product_id = item.product_id;
    form.minimum_stock = item.minimum_stock ?? 0;

    showAdjustment.value = true;
};

const closeAdjustment = () => {
    if (form.processing) {
        return;
    }

    showAdjustment.value = false;
    selectedInventory.value = null;
    form.reset();
};

const submitAdjustment = () => {
    form.post('/admin/inventory', {
        preserveScroll: true,

        onSuccess: () => {
            closeAdjustment();
        },
    });
};

const adjustmentTypes = computed(() => [
    {
        value: 'purchase',
        label: 'Stock Received',
        description: 'Add stock received from a supplier.',
        positive: true,
    },
    {
        value: 'return',
        label: 'Customer Return',
        description: 'Add stock returned by a customer.',
        positive: true,
    },
    {
        value: 'adjustment',
        label: 'Stock Adjustment',
        description: 'Manually increase or decrease stock.',
        positive: false,
    },
    {
        value: 'damaged',
        label: 'Damaged Stock',
        description: 'Remove damaged stock from inventory.',
        positive: false,
    },
    {
        value: 'expired',
        label: 'Expired Stock',
        description: 'Remove expired stock from inventory.',
        positive: false,
    },
    {
        value: 'correction',
        label: 'Correction',
        description: 'Correct an inventory quantity.',
        positive: false,
    },
]);
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Header -->
            <div
                class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <Link
                        href="/admin"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-green-600"
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

                        Dashboard
                    </Link>

                    <p
                        class="mt-5 text-sm font-semibold text-green-600"
                    >
                        Stock Management
                    </p>

                    <h1
                        class="mt-1 text-3xl font-bold tracking-tight text-slate-950"
                    >
                        Inventory
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm text-slate-500">
                        Monitor stock levels, identify low-stock products and
                        manage inventory movements.
                    </p>
                </div>

                <Link
                    href="/admin/inventory/create"
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
                            d="M12 5v14m-7-7h14"
                        />
                    </svg>

                    Adjust Stock
                </Link>
            </div>

            <!-- Summary -->
            <div
                class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
            >
                <!-- Total Products -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-600"
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
                                    d="M20 7.5 12 3 4 7.5v9L12 21l8-4.5v-9ZM12 12l8-4.5M12 12 4 7.5M12 12v9"
                                />
                            </svg>
                        </div>

                        <span
                            class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                        >
                            Catalogue
                        </span>
                    </div>

                    <p class="mt-5 text-2xl font-bold text-slate-950">
                        {{ inventory.total ?? 0 }}
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Products tracked
                    </p>
                </div>

                <!-- In Stock -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-green-600"
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
                                    d="m5 12 4 4L19 6"
                                />
                            </svg>
                        </div>

                        <span
                            class="text-xs font-semibold uppercase tracking-wide text-green-600"
                        >
                            Healthy
                        </span>
                    </div>

                    <p class="mt-5 text-2xl font-bold text-slate-950">
                        {{
                            inventory.data.filter(
                                (item) =>
                                    Number(item.quantity || 0) >
                                    Number(item.minimum_stock || 0),
                            ).length
                        }}
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Products adequately stocked
                    </p>
                </div>

                <!-- Low Stock -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600"
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
                                    d="M12 9v4m0 4h.01M10.3 3.7 2.7 17a2 2 0 0 0 1.74 3h15.12a2 2 0 0 0 1.74-3L13.7 3.7a2 2 0 0 0-3.4 0Z"
                                />
                            </svg>
                        </div>

                        <span
                            class="text-xs font-semibold uppercase tracking-wide text-amber-600"
                        >
                            Attention
                        </span>
                    </div>

                    <p class="mt-5 text-2xl font-bold text-slate-950">
                        {{
                            inventory.data.filter((item) => {
                                const quantity = Number(
                                    item.quantity || 0,
                                );
                                const minimum = Number(
                                    item.minimum_stock || 0,
                                );

                                return quantity > 0 && quantity <= minimum;
                            }).length
                        }}
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Products below minimum
                    </p>
                </div>

                <!-- Out of Stock -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-red-600"
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
                                    d="M6 6l12 12M18 6 6 18"
                                />
                            </svg>
                        </div>

                        <span
                            class="text-xs font-semibold uppercase tracking-wide text-red-600"
                        >
                            Critical
                        </span>
                    </div>

                    <p class="mt-5 text-2xl font-bold text-slate-950">
                        {{
                            inventory.data.filter(
                                (item) =>
                                    Number(item.quantity || 0) <= 0,
                            ).length
                        }}
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Products out of stock
                    </p>
                </div>
            </div>

            <!-- Inventory Card -->
            <div
                class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <!-- Card Header -->
                <div
                    class="border-b border-slate-200 px-5 py-5 sm:px-6"
                >
                    <div
                        class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                    >
                        <div>
                            <h2
                                class="text-lg font-bold text-slate-900"
                            >
                                Stock Overview
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Monitor available stock across your pharmacy.
                            </p>
                        </div>

                        <!-- Search -->
                        <form
                            @submit.prevent="searchProducts"
                            class="flex w-full gap-2 sm:w-auto"
                        >
                            <div class="relative flex-1 sm:w-80">
                                <svg
                                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
                                    />
                                </svg>

                                <input
                                    v-model="search"
                                    type="search"
                                    placeholder="Search product, SKU or barcode..."
                                    class="w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                />
                            </div>

                            <button
                                type="submit"
                                class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                            >
                                Search
                            </button>

                            <button
                                v-if="search"
                                type="button"
                                @click="clearSearch"
                                class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                            >
                                Clear
                            </button>
                        </form>
                    </div>

                    <div
                        class="mt-4 flex items-center justify-between text-xs text-slate-500"
                    >
                        <span>
                            {{ inventory.total ?? 0 }}
                            inventory records
                        </span>

                        <span>
                            Showing
                            {{ inventory.from ?? 0 }}
                            -
                            {{ inventory.to ?? 0 }}
                        </span>
                    </div>
                </div>

                <!-- Empty -->
                <div
                    v-if="inventory.data.length === 0"
                    class="px-6 py-20 text-center"
                >
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-green-50 text-green-600"
                    >
                        <svg
                            class="h-7 w-7"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.7"
                                d="M20 7.5 12 3 4 7.5v9L12 21l-8-4.5v-9L12 3Zm0 0v9m8-4.5-8 4.5m0 0-8-4.5"
                            />
                        </svg>
                    </div>

                    <h3
                        class="mt-5 text-lg font-bold text-slate-900"
                    >
                        No inventory records found
                    </h3>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm text-slate-500"
                    >
                        {{
                            search
                                ? 'Try changing your search term.'
                                : 'Inventory records will appear here when products are available.'
                        }}
                    </p>

                    <button
                        v-if="search"
                        type="button"
                        @click="clearSearch"
                        class="mt-6 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800"
                    >
                        Clear Search
                    </button>
                </div>

                <!-- Desktop -->
                <div
                    v-else
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
                                    Stock
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Available
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Minimum
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-100 bg-white"
                        >
                            <tr
                                v-for="item in inventory.data"
                                :key="item.id"
                                class="transition hover:bg-slate-50"
                            >
                                <!-- Product -->
                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center gap-4"
                                    >
                                        <div
                                            class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100"
                                        >
                                            <img
                                                v-if="item.product?.image"
                                                :src="`/storage/${item.product.image}`"
                                                :alt="item.product?.name"
                                                class="h-full w-full object-cover"
                                            />

                                            <svg
                                                v-else
                                                class="h-6 w-6 text-slate-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.7"
                                                    d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 0v9m8-4.5-8 4.5m0 0-8-4.5"
                                                />
                                            </svg>
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="max-w-xs truncate text-sm font-semibold text-slate-900"
                                            >
                                                {{
                                                    item.product?.name ??
                                                    'Unknown Product'
                                                }}
                                            </p>

                                            <p
                                                class="mt-1 text-xs text-slate-500"
                                            >
                                                {{
                                                    item.product?.category
                                                        ?.name ??
                                                    'Uncategorised'
                                                }}
                                            </p>

                                            <p
                                                v-if="item.product?.sku"
                                                class="mt-1 font-mono text-[11px] text-slate-400"
                                            >
                                                {{ item.product.sku }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Stock -->
                                <td class="px-6 py-4">
                                    <div>
                                        <p
                                            class="text-sm font-bold text-slate-900"
                                        >
                                            {{ item.quantity ?? 0 }}
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-slate-500"
                                        >
                                            {{
                                                item.reserved_quantity ?? 0
                                            }}
                                            reserved
                                        </p>
                                    </div>
                                </td>

                                <!-- Available -->
                                <td class="px-6 py-4">
                                    <span
                                        class="text-sm font-bold text-slate-900"
                                    >
                                        {{ getAvailable(item) }}
                                    </span>
                                </td>

                                <!-- Minimum -->
                                <td class="px-6 py-4">
                                    <span
                                        class="text-sm text-slate-600"
                                    >
                                        {{ item.minimum_stock ?? 0 }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="getStatus(item).classes"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full"
                                            :class="getStatus(item).dot"
                                        ></span>

                                        {{ getStatus(item).label }}
                                    </span>
                                </td>

                                <!-- Action -->
                                <td
                                    class="px-6 py-4 text-right"
                                >
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <Link
                                            :href="`/admin/inventory/${item.id}`"
                                            class="rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                                        >
                                            View
                                        </Link>

                                        <button
                                            type="button"
                                            @click="openAdjustment(item)"
                                            class="rounded-lg bg-green-50 px-3 py-2 text-xs font-semibold text-green-700 transition hover:bg-green-100"
                                        >
                                            Adjust
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile -->
                <div
                    v-if="inventory.data.length > 0"
                    class="divide-y divide-slate-100 md:hidden"
                >
                    <div
                        v-for="item in inventory.data"
                        :key="item.id"
                        class="p-5"
                    >
                        <div class="flex gap-4">
                            <div
                                class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100"
                            >
                                <img
                                    v-if="item.product?.image"
                                    :src="`/storage/${item.product.image}`"
                                    :alt="item.product?.name"
                                    class="h-full w-full object-cover"
                                />

                                <svg
                                    v-else
                                    class="h-7 w-7 text-slate-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.7"
                                        d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 0v9m8-4.5-8 4.5m0 0-8-4.5"
                                    />
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div class="min-w-0">
                                        <h3
                                            class="truncate font-semibold text-slate-900"
                                        >
                                            {{
                                                item.product?.name ??
                                                'Unknown Product'
                                            }}
                                        </h3>

                                        <p
                                            class="mt-1 text-xs text-slate-500"
                                        >
                                            {{
                                                item.product?.category
                                                    ?.name ??
                                                'Uncategorised'
                                            }}
                                        </p>
                                    </div>

                                    <span
                                        class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold"
                                        :class="getStatus(item).classes"
                                    >
                                        {{ getStatus(item).label }}
                                    </span>
                                </div>

                                <div
                                    class="mt-4 grid grid-cols-3 gap-3"
                                >
                                    <div>
                                        <p
                                            class="text-[10px] font-semibold uppercase tracking-wide text-slate-400"
                                        >
                                            Stock
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-bold text-slate-900"
                                        >
                                            {{ item.quantity ?? 0 }}
                                        </p>
                                    </div>

                                    <div>
                                        <p
                                            class="text-[10px] font-semibold uppercase tracking-wide text-slate-400"
                                        >
                                            Available
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-bold text-slate-900"
                                        >
                                            {{ getAvailable(item) }}
                                        </p>
                                    </div>

                                    <div>
                                        <p
                                            class="text-[10px] font-semibold uppercase tracking-wide text-slate-400"
                                        >
                                            Minimum
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-bold text-slate-900"
                                        >
                                            {{ item.minimum_stock ?? 0 }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="mt-4 flex gap-2"
                                >
                                    <Link
                                        :href="`/admin/inventory/${item.id}`"
                                        class="flex-1 rounded-lg bg-slate-100 px-3 py-2 text-center text-xs font-semibold text-slate-700 transition hover:bg-slate-200"
                                    >
                                        View Details
                                    </Link>

                                    <button
                                        type="button"
                                        @click="openAdjustment(item)"
                                        class="flex-1 rounded-lg bg-green-50 px-3 py-2 text-xs font-semibold text-green-700 transition hover:bg-green-100"
                                    >
                                        Adjust Stock
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="
                        inventory.links &&
                        inventory.links.length > 3
                    "
                    class="flex flex-wrap items-center justify-center gap-2 border-t border-slate-200 px-6 py-5"
                >
                    <template
                        v-for="(link, index) in inventory.links"
                        :key="index"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            class="rounded-lg px-3 py-2 text-sm font-medium transition"
                            :class="
                                link.active
                                    ? 'bg-green-600 text-white'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                            "
                            v-html="link.label"
                        />

                        <span
                            v-else
                            class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-300"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Adjustment Modal -->
        <div
            v-if="showAdjustment"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4 backdrop-blur-sm"
            @click.self="closeAdjustment"
        >
            <div
                class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl"
            >
                <!-- Modal Header -->
                <div
                    class="flex items-start justify-between border-b border-slate-200 px-6 py-5"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-green-600"
                        >
                            Inventory
                        </p>

                        <h2
                            class="mt-1 text-lg font-bold text-slate-900"
                        >
                            Adjust Stock
                        </h2>

                        <p
                            v-if="selectedInventory"
                            class="mt-1 text-sm text-slate-500"
                        >
                            {{ selectedInventory.product?.name }}
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeAdjustment"
                        class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
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
                                d="M6 6l12 12M18 6 6 18"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form
                    @submit.prevent="submitAdjustment"
                    class="space-y-5 px-6 py-6"
                >
                    <!-- Current Stock -->
                    <div
                        v-if="selectedInventory"
                        class="grid grid-cols-3 gap-3"
                    >
                        <div
                            class="rounded-xl bg-slate-50 p-3"
                        >
                            <p
                                class="text-[10px] font-bold uppercase tracking-wide text-slate-400"
                            >
                                Current
                            </p>

                            <p
                                class="mt-1 text-lg font-bold text-slate-900"
                            >
                                {{ selectedInventory.quantity ?? 0 }}
                            </p>
                        </div>

                        <div
                            class="rounded-xl bg-slate-50 p-3"
                        >
                            <p
                                class="text-[10px] font-bold uppercase tracking-wide text-slate-400"
                            >
                                Reserved
                            </p>

                            <p
                                class="mt-1 text-lg font-bold text-slate-900"
                            >
                                {{
                                    selectedInventory.reserved_quantity ??
                                    0
                                }}
                            </p>
                        </div>

                        <div
                            class="rounded-xl bg-green-50 p-3"
                        >
                            <p
                                class="text-[10px] font-bold uppercase tracking-wide text-green-600"
                            >
                                Available
                            </p>

                            <p
                                class="mt-1 text-lg font-bold text-green-700"
                            >
                                {{
                                    getAvailable(
                                        selectedInventory,
                                    )
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Type -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Adjustment Type
                        </label>

                        <select
                            v-model="form.type"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                        >
                            <option
                                v-for="type in adjustmentTypes"
                                :key="type.value"
                                :value="type.value"
                            >
                                {{ type.label }}
                            </option>
                        </select>

                        <p
                            v-for="type in adjustmentTypes"
                            v-if="type.value === form.type"
                            :key="`${type.value}-description`"
                            class="mt-2 text-xs text-slate-500"
                        >
                            {{ type.description }}
                        </p>

                        <p
                            v-if="form.errors.type"
                            class="mt-2 text-xs font-medium text-red-600"
                        >
                            {{ form.errors.type }}
                        </p>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Quantity
                        </label>

                        <input
                            v-model="form.quantity"
                            type="number"
                            step="1"
                            :placeholder="
                                form.type === 'adjustment' ||
                                form.type === 'correction'
                                    ? 'Use negative number to remove stock'
                                    : 'Enter quantity'
                            "
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                        />

                        <p class="mt-2 text-xs text-slate-500">
                            For adjustments and corrections,
                            positive adds stock and negative removes stock.
                        </p>

                        <p
                            v-if="form.errors.quantity"
                            class="mt-2 text-xs font-medium text-red-600"
                        >
                            {{ form.errors.quantity }}
                        </p>
                    </div>

                    <!-- Minimum Stock -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Minimum Stock Level
                        </label>

                        <input
                            v-model="form.minimum_stock"
                            type="number"
                            min="0"
                            step="1"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                        />

                        <p
                            v-if="form.errors.minimum_stock"
                            class="mt-2 text-xs font-medium text-red-600"
                        >
                            {{ form.errors.minimum_stock }}
                        </p>
                    </div>

                    <!-- Reference -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Reference
                            <span
                                class="font-normal text-slate-400"
                            >
                                (Optional)
                            </span>
                        </label>

                        <input
                            v-model="form.reference"
                            type="text"
                            placeholder="e.g. PO-00001"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                        />

                        <p
                            v-if="form.errors.reference"
                            class="mt-2 text-xs font-medium text-red-600"
                        >
                            {{ form.errors.reference }}
                        </p>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Notes
                            <span
                                class="font-normal text-slate-400"
                            >
                                (Optional)
                            </span>
                        </label>

                        <textarea
                            v-model="form.notes"
                            rows="3"
                            placeholder="Add a note about this stock movement..."
                            class="w-full resize-none rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                        ></textarea>

                        <p
                            v-if="form.errors.notes"
                            class="mt-2 text-xs font-medium text-red-600"
                        >
                            {{ form.errors.notes }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div
                        class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end"
                    >
                        <button
                            type="button"
                            @click="closeAdjustment"
                            class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-green-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{
                                form.processing
                                    ? 'Saving...'
                                    : 'Save Adjustment'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>