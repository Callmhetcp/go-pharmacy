<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },

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
    _method: 'PUT',

    category_id: props.product.category_id ?? '',
    supplier_id: props.product.supplier_id ?? '',

    name: props.product.name ?? '',
    slug: props.product.slug ?? '',
    sku: props.product.sku ?? '',
    barcode: props.product.barcode ?? '',

    brand: props.product.brand ?? '',
    generic_name: props.product.generic_name ?? '',

    description: props.product.description ?? '',

    price: props.product.price ?? '',
    cost_price: props.product.cost_price ?? '',

    dosage_form: props.product.dosage_form ?? '',
    strength: props.product.strength ?? '',

    minimum_stock: props.product.minimum_stock ?? 0,

    requires_prescription: Boolean(
        props.product.requires_prescription
    ),

    is_active: Boolean(
        props.product.is_active
    ),

    is_featured: Boolean(
        props.product.is_featured
    ),

    image: null,
});

const submit = () => {
    form.post(`/admin/products/${props.product.id}`, {
        forceFormData: true,
    });
};
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="mb-8">
                <Link
                    href="/admin/products"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-green-600"
                >
                    ← Back to Products
                </Link>

                <p class="mt-6 text-sm font-semibold text-green-600">
                    Catalogue Management
                </p>

                <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">
                    Edit Product
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Update product information, pricing, supplier and catalogue settings.
                </p>
            </div>

            <form
                @submit.prevent="submit"
                class="space-y-6"
            >

                <!-- Basic Information -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7"
                >
                    <div class="mb-7">
                        <h2 class="text-lg font-bold text-slate-900">
                            Basic Information
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Update the product's main catalogue information.
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
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
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
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            >
                                <option value="">
                                    Select category
                                </option>

                                <option
                                    v-for="category in categories"
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
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
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
                        </div>

                        <!-- Slug -->
                        <div>
                            <label
                                for="slug"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Slug
                            </label>

                            <input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p
                                v-if="form.errors.slug"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.slug }}
                            </p>
                        </div>

                        <!-- SKU -->
                        <div>
                            <label
                                for="sku"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                SKU
                            </label>

                            <input
                                id="sku"
                                v-model="form.sku"
                                type="text"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 font-mono text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
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
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 font-mono text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
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
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
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
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
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
                            Pricing
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Update selling and cost prices.
                        </p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">

                        <!-- Selling Price -->
                        <div>
                            <label
                                for="price"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Selling Price (₦)
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="price"
                                v-model="form.price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p
                                v-if="form.errors.price"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.price }}
                            </p>
                        </div>

                        <!-- Cost Price -->
                        <div>
                            <label
                                for="cost_price"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Cost Price (₦)
                            </label>

                            <input
                                id="cost_price"
                                v-model="form.cost_price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p
                                v-if="form.errors.cost_price"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.cost_price }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Pharmacy Details -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7"
                >
                    <div class="mb-7">
                        <h2 class="text-lg font-bold text-slate-900">
                            Pharmacy Details
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Update dosage, strength and product description.
                        </p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">

                        <!-- Dosage Form -->
                        <div>
                            <label
                                for="dosage_form"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Dosage Form
                            </label>

                            <select
                                id="dosage_form"
                                v-model="form.dosage_form"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            >
                                <option value="">
                                    Select dosage form
                                </option>

                                <option value="Tablet">Tablet</option>
                                <option value="Capsule">Capsule</option>
                                <option value="Syrup">Syrup</option>
                                <option value="Suspension">Suspension</option>
                                <option value="Cream">Cream</option>
                                <option value="Ointment">Ointment</option>
                                <option value="Gel">Gel</option>
                                <option value="Drops">Drops</option>
                                <option value="Injection">Injection</option>
                                <option value="Inhaler">Inhaler</option>
                                <option value="Powder">Powder</option>
                                <option value="Other">Other</option>
                            </select>

                            <p
                                v-if="form.errors.dosage_form"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.dosage_form }}
                            </p>
                        </div>

                        <!-- Strength -->
                        <div>
                            <label
                                for="strength"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Strength
                            </label>

                            <input
                                id="strength"
                                v-model="form.strength"
                                type="text"
                                placeholder="e.g. 500mg"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p
                                v-if="form.errors.strength"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.strength }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label
                                for="description"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Description
                            </label>

                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="5"
                                class="w-full resize-none rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
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

                <!-- Inventory -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7"
                >
                    <div class="mb-7">
                        <h2 class="text-lg font-bold text-slate-900">
                            Inventory Settings
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Configure the minimum stock warning level.
                        </p>
                    </div>

                    <div class="max-w-md">
                        <label
                            for="minimum_stock"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Minimum Stock Level
                        </label>

                        <input
                            id="minimum_stock"
                            v-model="form.minimum_stock"
                            type="number"
                            min="0"
                            step="1"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                        />

                        <p
                            v-if="form.errors.minimum_stock"
                            class="mt-2 text-xs font-medium text-red-600"
                        >
                            {{ form.errors.minimum_stock }}
                        </p>
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
                            Leave the upload empty to keep the existing image.
                        </p>
                    </div>

                    <div
                        v-if="props.product.image"
                        class="mb-5 flex items-center gap-4"
                    >
                        <img
                            :src="`/storage/${props.product.image}`"
                            :alt="props.product.name"
                            class="h-24 w-24 rounded-xl border border-slate-200 object-cover"
                        />

                        <div>
                            <p class="text-sm font-semibold text-slate-800">
                                Current image
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                Upload a new image only if you want to replace it.
                            </p>
                        </div>
                    </div>

                    <input
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp"
                        @change="form.image = $event.target.files[0]"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-green-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-green-700"
                    />

                    <p
                        v-if="form.errors.image"
                        class="mt-2 text-xs font-medium text-red-600"
                    >
                        {{ form.errors.image }}
                    </p>
                </section>

                <!-- Product Options -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7"
                >
                    <div class="mb-7">
                        <h2 class="text-lg font-bold text-slate-900">
                            Product Options
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Control product availability and purchasing requirements.
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
                                    Make this product available on the store.
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
                                    Highlight this product in featured sections.
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
                                    Require a valid prescription before purchase.
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
                                ? 'Saving Changes...'
                                : 'Save Changes'
                        }}
                    </button>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>