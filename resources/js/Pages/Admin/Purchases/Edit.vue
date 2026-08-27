<script setup>
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    purchase: {
        type: Object,
        required: true,
    },
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
    supplier_id: props.purchase.supplier_id ?? '',
    reference: props.purchase.reference ?? '',
    purchase_date: props.purchase.purchase_date
        ? String(props.purchase.purchase_date).substring(0, 10)
        : '',
    discount: Number(props.purchase.discount ?? 0),
    status: props.purchase.status ?? 'draft',
    notes: props.purchase.notes ?? '',
    items: (props.purchase.items ?? []).map((item) => ({
        product_id: item.product_id ?? '',
        quantity: Number(item.quantity ?? 1),
        unit_cost: Number(item.unit_cost ?? 0),
        batch_number: item.batch_number ?? '',
        expiry_date: item.expiry_date
            ? String(item.expiry_date).substring(0, 10)
            : '',
    })),
});

const processing = ref(false);

const subtotal = computed(() => {
    return form.items.reduce((total, item) => {
        return (
            total +
            Number(item.quantity || 0) * Number(item.unit_cost || 0)
        );
    }, 0);
});

const totalAmount = computed(() => {
    return Math.max(
        0,
        subtotal.value - Number(form.discount || 0),
    );
});

const formatPrice = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
    }).format(Number(amount || 0));
};

const productName = (productId) => {
    const product = props.products.find(
        (item) => Number(item.id) === Number(productId),
    );

    return product?.name ?? 'Select product';
};

const addItem = () => {
    form.items.push({
        product_id: '',
        quantity: 1,
        unit_cost: 0,
        batch_number: '',
        expiry_date: '',
    });
};

const removeItem = (index) => {
    if (form.items.length === 1) {
        return;
    }

    form.items.splice(index, 1);
};

const submit = () => {
    processing.value = true;

    form.put(`/admin/purchases/${props.purchase.id}`, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                <div class="flex items-start gap-4">
                    <Link
                        :href="`/admin/purchases/${purchase.id}`"
                        class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
                    >
                        ←
                    </Link>

                    <div>
                        <p class="text-sm font-semibold text-green-600">
                            Procurement Management
                        </p>

                        <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">
                            Edit Purchase Order
                        </h1>

                        <p class="mt-2 text-sm text-slate-500">
                            Update purchase {{ purchase.reference }} before it is received.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Validation Errors -->
            <div
                v-if="Object.keys(form.errors).length"
                class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-5"
            >
                <p class="text-sm font-bold text-red-800">
                    Please correct the following errors:
                </p>

                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                    <li
                        v-for="(error, key) in form.errors"
                        :key="key"
                    >
                        {{ error }}
                    </li>
                </ul>
            </div>

            <form
                class="mt-8 space-y-6"
                @submit.prevent="submit"
            >
                <!-- Purchase Information -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h2 class="text-lg font-bold text-slate-900">
                            Purchase Information
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Update supplier, reference, date and purchase status.
                        </p>
                    </div>

                    <div class="grid gap-5 p-6 md:grid-cols-2">
                        <!-- Supplier -->
                        <div>
                            <label class="text-sm font-semibold text-slate-700">
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
                                    {{
                                        supplier.company_name
                                            ? `${supplier.company_name} — ${supplier.name}`
                                            : supplier.name
                                    }}
                                </option>
                            </select>

                            <p
                                v-if="form.errors.supplier_id"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.supplier_id }}
                            </p>
                        </div>

                        <!-- Reference -->
                        <div>
                            <label class="text-sm font-semibold text-slate-700">
                                Reference
                            </label>

                            <input
                                v-model="form.reference"
                                type="text"
                                placeholder="e.g. PO-2026-0001"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p
                                v-if="form.errors.reference"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.reference }}
                            </p>
                        </div>

                        <!-- Purchase Date -->
                        <div>
                            <label class="text-sm font-semibold text-slate-700">
                                Purchase Date
                            </label>

                            <input
                                v-model="form.purchase_date"
                                type="date"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p
                                v-if="form.errors.purchase_date"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.purchase_date }}
                            </p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="text-sm font-semibold text-slate-700">
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
                            </select>

                            <p class="mt-1 text-xs text-slate-400">
                                Received and cancelled purchases are locked.
                            </p>
                        </div>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label class="text-sm font-semibold text-slate-700">
                                Notes
                            </label>

                            <textarea
                                v-model="form.notes"
                                rows="4"
                                placeholder="Optional notes about this purchase..."
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Purchase Items -->
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col justify-between gap-4 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">
                                Purchase Items
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Update products, quantities, costs and batch information.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700"
                            @click="addItem"
                        >
                            + Add Item
                        </button>
                    </div>

                    <div class="space-y-5 p-6">
                        <div
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">
                                        Item {{ index + 1 }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ productName(item.product_id) }}
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    class="rounded-lg px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                                    :disabled="form.items.length === 1"
                                    @click="removeItem(index)"
                                >
                                    Remove
                                </button>
                            </div>

                            <div class="mt-5 grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                                <!-- Product -->
                                <div class="lg:col-span-2">
                                    <label class="text-xs font-semibold text-slate-600">
                                        Product
                                    </label>

                                    <select
                                        v-model="item.product_id"
                                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                    >
                                        <option value="">
                                            Select product
                                        </option>

                                        <option
                                            v-for="product in products"
                                            :key="product.id"
                                            :value="product.id"
                                        >
                                            {{ product.name }}
                                            <template v-if="product.sku">
                                                — {{ product.sku }}
                                            </template>
                                        </option>
                                    </select>
                                </div>

                                <!-- Quantity -->
                                <div>
                                    <label class="text-xs font-semibold text-slate-600">
                                        Quantity
                                    </label>

                                    <input
                                        v-model.number="item.quantity"
                                        type="number"
                                        min="1"
                                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                    />
                                </div>

                                <!-- Unit Cost -->
                                <div>
                                    <label class="text-xs font-semibold text-slate-600">
                                        Unit Cost
                                    </label>

                                    <input
                                        v-model.number="item.unit_cost"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                    />
                                </div>

                                <!-- Total -->
                                <div>
                                    <label class="text-xs font-semibold text-slate-600">
                                        Item Total
                                    </label>

                                    <div class="mt-2 flex min-h-[46px] items-center rounded-xl border border-slate-200 bg-slate-100 px-3 text-sm font-bold text-slate-900">
                                        {{
                                            formatPrice(
                                                Number(item.quantity || 0) *
                                                    Number(item.unit_cost || 0),
                                            )
                                        }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                <!-- Batch -->
                                <div>
                                    <label class="text-xs font-semibold text-slate-600">
                                        Batch Number
                                    </label>

                                    <input
                                        v-model="item.batch_number"
                                        type="text"
                                        placeholder="Optional"
                                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                    />
                                </div>

                                <!-- Expiry -->
                                <div>
                                    <label class="text-xs font-semibold text-slate-600">
                                        Expiry Date
                                    </label>

                                    <input
                                        v-model="item.expiry_date"
                                        type="date"
                                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                    />
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="!form.items.length"
                            class="rounded-2xl border border-dashed border-slate-300 px-6 py-12 text-center"
                        >
                            <p class="text-sm font-semibold text-slate-700">
                                No purchase items
                            </p>

                            <button
                                type="button"
                                class="mt-3 text-sm font-semibold text-green-600 hover:text-green-700"
                                @click="addItem"
                            >
                                Add the first item
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-slate-900">
                            Discount
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Apply an optional discount to this purchase.
                        </p>

                        <div class="mt-5 max-w-sm">
                            <label class="text-sm font-semibold text-slate-700">
                                Discount Amount
                            </label>

                            <input
                                v-model.number="form.discount"
                                type="number"
                                min="0"
                                step="0.01"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p
                                v-if="Number(form.discount) > subtotal"
                                class="mt-2 text-xs font-semibold text-red-600"
                            >
                                Discount cannot be greater than the subtotal.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-950 p-6 text-white shadow-sm">
                        <h2 class="text-lg font-bold">
                            Purchase Summary
                        </h2>

                        <div class="mt-6 space-y-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-400">
                                    Subtotal
                                </span>

                                <span class="font-semibold">
                                    {{ formatPrice(subtotal) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-400">
                                    Discount
                                </span>

                                <span class="font-semibold">
                                    -{{ formatPrice(form.discount) }}
                                </span>
                            </div>

                            <div class="border-t border-slate-700 pt-4">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">
                                        Total
                                    </span>

                                    <span class="text-2xl font-bold">
                                        {{ formatPrice(totalAmount) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <Link
                        :href="`/admin/purchases/${purchase.id}`"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Cancel
                    </Link>

                    <button
                        type="submit"
                        :disabled="
                            form.processing ||
                            processing ||
                            !form.items.length ||
                            Number(form.discount) > subtotal
                        "
                        class="inline-flex items-center justify-center rounded-xl bg-green-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{
                            form.processing || processing
                                ? 'Saving Changes...'
                                : 'Save Changes'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
