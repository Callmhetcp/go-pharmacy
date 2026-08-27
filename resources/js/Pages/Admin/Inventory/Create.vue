<script setup>
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    product_id: '',
    type: 'purchase',
    quantity: '',
    reference: '',
    notes: '',
    minimum_stock: '',
});

const selectedProduct = computed(() => {
    return props.products.find(
        product => Number(product.id) === Number(form.product_id),
    );
});

const isDecrease = computed(() => {
    return ['damaged', 'expired'].includes(form.type);
});

const submit = () => {
    form.post('/admin/inventory', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link
                    href="/admin/inventory"
                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
                >
                    ←
                </Link>

                <div>
                    <p class="text-sm font-semibold text-green-600">
                        Inventory Management
                    </p>

                    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">
                        Adjust Stock
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Record a stock movement for a pharmacy product.
                    </p>
                </div>
            </div>

            <!-- Form -->
            <form
                @submit.prevent="submit"
                class="mt-8 space-y-6"
            >

                <!-- Product -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6"
                >
                    <h2 class="text-lg font-bold text-slate-900">
                        Product
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Select the product whose inventory you want to update.
                    </p>

                    <div class="mt-6">
                        <label class="text-sm font-semibold text-slate-700">
                            Product
                        </label>

                        <select
                            v-model="form.product_id"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                        >
                            <option value="">
                                Select a product
                            </option>

                            <option
                                v-for="product in products"
                                :key="product.id"
                                :value="product.id"
                            >
                                {{ product.name }}
                                {{ product.sku ? ` — ${product.sku}` : '' }}
                            </option>
                        </select>

                        <p
                            v-if="form.errors.product_id"
                            class="mt-2 text-xs font-medium text-red-600"
                        >
                            {{ form.errors.product_id }}
                        </p>
                    </div>

                    <!-- Selected product -->
                    <div
                        v-if="selectedProduct"
                        class="mt-5 rounded-xl bg-slate-50 p-4"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-900">
                                    {{ selectedProduct.name }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    SKU:
                                    {{ selectedProduct.sku ?? 'Not assigned' }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-xs text-slate-400">
                                    Product minimum
                                </p>

                                <p class="font-bold text-slate-900">
                                    {{ selectedProduct.minimum_stock ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Movement -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6"
                >
                    <h2 class="text-lg font-bold text-slate-900">
                        Stock Movement
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Specify what happened to the stock.
                    </p>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">

                        <!-- Type -->
                        <div>
                            <label class="text-sm font-semibold text-slate-700">
                                Movement Type
                            </label>

                            <select
                                v-model="form.type"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            >
                                <option value="purchase">
                                    Purchase / Restock
                                </option>

                                <option value="return">
                                    Customer Return
                                </option>

                                <option value="adjustment">
                                    Stock Adjustment
                                </option>

                                <option value="correction">
                                    Correction
                                </option>

                                <option value="damaged">
                                    Damaged Stock
                                </option>

                                <option value="expired">
                                    Expired Stock
                                </option>
                            </select>

                            <p
                                v-if="form.errors.type"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.type }}
                            </p>
                        </div>

                        <!-- Quantity -->
                        <div>
                            <label class="text-sm font-semibold text-slate-700">
                                Quantity
                            </label>

                            <input
                                v-model="form.quantity"
                                type="number"
                                min="1"
                                placeholder="Enter quantity"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p class="mt-2 text-xs text-slate-400">
                                {{ isDecrease
                                    ? 'This movement will reduce available stock.'
                                    : 'Enter the number of units involved.' }}
                            </p>

                            <p
                                v-if="form.errors.quantity"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.quantity }}
                            </p>
                        </div>

                        <!-- Reference -->
                        <div>
                            <label class="text-sm font-semibold text-slate-700">
                                Reference
                                <span class="font-normal text-slate-400">
                                    (optional)
                                </span>
                            </label>

                            <input
                                v-model="form.reference"
                                type="text"
                                placeholder="e.g. INV-00025"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p
                                v-if="form.errors.reference"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.reference }}
                            </p>
                        </div>

                        <!-- Minimum Stock -->
                        <div>
                            <label class="text-sm font-semibold text-slate-700">
                                Minimum Stock
                            </label>

                            <input
                                v-model="form.minimum_stock"
                                type="number"
                                min="0"
                                placeholder="e.g. 10"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p class="mt-2 text-xs text-slate-400">
                                Used to identify low-stock products.
                            </p>

                            <p
                                v-if="form.errors.minimum_stock"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.minimum_stock }}
                            </p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mt-5">
                        <label class="text-sm font-semibold text-slate-700">
                            Notes
                            <span class="font-normal text-slate-400">
                                (optional)
                            </span>
                        </label>

                        <textarea
                            v-model="form.notes"
                            rows="4"
                            placeholder="Add any useful information about this stock movement..."
                            class="mt-2 w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                        ></textarea>

                        <p
                            v-if="form.errors.notes"
                            class="mt-2 text-xs font-medium text-red-600"
                        >
                            {{ form.errors.notes }}
                        </p>
                    </div>
                </section>

                <!-- Actions -->
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <Link
                        href="/admin/inventory"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{
                            form.processing
                                ? 'Saving...'
                                : 'Save Stock Movement'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>