<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    products: {
        type: Object,
        required: true,
    },
});

const deletingId = ref(null);

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
    }).format(Number(price || 0));
};

const deleteProduct = (product) => {
    if (!confirm(`Are you sure you want to delete "${product.name}"?`)) {
        return;
    }

    deletingId.value = product.id;

    router.delete(`/admin/products/${product.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deletingId.value = null;
        },
    });
};
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Header -->
            <div
                class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <p class="text-sm font-semibold text-green-600">
                        Catalogue Management
                    </p>

                    <h1
                        class="mt-1 text-3xl font-bold tracking-tight text-slate-950"
                    >
                        Products
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Manage your pharmacy products, pricing, availability
                        and catalogue information.
                    </p>
                </div>

                <Link
                    href="/admin/products/create"
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

                    Add Product
                </Link>
            </div>

            <!-- Catalogue Card -->
            <div
                class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <!-- Card Header -->
                <div
                    class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            Product Catalogue
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ props.products.total ?? 0 }}
                            products in catalogue
                        </p>
                    </div>

                    <div
                        class="rounded-lg bg-slate-50 px-3 py-2 text-xs font-medium text-slate-500"
                    >
                        Showing
                        {{ props.products.from ?? 0 }}
                        -
                        {{ props.products.to ?? 0 }}
                        of
                        {{ props.products.total ?? 0 }}
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="props.products.data.length === 0"
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
                                stroke-width="1.8"
                                d="M20 7.5 12 3 4 7.5m16 0v9L12 21l-8-4.5v-9m16 0-8 4.5m0 0L4 7.5M12 12v9"
                            />
                        </svg>
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-slate-900">
                        No products yet
                    </h3>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm text-slate-500"
                    >
                        Start building your pharmacy catalogue by adding your
                        first product.
                    </p>

                    <Link
                        href="/admin/products/create"
                        class="mt-6 inline-flex rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700"
                    >
                        Add Your First Product
                    </Link>
                </div>

                <!-- Desktop Table -->
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
                                    Category
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Supplier
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    SKU
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Price
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr
                                v-for="product in props.products.data"
                                :key="product.id"
                                class="transition hover:bg-slate-50"
                            >
                                <!-- Product -->
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="h-12 w-12 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-100"
                                        >
                                            <img
                                                v-if="product.image"
                                                :src="`/storage/${product.image}`"
                                                :alt="product.name"
                                                class="h-full w-full object-cover"
                                            />

                                            <div
                                                v-else
                                                class="flex h-full w-full items-center justify-center text-slate-400"
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
                                                        stroke-width="1.7"
                                                        d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 0v9m8-4.5-8 4.5m0 0-8-4.5"
                                                    />
                                                </svg>
                                            </div>
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="max-w-xs truncate text-sm font-semibold text-slate-900"
                                            >
                                                {{ product.name }}
                                            </p>

                                            <p
                                                v-if="product.brand"
                                                class="mt-1 text-xs text-slate-500"
                                            >
                                                {{ product.brand }}
                                            </p>

                                            <div
                                                class="mt-1 flex flex-wrap gap-2"
                                            >
                                                <span
                                                    v-if="product.is_featured"
                                                    class="text-[10px] font-bold uppercase tracking-wide text-amber-600"
                                                >
                                                    Featured
                                                </span>

                                                <span
                                                    v-if="product.requires_prescription"
                                                    class="text-[10px] font-bold uppercase tracking-wide text-red-600"
                                                >
                                                    Prescription
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Category -->
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        class="text-sm text-slate-600"
                                    >
                                        {{ product.category?.name ?? '—' }}
                                    </span>
                                </td>

                                <!-- Supplier -->
                                <td class="px-6 py-4">
                                    <div
                                        v-if="product.supplier"
                                        class="max-w-[180px]"
                                    >
                                        <p
                                            class="truncate text-sm font-medium text-slate-700"
                                        >
                                            {{
                                                product.supplier.company_name ||
                                                product.supplier.name
                                            }}
                                        </p>

                                        <p
                                            v-if="
                                                product.supplier.company_name &&
                                                product.supplier.name
                                            "
                                            class="mt-1 truncate text-xs text-slate-400"
                                        >
                                            {{ product.supplier.name }}
                                        </p>
                                    </div>

                                    <span
                                        v-else
                                        class="text-sm text-slate-400"
                                    >
                                        —
                                    </span>
                                </td>

                                <!-- SKU -->
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        class="font-mono text-xs text-slate-500"
                                    >
                                        {{ product.sku ?? '—' }}
                                    </span>
                                </td>

                                <!-- Price -->
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        class="text-sm font-bold text-slate-900"
                                    >
                                        {{ formatPrice(product.price) }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        v-if="product.is_active"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full bg-green-500"
                                        ></span>

                                        Active
                                    </span>

                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full bg-slate-400"
                                        ></span>

                                        Inactive
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-right"
                                >
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <Link
                                            :href="`/admin/products/${product.id}/edit`"
                                            class="rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                                        >
                                            Edit
                                        </Link>

                                        <button
                                            type="button"
                                            :disabled="
                                                deletingId === product.id
                                            "
                                            @click="deleteProduct(product)"
                                            class="rounded-lg px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            {{
                                                deletingId === product.id
                                                    ? 'Deleting...'
                                                    : 'Delete'
                                            }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div
                    v-if="props.products.data.length > 0"
                    class="divide-y divide-slate-100 md:hidden"
                >
                    <div
                        v-for="product in props.products.data"
                        :key="product.id"
                        class="p-5"
                    >
                        <div class="flex gap-4">
                            <div
                                class="h-16 w-16 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-100"
                            >
                                <img
                                    v-if="product.image"
                                    :src="`/storage/${product.image}`"
                                    :alt="product.name"
                                    class="h-full w-full object-cover"
                                />

                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center text-slate-400"
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
                                            d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 0v9m8-4.5-8 4.5m0 0-8-4.5"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div>
                                        <h3
                                            class="font-semibold text-slate-900"
                                        >
                                            {{ product.name }}
                                        </h3>

                                        <p
                                            class="mt-1 text-xs text-slate-500"
                                        >
                                            {{
                                                product.category?.name ??
                                                'Uncategorised'
                                            }}
                                        </p>

                                        <p
                                            v-if="product.supplier"
                                            class="mt-1 text-xs text-slate-400"
                                        >
                                            Supplier:
                                            {{
                                                product.supplier.company_name ||
                                                product.supplier.name
                                            }}
                                        </p>
                                    </div>

                                    <span
                                        v-if="product.is_active"
                                        class="shrink-0 rounded-full bg-green-50 px-2 py-1 text-[10px] font-bold text-green-700"
                                    >
                                        Active
                                    </span>

                                    <span
                                        v-else
                                        class="shrink-0 rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-600"
                                    >
                                        Inactive
                                    </span>
                                </div>

                                <div
                                    class="mt-3 flex items-center justify-between"
                                >
                                    <span
                                        class="font-bold text-slate-900"
                                    >
                                        {{ formatPrice(product.price) }}
                                    </span>

                                    <div class="flex gap-2">
                                        <Link
                                            :href="`/admin/products/${product.id}/edit`"
                                            class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700"
                                        >
                                            Edit
                                        </Link>

                                        <button
                                            type="button"
                                            :disabled="
                                                deletingId === product.id
                                            "
                                            @click="deleteProduct(product)"
                                            class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 disabled:opacity-50"
                                        >
                                            {{
                                                deletingId === product.id
                                                    ? 'Deleting...'
                                                    : 'Delete'
                                            }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="
                        props.products.links &&
                        props.products.links.length > 3
                    "
                    class="flex flex-wrap items-center justify-center gap-2 border-t border-slate-200 px-6 py-5"
                >
                    <template
                        v-for="(link, index) in props.products.links"
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
    </AdminLayout>
</template>