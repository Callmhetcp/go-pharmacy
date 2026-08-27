<script setup>
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    suppliers: {
        type: Array,
        default: () => [],
    },
    products: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    supplier_id: '',
    reference: '',
    purchase_date: new Date().toISOString().split('T')[0],
    discount: 0,
    status: 'draft',
    notes: '',
    items: [],
});

const productSearch = ref('');
const showProductList = ref(false);

const filteredProducts = computed(() => {
    const search = productSearch.value.trim().toLowerCase();

    if (!search) {
        return props.products;
    }

    return props.products.filter((product) => {
        return (
            product.name?.toLowerCase().includes(search) ||
            product.sku?.toLowerCase().includes(search) ||
            product.barcode?.toLowerCase().includes(search)
        );
    });
});

const subtotal = computed(() => {
    return form.items.reduce((total, item) => {
        return total + Number(item.quantity || 0) * Number(item.unit_cost || 0);
    }, 0);
});

const discountAmount = computed(() => {
    return Math.min(Number(form.discount || 0), subtotal.value);
});

const totalAmount = computed(() => {
    return Math.max(0, subtotal.value - discountAmount.value);
});

const formatPrice = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
    }).format(Number(amount || 0));
};

const addProduct = (product) => {
    const existingItem = form.items.find(
        (item) => Number(item.product_id) === Number(product.id),
    );

    if (existingItem) {
        existingItem.quantity = Number(existingItem.quantity || 0) + 1;
    } else {
        form.items.push({
            product_id: product.id,
            quantity: 1,
            unit_cost: 0,
            batch_number: '',
            expiry_date: '',
        });
    }

    productSearch.value = '';
    showProductList.value = false;
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const getProduct = (productId) => {
    return props.products.find(
        (product) => Number(product.id) === Number(productId),
    );
};

const submit = () => {
    form.post('/admin/purchases', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex items-start gap-4">
                <Link
                    href="/admin/purchases"
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

                    <h1
                        class="mt-1 text-3xl font-bold tracking-tight text-slate-950"
                    >
                        Create Purchase Order
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Record products purchased from a supplier.
                    </p>
                </div>
            </div>

            <form
                class="mt-8 space-y-6"
                @submit.prevent="submit"
            >
                <!-- Purchase Information -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            Purchase Information
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Enter the supplier and purchase order details.
                        </p>
                    </div>

                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        <!-- Supplier -->
                        <div>
                            <label
                                class="text-sm font-semibold text-slate-700"
                            >
                                Supplier
                            </label>

                            <select
                                v-model="form.supplier_id"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            >
                                <option value="">
                                    Select supplier
                                </option>

                                <option
                                    v-for="supplier in suppliers"
                                    :key="supplier.id"
                                    :value="supplier.id"
                                >
                                    {{ supplier.name }}
                                    {{
                                        supplier.company_name
                                            ? ` — ${supplier.company_name}`
                                            : ''
                                    }}
                                </option>
                            </select>

                            <p
                                v-if="form.errors.supplier_id"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.supplier_id }}
                            </p>
                        </div>

                        <!-- Reference -->
                        <div>
                            <label
                                class="text-sm font-semibold text-slate-700"
                            >
                                Purchase Reference
                            </label>

                            <input
                                v-model="form.reference"
                                type="text"
                                placeholder="e.g. PO-2026-0001"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p
                                v-if="form.errors.reference"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.reference }}
                            </p>
                        </div>

                        <!-- Date -->
                        <div>
                            <label
                                class="text-sm font-semibold text-slate-700"
                            >
                                Purchase Date
                            </label>

                            <input
                                v-model="form.purchase_date"
                                type="date"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p
                                v-if="form.errors.purchase_date"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.purchase_date }}
                            </p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label
                                class="text-sm font-semibold text-slate-700"
                            >
                                Status
                            </label>

                            <select
                                v-model="form.status"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            >
                                <option value="draft">
                                    Draft
                                </option>

                                <option value="ordered">
                                    Ordered
                                </option>

                                <option value="received">
                                    Received
                                </option>

                                <option value="cancelled">
                                    Cancelled
                                </option>
                            </select>

                            <p
                                v-if="form.errors.status"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.status }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Products -->
                <section
                    class="overflow-visible rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h2 class="text-lg font-bold text-slate-900">
                            Purchase Items
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Add the products received or ordered from the
                            supplier.
                        </p>
                    </div>

                    <!-- Product Search -->
                    <div class="relative border-b border-slate-200 p-6">
                        <label
                            class="text-sm font-semibold text-slate-700"
                        >
                            Add Product
                        </label>

                        <input
                            v-model="productSearch"
                            type="text"
                            placeholder="Search by product name, SKU or barcode..."
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            @focus="showProductList = true"
                        />

                        <!-- Search Results -->
                        <div
                            v-if="
                                showProductList &&
                                productSearch.trim() &&
                                filteredProducts.length
                            "
                            class="absolute left-6 right-6 top-[94px] z-20 max-h-72 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl"
                        >
                            <button
                                v-for="product in filteredProducts"
                                :key="product.id"
                                type="button"
                                class="flex w-full items-center justify-between gap-4 border-b border-slate-100 px-4 py-3 text-left transition last:border-0 hover:bg-slate-50"
                                @click="addProduct(product)"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-semibold text-slate-900"
                                    >
                                        {{ product.name }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{
                                            product.sku
                                                ? `SKU: ${product.sku}`
                                                : 'No SKU'
                                        }}
                                    </p>
                                </div>

                                <span
                                    class="shrink-0 rounded-lg bg-green-50 px-3 py-1 text-xs font-semibold text-green-700"
                                >
                                    Add
                                </span>
                            </button>
                        </div>

                        <div
                            v-if="
                                showProductList &&
                                productSearch.trim() &&
                                !filteredProducts.length
                            "
                            class="absolute left-6 right-6 top-[94px] z-20 rounded-xl border border-slate-200 bg-white p-5 text-center shadow-xl"
                        >
                            <p class="text-sm font-semibold text-slate-700">
                                No products found
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Try searching by another name, SKU or barcode.
                            </p>
                        </div>
                    </div>

                    <!-- Items -->
                    <div
                        v-if="form.items.length"
                        class="divide-y divide-slate-100"
                    >
                        <div
                            v-for="(item, index) in form.items"
                            :key="`${item.product_id}-${index}`"
                            class="p-6"
                        >
                            <div
                                class="flex flex-col gap-5 lg:flex-row lg:items-start"
                            >
                                <!-- Product -->
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="text-sm font-bold text-slate-900"
                                    >
                                        {{
                                            getProduct(item.product_id)?.name ??
                                            'Product'
                                        }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        SKU:
                                        {{
                                            getProduct(item.product_id)?.sku ??
                                            '—'
                                        }}
                                    </p>
                                </div>

                                <!-- Quantity -->
                                <div class="w-full lg:w-32">
                                    <label
                                        class="text-xs font-semibold text-slate-500"
                                    >
                                        Quantity
                                    </label>

                                    <input
                                        v-model.number="item.quantity"
                                        type="number"
                                        min="1"
                                        class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                    />

                                    <p
                                        v-if="
                                            form.errors[
                                                `items.${index}.quantity`
                                            ]
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            form.errors[
                                                `items.${index}.quantity`
                                            ]
                                        }}
                                    </p>
                                </div>

                                <!-- Unit Cost -->
                                <div class="w-full lg:w-40">
                                    <label
                                        class="text-xs font-semibold text-slate-500"
                                    >
                                        Unit Cost
                                    </label>

                                    <input
                                        v-model.number="item.unit_cost"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                    />

                                    <p
                                        v-if="
                                            form.errors[
                                                `items.${index}.unit_cost`
                                            ]
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            form.errors[
                                                `items.${index}.unit_cost`
                                            ]
                                        }}
                                    </p>
                                </div>

                                <!-- Total -->
                                <div class="w-full lg:w-36">
                                    <label
                                        class="text-xs font-semibold text-slate-500"
                                    >
                                        Total
                                    </label>

                                    <div
                                        class="mt-2 rounded-xl bg-slate-50 px-3 py-2.5 text-sm font-bold text-slate-900"
                                    >
                                        {{
                                            formatPrice(
                                                Number(item.quantity || 0) *
                                                    Number(
                                                        item.unit_cost || 0,
                                                    ),
                                            )
                                        }}
                                    </div>
                                </div>

                                <!-- Remove -->
                                <div class="lg:pt-6">
                                    <button
                                        type="button"
                                        class="rounded-xl px-3 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                                        @click="removeItem(index)"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>

                            <!-- Batch / Expiry -->
                            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label
                                        class="text-xs font-semibold text-slate-500"
                                    >
                                        Batch Number
                                    </label>

                                    <input
                                        v-model="item.batch_number"
                                        type="text"
                                        placeholder="Optional batch number"
                                        class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                    />

                                    <p
                                        v-if="
                                            form.errors[
                                                `items.${index}.batch_number`
                                            ]
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            form.errors[
                                                `items.${index}.batch_number`
                                            ]
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="text-xs font-semibold text-slate-500"
                                    >
                                        Expiry Date
                                    </label>

                                    <input
                                        v-model="item.expiry_date"
                                        type="date"
                                        class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                    />

                                    <p
                                        v-if="
                                            form.errors[
                                                `items.${index}.expiry_date`
                                            ]
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            form.errors[
                                                `items.${index}.expiry_date`
                                            ]
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty -->
                    <div
                        v-else
                        class="px-6 py-16 text-center"
                    >
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-400"
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
                                    d="M12 3v18m9-9H3"
                                />
                            </svg>
                        </div>

                        <h3
                            class="mt-4 text-sm font-bold text-slate-900"
                        >
                            No products added
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Search for a product above to add it to this
                            purchase.
                        </p>
                    </div>
                </section>

                <p
                    v-if="form.errors.items"
                    class="text-sm font-medium text-red-600"
                >
                    {{ form.errors.items }}
                </p>

                <!-- Summary -->
                <section
                    class="grid gap-6 lg:grid-cols-3"
                >
                    <!-- Notes -->
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2"
                    >
                        <h2 class="text-lg font-bold text-slate-900">
                            Notes
                        </h2>

                        <textarea
                            v-model="form.notes"
                            rows="6"
                            placeholder="Add any useful information about this purchase..."
                            class="mt-4 w-full resize-none rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                        />

                        <p
                            v-if="form.errors.notes"
                            class="mt-2 text-xs font-medium text-red-600"
                        >
                            {{ form.errors.notes }}
                        </p>
                    </div>

                    <!-- Totals -->
                    <div
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
                                    {{ formatPrice(subtotal) }}
                                </span>
                            </div>

                            <div>
                                <label
                                    class="text-sm font-medium text-slate-500"
                                >
                                    Discount
                                </label>

                                <input
                                    v-model.number="form.discount"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                />

                                <p
                                    v-if="form.errors.discount"
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{ form.errors.discount }}
                                </p>
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
                                        {{ formatPrice(totalAmount) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Actions -->
                <div
                    class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
                >
                    <Link
                        href="/admin/purchases"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Cancel
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing || !form.items.length"
                        class="inline-flex items-center justify-center rounded-xl bg-green-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{
                            form.processing
                                ? 'Saving...'
                                : 'Create Purchase Order'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
