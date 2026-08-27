<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const props = defineProps({
    products: {
        type: Object,
        required: true,
    },

    categories: {
        type: Array,
        default: () => [],
    },

    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search ?? '');
const category = ref(props.filters.category ?? '');
const prescription = ref(props.filters.prescription ?? '');
const sort = ref(props.filters.sort ?? '');

const showFilters = ref(false);
const addingProduct = ref(null);

const applyFilters = () => {
    router.get(
        route('shop.index'),
        {
            search: search.value || undefined,
            category: category.value || undefined,
            prescription: prescription.value || undefined,
            sort: sort.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const clearFilters = () => {
    search.value = '';
    category.value = '';
    prescription.value = '';
    sort.value = '';

    router.get(
        route('shop.index'),
        {},
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const productCount = computed(
    () => props.products.total ?? 0
);

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        maximumFractionDigits: 0,
    }).format(Number(price));
};

const imageUrl = (image) => {
    if (!image) {
        return null;
    }

    return image.startsWith('http')
        ? image
        : `/storage/${image}`;
};

const productAvailable = (product) => {
    if (!product.inventory) {
        return false;
    }

    return (
        Number(product.inventory.quantity) -
        Number(product.inventory.reserved_quantity)
    ) > 0;
};

const addToCart = (product) => {
    if (
        !productAvailable(product) ||
        addingProduct.value === product.id
    ) {
        return;
    }

    addingProduct.value = product.id;

    router.post(
        route('cart.store'),
        {
            product_id: product.id,
            quantity: 1,
        },
        {
            preserveScroll: true,

            onFinish: () => {
                addingProduct.value = null;
            },
        }
    );
};
</script>

<template>
    <CustomerLayout>
        <div class="min-h-screen bg-slate-50 dark:bg-slate-950">

            <!-- Header -->

            <section
                class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8"
                >
                    <div class="max-w-3xl">
                        <p
                            class="text-sm font-bold uppercase tracking-[0.2em] text-green-600"
                        >
                            Go Pharmacy
                        </p>

                        <h1
                            class="mt-3 text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl dark:text-white"
                        >
                            Shop healthcare
                            <span class="text-green-600">
                                with confidence.
                            </span>
                        </h1>

                        <p
                            class="mt-4 max-w-2xl text-base leading-7 text-slate-600 dark:text-slate-300"
                        >
                            Browse medicines, wellness products and
                            healthcare essentials from Go Pharmacy.
                        </p>
                    </div>

                    <form
                        class="mt-8"
                        @submit.prevent="applyFilters"
                    >
                        <div class="relative max-w-3xl">
                            <input
                                v-model="search"
                                type="search"
                                placeholder="Search medicines, brands, products..."
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 pr-28 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />

                            <button
                                type="submit"
                                class="absolute right-2 top-2 rounded-xl bg-green-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-green-700"
                            >
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Content -->

            <section
                class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
            >
                <button
                    type="button"
                    class="mb-6 flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 md:hidden dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                    @click="showFilters = !showFilters"
                >
                    {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                </button>

                <div class="grid gap-8 md:grid-cols-[240px_1fr]">

                    <!-- Filters -->

                    <aside
                        :class="[
                            'h-fit rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900',
                            showFilters
                                ? 'block'
                                : 'hidden md:block',
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <h2
                                class="font-bold text-slate-950 dark:text-white"
                            >
                                Filters
                            </h2>

                            <button
                                type="button"
                                class="text-xs font-semibold text-green-600"
                                @click="clearFilters"
                            >
                                Clear
                            </button>
                        </div>

                        <div class="mt-6">
                            <label
                                class="text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Category
                            </label>

                            <select
                                v-model="category"
                                @change="applyFilters"
                                class="mt-3 w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm outline-none focus:border-green-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            >
                                <option value="">
                                    All categories
                                </option>

                                <option
                                    v-for="item in categories"
                                    :key="item.id"
                                    :value="item.slug"
                                >
                                    {{ item.name }}
                                </option>
                            </select>
                        </div>

                        <div class="mt-6">
                            <label
                                class="text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Prescription
                            </label>

                            <select
                                v-model="prescription"
                                @change="applyFilters"
                                class="mt-3 w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm outline-none focus:border-green-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            >
                                <option value="">
                                    All products
                                </option>

                                <option value="yes">
                                    Prescription required
                                </option>

                                <option value="no">
                                    No prescription required
                                </option>
                            </select>
                        </div>

                        <div class="mt-6">
                            <label
                                class="text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Sort by
                            </label>

                            <select
                                v-model="sort"
                                @change="applyFilters"
                                class="mt-3 w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm outline-none focus:border-green-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            >
                                <option value="">
                                    Recommended
                                </option>

                                <option value="newest">
                                    Newest
                                </option>

                                <option value="name">
                                    Name
                                </option>

                                <option value="price_low">
                                    Price: Low to High
                                </option>

                                <option value="price_high">
                                    Price: High to Low
                                </option>
                            </select>
                        </div>
                    </aside>

                    <!-- Products -->

                    <div>
                        <div
                            class="mb-6 flex items-center justify-between"
                        >
                            <p class="text-sm text-slate-500">
                                Showing
                                <span
                                    class="font-bold text-slate-900 dark:text-white"
                                >
                                    {{ productCount }}
                                </span>
                                products
                            </p>
                        </div>

                        <div
                            v-if="products.data.length === 0"
                            class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-20 text-center dark:border-slate-700 dark:bg-slate-900"
                        >
                            <h2
                                class="text-xl font-bold text-slate-900 dark:text-white"
                            >
                                No products found
                            </h2>

                            <p class="mt-2 text-sm text-slate-500">
                                Try changing your search or filters.
                            </p>

                            <button
                                type="button"
                                class="mt-6 rounded-xl bg-green-600 px-5 py-3 text-sm font-bold text-white"
                                @click="clearFilters"
                            >
                                Clear filters
                            </button>
                        </div>

                        <div
                            v-else
                            class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <article
                                v-for="product in products.data"
                                :key="product.id"
                                class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition duration-300 hover:-translate-y-1 hover:border-green-200 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
                            >
                                <div
                                    class="relative flex h-56 items-center justify-center overflow-hidden bg-slate-100 dark:bg-slate-950"
                                >
                                    <img
                                        v-if="imageUrl(product.image)"
                                        :src="imageUrl(product.image)"
                                        :alt="product.name"
                                        class="h-full w-full object-contain p-6 transition duration-500 group-hover:scale-105"
                                    />

                                    <div
                                        v-else
                                        class="text-center text-slate-400"
                                    >
                                        <div
                                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-2xl shadow-sm dark:bg-slate-900"
                                        >
                                            💊
                                        </div>

                                        <p class="mt-3 text-xs">
                                            Go Pharmacy
                                        </p>
                                    </div>

                                    <span
                                        v-if="product.requires_prescription"
                                        class="absolute left-3 top-3 rounded-full bg-amber-100 px-3 py-1 text-[11px] font-bold text-amber-700"
                                    >
                                        Prescription
                                    </span>

                                    <span
                                        v-if="product.is_featured"
                                        class="absolute right-3 top-3 rounded-full bg-green-600 px-3 py-1 text-[11px] font-bold text-white"
                                    >
                                        Featured
                                    </span>
                                </div>

                                <div class="p-5">
                                    <p
                                        v-if="product.brand"
                                        class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                    >
                                        {{ product.brand }}
                                    </p>

                                    <h3
                                        class="mt-1 line-clamp-2 text-base font-bold text-slate-950 dark:text-white"
                                    >
                                        {{ product.name }}
                                    </h3>

                                    <p
                                        v-if="product.generic_name"
                                        class="mt-1 text-xs text-slate-500"
                                    >
                                        {{ product.generic_name }}
                                    </p>

                                    <div
                                        class="mt-4 flex items-end justify-between gap-3"
                                    >
                                        <div>
                                            <p
                                                class="text-lg font-extrabold text-slate-950 dark:text-white"
                                            >
                                                {{ formatPrice(product.price) }}
                                            </p>

                                            <p
                                                v-if="productAvailable(product)"
                                                class="mt-1 text-xs font-medium text-green-600"
                                            >
                                                In stock
                                            </p>

                                            <p
                                                v-else
                                                class="mt-1 text-xs font-medium text-red-500"
                                            >
                                                Currently unavailable
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            :disabled="
                                                !productAvailable(product) ||
                                                addingProduct === product.id
                                            "
                                            class="rounded-xl bg-slate-950 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-green-600 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400 dark:bg-white dark:text-slate-950 dark:hover:bg-green-500 dark:hover:text-white"
                                            @click="addToCart(product)"
                                        >
                                            {{
                                                addingProduct === product.id
                                                    ? 'Adding...'
                                                    : 'Add to cart'
                                            }}
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <!-- Pagination -->

                        <div
                            v-if="products.links?.length > 3"
                            class="mt-10 flex flex-wrap justify-center gap-2"
                        >
                            <template
                                v-for="(link, index) in products.links"
                                :key="index"
                            >
                                <button
                                    v-if="link.url"
                                    type="button"
                                    class="rounded-lg px-4 py-2 text-sm font-semibold transition"
                                    :class="
                                        link.active
                                            ? 'bg-green-600 text-white'
                                            : 'border border-slate-200 bg-white text-slate-700 hover:border-green-300 hover:text-green-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'
                                    "
                                    @click="router.visit(link.url)"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </CustomerLayout>
</template>