<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },

    suppliers: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    category_id: '',
    supplier_id: '',
    name: '',
    sku: '',
    barcode: '',
    brand: '',
    generic_name: '',
    description: '',
    short_description: '',
    price: '',
    sale_price: '',
    cost_price: '',
    minimum_stock: 0,
    image: null,
    is_active: true,
    is_featured: false,
    requires_prescription: false,
});

const submit = () => {
    form.post('/admin/products', {
        forceFormData: true,
    });
};
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="mb-8">
                <Link
                    href="/admin/products"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-green-600"
                >
                    ← Back to Products
                </Link>

                <p class="mt-6 text-sm font-semibold text-green-600">
                    Product Management
                </p>

                <h1
                    class="mt-1 text-3xl font-bold tracking-tight text-slate-950"
                >
                    Add Product
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Add a new medicine or pharmacy product to your inventory.
                </p>
            </div>

            <form
                @submit.prevent="submit"
                class="space-y-6"
            >

                <!-- Product Information -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7"
                >
                    <div class="mb-7">
                        <h2 class="text-lg font-bold text-slate-900">
                            Product Information
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Enter the basic information for this product.
                        </p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">

                        <!-- Product Name -->
                        <div class="md:col-span-2">
                            <label
                                for="name"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Product Name
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. Paracetamol 500mg"
                                class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-50"
                            />

                            <p
                                v-if="form.errors.name"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Category -->
                        <div>
                            <label
                                for="category_id"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Category
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-50"
                            >
                                <option value="">
                                    Select category
                                </option>

                                <option
                                    v-for="category in props.categories"
                                    :key="category.id"
                                    :value="category.id"
                                >
                                    {{ category.name }}
                                </option>
                            </select>

                            <p
                                v-if="form.errors.category_id"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.category_id }}
                            </p>
                        </div>

                        <!-- Supplier -->
                        <div>
                            <label
                                for="supplier_id"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Supplier
                            </label>

                            <select
                                id="supplier_id"
                                v-model="form.supplier_id"
                                class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-50"
                            >
                                <option value="">
                                    Select supplier
                                </option>

                                <option
                                    v-for="supplier in props.suppliers"
                                    :key="supplier.id"
                                    :value="supplier.id"
                                >
                                    {{
                                        supplier.company_name
                                            ? `${supplier.name} — ${supplier.company_name}`
                                            : supplier.name
                                    }}
                                </option>
                            </select>

                            <p
                                v-if="form.errors.supplier_id"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.supplier_id }}
                            </p>

                            <p
                                v-if="props.suppliers.length === 0"
                                class="mt-2 text-xs text-slate-500"
                            >
                                No active suppliers available.
                            </p>
                        </div>

                        <!-- SKU -->
                        <div>
                            <label
                                for="sku"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                SKU
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="sku"
                                v-model="form.sku"
                                type="text"
                                placeholder="e.g. GO-PARA-500"
                                class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-50"
                            />

                            <p
                                v-if="form.errors.sku"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.sku }}
                            </p>
                        </div>

                        <!-- Barcode -->
                        <div>
                            <label
                                for="barcode"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Barcode
                            </label>

                            <input
                                id="barcode"
                                v-model="form.barcode"
                                type="text"
                                placeholder="e.g. 6151100012345"
                                class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-50"
                            />

                            <p
                                v-if="form.errors.barcode"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.barcode }}
                            </p>
                        </div>

                        <!-- Brand -->
                        <div>
                            <label
                                for="brand"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Brand
                            </label>

                            <input
                                id="brand"
                                v-model="form.brand"
                                type="text"
                                placeholder="e.g. Emzor"
                                class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-50"
                            />

                            <p
                                v-if="form.errors.brand"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.brand }}
                            </p>
                        </div>

                        <!-- Generic Name -->
                        <div>
                            <label
                                for="generic_name"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Generic Name
                            </label>

                            <input
                                id="generic_name"
                                v-model="form.generic_name"
                                type="text"
                                placeholder="e.g. Paracetamol"
                                class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-50"
                            />

                            <p
                                v-if="form.errors.generic_name"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.generic_name }}
                            </p>
                        </div>

                    </div>
                </section>

                <!-- Pricing -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7"
                >
                    <div class="mb-7">
                        <h2 class="text-lg font-bold text-slate-900">
                            Pricing & Inventory
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Set product pricing and stock information.
                        </p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">

                        <!-- Cost Price -->
                        <div>
                            <label
                                for="cost_price"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Cost Price
                            </label>

                            <input
                                id="cost_price"
                                v-model="form.cost_price"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="₦0.00"
                                class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-50"
                            />

                            <p
                                v-if="form.errors.cost_price"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.cost_price }}
                            </p>
                        </div>

                        <!-- Selling Price -->
                        <div>
                            <label
                                for="price"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Selling Price
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="price"
                                v-model="form.price"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="₦0.00"
                                class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-50"
                            />

                            <p
                                v-if="form.errors.price"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.price }}
                            </p>
                        </div>

                        <!-- Sale Price -->
                        <div>
                            <label
                                for="sale_price"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Sale Price
                            </label>

                            <input
                                id="sale_price"
                                v-model="form.sale_price"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="₦0.00"
                                class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-50"
                            />

                            <p
                                v-if="form.errors.sale_price"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.sale_price }}
                            </p>
                        </div>

                        <!-- Minimum Stock -->
                        <div>
                            <label
                                for="minimum_stock"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Minimum Stock
                            </label>

                            <input
                                id="minimum_stock"
                                v-model="form.minimum_stock"
                                type="number"
                                min="0"
                                placeholder="0"
                                class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-50"
                            />

                            <p
                                v-if="form.errors.minimum_stock"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.minimum_stock }}
                            </p>
                        </div>

                    </div>
                </section>

                <!-- Description -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7"
                >
                    <div class="mb-7">
                        <h2 class="text-lg font-bold text-slate-900">
                            Product Description
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Add information customers should know about this product.
                        </p>
                    </div>

                    <div class="space-y-6">

                        <!-- Short Description -->
                        <div>
                            <label
                                for="short_description"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Short Description
                            </label>

                            <textarea
                                id="short_description"
                                v-model="form.short_description"
                                rows="3"
                                placeholder="Brief description of the product..."
                                class="min-h-28 w-full resize-y rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm leading-6 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-50"
                            ></textarea>

                            <p
                                v-if="form.errors.short_description"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.short_description }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label
                                for="description"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Full Description
                            </label>

                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="6"
                                placeholder="Enter detailed product information..."
                                class="min-h-40 w-full resize-y rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm leading-6 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-50"
                            ></textarea>

                            <p
                                v-if="form.errors.description"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>

                    </div>
                </section>

                <!-- Product Image -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7"
                >
                    <div class="mb-7">
                        <h2 class="text-lg font-bold text-slate-900">
                            Product Image
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Upload the main image for this product.
                        </p>
                    </div>

                    <div>
                        <label
                            for="image"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Product Image
                        </label>

                        <input
                            id="image"
                            type="file"
                            accept="image/*"
                            @change="form.image = $event.target.files[0]"
                            class="block w-full cursor-pointer rounded-xl border border-slate-300 bg-white text-sm text-slate-600 file:mr-4 file:border-0 file:bg-green-50 file:px-5 file:py-3 file:text-sm file:font-semibold file:text-green-700 hover:file:bg-green-100"
                        />

                        <p class="mt-2 text-xs text-slate-500">
                            Recommended: JPG, PNG or WEBP.
                        </p>

                        <p
                            v-if="form.errors.image"
                            class="mt-2 text-xs font-medium text-red-600"
                        >
                            {{ form.errors.image }}
                        </p>
                    </div>
                </section>

                <!-- Product Settings -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7"
                >
                    <div class="mb-7">
                        <h2 class="text-lg font-bold text-slate-900">
                            Product Settings
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Control how this product behaves across Go Pharmacy.
                        </p>
                    </div>

                    <div class="space-y-5">

                        <!-- Active -->
                        <label class="flex cursor-pointer items-start gap-3">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                            />

                            <span>
                                <span class="block text-sm font-semibold text-slate-800">
                                    Active product
                                </span>

                                <span class="mt-1 block text-xs leading-5 text-slate-500">
                                    Make this product available on the website and
                                    administrative systems.
                                </span>
                            </span>
                        </label>

                        <!-- Featured -->
                        <label class="flex cursor-pointer items-start gap-3">
                            <input
                                v-model="form.is_featured"
                                type="checkbox"
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                            />

                            <span>
                                <span class="block text-sm font-semibold text-slate-800">
                                    Featured product
                                </span>

                                <span class="mt-1 block text-xs leading-5 text-slate-500">
                                    Highlight this product in featured product sections.
                                </span>
                            </span>
                        </label>

                        <!-- Prescription -->
                        <label class="flex cursor-pointer items-start gap-3">
                            <input
                                v-model="form.requires_prescription"
                                type="checkbox"
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                            />

                            <span>
                                <span class="block text-sm font-semibold text-slate-800">
                                    Prescription required
                                </span>

                                <span class="mt-1 block text-xs leading-5 text-slate-500">
                                    Require a valid prescription before this product
                                    can be purchased.
                                </span>
                            </span>
                        </label>

                    </div>
                </section>

                <!-- Actions -->
                <div
                    class="flex flex-col-reverse gap-3 pb-6 sm:flex-row sm:justify-end"
                >
                    <Link
                        href="/admin/products"
                        class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-6 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Cancel
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex h-12 items-center justify-center rounded-xl bg-green-600 px-7 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{
                            form.processing
                                ? 'Creating Product...'
                                : 'Create Product'
                        }}
                    </button>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>