<script setup>
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useAdminTheme } from '@/Composables/useAdminTheme';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});

const { theme } = useAdminTheme();

const imagePreview = ref(null);

const form = useForm({
    title: '',
    description: '',
    image: null,
    product_id: '',
    button_text: 'Shop Now',
    starts_at: '',
    ends_at: '',
    is_active: true,
    sort_order: 0,
});

const isDark = computed(() => theme.value === 'dark');

const pageClasses = computed(() =>
    isDark.value
        ? 'bg-slate-950 text-slate-100'
        : 'bg-slate-50 text-slate-900'
);

const cardClasses = computed(() =>
    isDark.value
        ? 'border-slate-800 bg-slate-900'
        : 'border-slate-200 bg-white'
);

const inputClasses = computed(() =>
    isDark.value
        ? 'border-slate-700 bg-slate-950 text-white placeholder:text-slate-500'
        : 'border-slate-300 bg-white text-slate-900 placeholder:text-slate-400'
);

const labelClasses = computed(() =>
    isDark.value
        ? 'text-slate-200'
        : 'text-slate-700'
);

const headingClasses = computed(() =>
    isDark.value
        ? 'text-white'
        : 'text-slate-950'
);

const mutedClasses = computed(() =>
    isDark.value
        ? 'text-slate-400'
        : 'text-slate-500'
);

const selectedProduct = computed(() => {
    return props.products.find(
        (product) =>
            String(product.id) === String(form.product_id)
    );
});

const handleImageChange = (event) => {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    form.image = file;

    const reader = new FileReader();

    reader.onload = (e) => {
        imagePreview.value = e.target.result;
    };

    reader.readAsDataURL(file);
};

const removeImage = () => {
    form.image = null;
    imagePreview.value = null;

    const input = document.getElementById(
        'advertisement-image'
    );

    if (input) {
        input.value = '';
    }
};

const submit = () => {
    form.post('/admin/advertisements', {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <div
        class="min-h-screen transition-colors duration-300"
        :class="pageClasses"
    >
        <!-- =========================================================
             HEADER
        ========================================================== -->
        <div
            class="border-b transition-colors duration-300"
            :class="
                isDark
                    ? 'border-slate-800 bg-slate-900'
                    : 'border-slate-200 bg-white'
            "
        >
            <div
                class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p class="text-sm font-medium text-green-600">
                            Advertisements
                        </p>

                        <h1
                            class="mt-1 text-2xl font-bold tracking-tight"
                            :class="headingClasses"
                        >
                            Create Advertisement
                        </h1>

                        <p
                            class="mt-1 text-sm"
                            :class="mutedClasses"
                        >
                            Create a promotional banner for the Go
                            Pharmacy website.
                        </p>
                    </div>

                    <Link
                        href="/admin/advertisements"
                        class="inline-flex items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-semibold transition"
                        :class="
                            isDark
                                ? 'border-slate-700 bg-slate-800 text-slate-200 hover:border-slate-600 hover:bg-slate-700'
                                : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50'
                        "
                    >
                        ← Back to Advertisements
                    </Link>
                </div>
            </div>
        </div>

        <!-- =========================================================
             CONTENT
        ========================================================== -->
        <div
            class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
        >
            <form
                @submit.prevent="submit"
                class="grid gap-8 lg:grid-cols-3"
            >
                <!-- =================================================
                     MAIN CONTENT
                ================================================== -->
                <div class="space-y-8 lg:col-span-2">

                    <!-- Advertisement Details -->
                    <section
                        class="rounded-2xl border shadow-sm transition-colors duration-300"
                        :class="cardClasses"
                    >
                        <div
                            class="border-b px-6 py-5"
                            :class="
                                isDark
                                    ? 'border-slate-800'
                                    : 'border-slate-200'
                            "
                        >
                            <h2
                                class="text-lg font-bold"
                                :class="headingClasses"
                            >
                                Advertisement Details
                            </h2>

                            <p
                                class="mt-1 text-sm"
                                :class="mutedClasses"
                            >
                                Enter the content customers will see.
                            </p>
                        </div>

                        <div class="space-y-6 p-6">

                            <!-- Title -->
                            <div>
                                <label
                                    for="title"
                                    class="mb-2 block text-sm font-semibold"
                                    :class="labelClasses"
                                >
                                    Advertisement Title
                                </label>

                                <input
                                    id="title"
                                    v-model="form.title"
                                    type="text"
                                    placeholder="e.g. Get 20% Off Selected Vitamins"
                                    class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                    :class="inputClasses"
                                />

                                <p
                                    v-if="form.errors.title"
                                    class="mt-2 text-sm text-red-500"
                                >
                                    {{ form.errors.title }}
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label
                                    for="description"
                                    class="mb-2 block text-sm font-semibold"
                                    :class="labelClasses"
                                >
                                    Description
                                </label>

                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="5"
                                    placeholder="Write a short description for the advertisement..."
                                    class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                    :class="inputClasses"
                                ></textarea>

                                <p
                                    v-if="form.errors.description"
                                    class="mt-2 text-sm text-red-500"
                                >
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- Button Text -->
                            <div>
                                <label
                                    for="button_text"
                                    class="mb-2 block text-sm font-semibold"
                                    :class="labelClasses"
                                >
                                    Button Text
                                </label>

                                <input
                                    id="button_text"
                                    v-model="form.button_text"
                                    type="text"
                                    maxlength="50"
                                    placeholder="Shop Now"
                                    class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                    :class="inputClasses"
                                />

                                <p
                                    v-if="form.errors.button_text"
                                    class="mt-2 text-sm text-red-500"
                                >
                                    {{ form.errors.button_text }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Advertisement Image -->
                    <section
                        class="rounded-2xl border shadow-sm transition-colors duration-300"
                        :class="cardClasses"
                    >
                        <div
                            class="border-b px-6 py-5"
                            :class="
                                isDark
                                    ? 'border-slate-800'
                                    : 'border-slate-200'
                            "
                        >
                            <h2
                                class="text-lg font-bold"
                                :class="headingClasses"
                            >
                                Advertisement Image
                            </h2>

                            <p
                                class="mt-1 text-sm"
                                :class="mutedClasses"
                            >
                                Upload the promotional image customers
                                will see.
                            </p>
                        </div>

                        <div class="p-6">
                            <!-- Preview -->
                            <div
                                v-if="imagePreview"
                                class="relative overflow-hidden rounded-2xl border"
                                :class="
                                    isDark
                                        ? 'border-slate-700 bg-slate-950'
                                        : 'border-slate-200 bg-slate-100'
                                "
                            >
                                <img
                                    :src="imagePreview"
                                    alt="Advertisement preview"
                                    class="h-72 w-full object-cover"
                                />

                                <button
                                    type="button"
                                    @click="removeImage"
                                    class="absolute right-3 top-3 rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white shadow-lg transition hover:bg-red-700"
                                >
                                    Remove
                                </button>
                            </div>

                            <!-- Upload -->
                            <label
                                v-else
                                for="advertisement-image"
                                class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed px-6 py-12 text-center transition"
                                :class="
                                    isDark
                                        ? 'border-slate-700 bg-slate-950 hover:border-green-500 hover:bg-green-950/20'
                                        : 'border-slate-300 bg-slate-50 hover:border-green-400 hover:bg-green-50/40'
                                "
                            >
                                <div
                                    class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-green-600"
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
                                            d="M12 16V4m0 0-4 4m4-4 4 4M5 20h14"
                                        />
                                    </svg>
                                </div>

                                <span
                                    class="text-sm font-semibold"
                                    :class="labelClasses"
                                >
                                    Click to upload image
                                </span>

                                <span
                                    class="mt-1 text-xs"
                                    :class="mutedClasses"
                                >
                                    JPG, JPEG, PNG or WEBP · Maximum 5MB
                                </span>
                            </label>

                            <input
                                id="advertisement-image"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                class="hidden"
                                @change="handleImageChange"
                            />

                            <p
                                v-if="form.errors.image"
                                class="mt-2 text-sm text-red-500"
                            >
                                {{ form.errors.image }}
                            </p>
                        </div>
                    </section>

                    <!-- =================================================
                         SCHEDULE
                    ================================================== -->
                    <section
                        class="rounded-2xl border shadow-sm transition-colors duration-300"
                        :class="cardClasses"
                    >
                        <div
                            class="border-b px-6 py-5"
                            :class="
                                isDark
                                    ? 'border-slate-800'
                                    : 'border-slate-200'
                            "
                        >
                            <h2
                                class="text-lg font-bold"
                                :class="headingClasses"
                            >
                                Advertisement Schedule
                            </h2>

                            <p
                                class="mt-1 text-sm"
                                :class="mutedClasses"
                            >
                                Control when the advertisement starts
                                and when it expires.
                            </p>
                        </div>

                        <div class="grid gap-6 p-6 md:grid-cols-2">

                            <!-- Start Date -->
                            <div>
                                <label
                                    for="starts_at"
                                    class="mb-2 block text-sm font-semibold"
                                    :class="labelClasses"
                                >
                                    Start Date & Time
                                </label>

                                <input
                                    id="starts_at"
                                    v-model="form.starts_at"
                                    type="datetime-local"
                                    :class="
                                        isDark
                                            ? 'scheme-dark border-slate-700 bg-slate-950 text-white'
                                            : 'scheme-light border-slate-300 bg-white text-slate-900'
                                    "
                                    class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                />

                                <p
                                    class="mt-1 text-xs"
                                    :class="mutedClasses"
                                >
                                    Leave empty to start immediately.
                                </p>

                                <p
                                    v-if="form.errors.starts_at"
                                    class="mt-2 text-sm text-red-500"
                                >
                                    {{ form.errors.starts_at }}
                                </p>
                            </div>

                            <!-- Expiry Date -->
                            <div>
                                <label
                                    for="ends_at"
                                    class="mb-2 block text-sm font-semibold"
                                    :class="labelClasses"
                                >
                                    Expiry Date & Time
                                </label>

                                <input
                                    id="ends_at"
                                    v-model="form.ends_at"
                                    type="datetime-local"
                                    :class="
                                        isDark
                                            ? 'scheme-dark border-slate-700 bg-slate-950 text-white'
                                            : 'scheme-light border-slate-300 bg-white text-slate-900'
                                    "
                                    class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                />

                                <p
                                    class="mt-1 text-xs"
                                    :class="mutedClasses"
                                >
                                    Leave empty for no expiry date.
                                </p>

                                <p
                                    v-if="form.errors.ends_at"
                                    class="mt-2 text-sm text-red-500"
                                >
                                    {{ form.errors.ends_at }}
                                </p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- =================================================
                     SIDEBAR
                ================================================== -->
                <div class="space-y-8">

                    <!-- Target Product -->
                    <section
                        class="rounded-2xl border shadow-sm transition-colors duration-300"
                        :class="cardClasses"
                    >
                        <div
                            class="border-b px-6 py-5"
                            :class="
                                isDark
                                    ? 'border-slate-800'
                                    : 'border-slate-200'
                            "
                        >
                            <h2
                                class="text-lg font-bold"
                                :class="headingClasses"
                            >
                                Target Product
                            </h2>

                            <p
                                class="mt-1 text-sm"
                                :class="mutedClasses"
                            >
                                Choose the product customers should be
                                sent to.
                            </p>
                        </div>

                        <div class="p-6">
                            <label
                                for="product_id"
                                class="mb-2 block text-sm font-semibold"
                                :class="labelClasses"
                            >
                                Product
                            </label>

                            <select
                                id="product_id"
                                v-model="form.product_id"
                                class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                :class="inputClasses"
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
                                </option>
                            </select>

                            <p
                                v-if="form.errors.product_id"
                                class="mt-2 text-sm text-red-500"
                            >
                                {{ form.errors.product_id }}
                            </p>

                            <div
                                v-if="selectedProduct"
                                class="mt-5 rounded-xl p-4"
                                :class="
                                    isDark
                                        ? 'bg-slate-950'
                                        : 'bg-slate-50'
                                "
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-wide"
                                    :class="
                                        isDark
                                            ? 'text-slate-500'
                                            : 'text-slate-400'
                                    "
                                >
                                    Selected Product
                                </p>

                                <p
                                    class="mt-1 font-semibold"
                                    :class="headingClasses"
                                >
                                    {{ selectedProduct.name }}
                                </p>

                                <p
                                    v-if="selectedProduct.price"
                                    class="mt-1 text-sm font-bold text-green-600"
                                >
                                    ₦{{
                                        Number(
                                            selectedProduct.price
                                        ).toLocaleString()
                                    }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Display Settings -->
                    <section
                        class="rounded-2xl border shadow-sm transition-colors duration-300"
                        :class="cardClasses"
                    >
                        <div
                            class="border-b px-6 py-5"
                            :class="
                                isDark
                                    ? 'border-slate-800'
                                    : 'border-slate-200'
                            "
                        >
                            <h2
                                class="text-lg font-bold"
                                :class="headingClasses"
                            >
                                Display Settings
                            </h2>

                            <p
                                class="mt-1 text-sm"
                                :class="mutedClasses"
                            >
                                Control how the advertisement is
                                displayed.
                            </p>
                        </div>

                        <div class="space-y-6 p-6">

                            <!-- Active -->
                            <label
                                class="flex cursor-pointer items-start gap-3"
                            >
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="mt-1 h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                />

                                <span>
                                    <span
                                        class="block text-sm font-semibold"
                                        :class="headingClasses"
                                    >
                                        Active Advertisement
                                    </span>

                                    <span
                                        class="mt-1 block text-xs leading-5"
                                        :class="mutedClasses"
                                    >
                                        Allow this advertisement to
                                        appear when its schedule is
                                        active.
                                    </span>
                                </span>
                            </label>

                            <!-- Sort Order -->
                            <div>
                                <label
                                    for="sort_order"
                                    class="mb-2 block text-sm font-semibold"
                                    :class="labelClasses"
                                >
                                    Sort Order
                                </label>

                                <input
                                    id="sort_order"
                                    v-model.number="form.sort_order"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                    :class="inputClasses"
                                />

                                <p
                                    class="mt-1 text-xs"
                                    :class="mutedClasses"
                                >
                                    Lower numbers appear first.
                                </p>

                                <p
                                    v-if="form.errors.sort_order"
                                    class="mt-2 text-sm text-red-500"
                                >
                                    {{ form.errors.sort_order }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Submit -->
                    <section
                        class="rounded-2xl border p-6 shadow-sm transition-colors duration-300"
                        :class="cardClasses"
                    >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full rounded-xl bg-green-600 px-5 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{
                                form.processing
                                    ? 'Creating Advertisement...'
                                    : 'Create Advertisement'
                            }}
                        </button>

                        <Link
                            href="/admin/advertisements"
                            class="mt-3 flex w-full items-center justify-center rounded-xl border px-5 py-3.5 text-sm font-semibold transition"
                            :class="
                                isDark
                                    ? 'border-slate-700 text-slate-200 hover:bg-slate-800'
                                    : 'border-slate-200 text-slate-700 hover:bg-slate-50'
                            "
                        >
                            Cancel
                        </Link>
                    </section>
                </div>
            </form>
        </div>
    </div>
</template>