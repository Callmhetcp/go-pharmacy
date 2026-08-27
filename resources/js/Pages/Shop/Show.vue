<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },

    relatedProducts: {
        type: Array,
        default: () => [],
    },
});

const quantity = ref(1);
const addingToCart = ref(false);

/*
|--------------------------------------------------------------------------
| Inventory
|--------------------------------------------------------------------------
*/

const inventory = computed(() => props.product.inventory);

const availableQuantity = computed(() => {
    if (!inventory.value) {
        return 0;
    }

    return Math.max(
        0,
        Number(inventory.value.quantity ?? 0) -
            Number(inventory.value.reserved_quantity ?? 0)
    );
});

const isInStock = computed(() => availableQuantity.value > 0);

const isLowStock = computed(() => {
    if (!inventory.value) {
        return false;
    }

    return (
        availableQuantity.value <=
        Number(inventory.value.minimum_stock ?? 0)
    );
});

/*
|--------------------------------------------------------------------------
| Product
|--------------------------------------------------------------------------
*/

const formattedPrice = computed(() => {
    return Number(props.product.price ?? 0).toLocaleString('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
});

/*
|--------------------------------------------------------------------------
| Quantity
|--------------------------------------------------------------------------
*/

const decreaseQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

const increaseQuantity = () => {
    if (quantity.value < availableQuantity.value) {
        quantity.value++;
    }
};

/*
|--------------------------------------------------------------------------
| Add To Cart
|--------------------------------------------------------------------------
*/

const addToCart = () => {
    if (
        !props.product.is_active ||
        !isInStock.value ||
        addingToCart.value
    ) {
        return;
    }

    addingToCart.value = true;

    router.post(
        route('cart.store'),
        {
            product_id: props.product.id,
            quantity: quantity.value,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                quantity.value = 1;
            },

            onFinish: () => {
                addingToCart.value = false;
            },
        }
    );
};
</script>

<template>
    <CustomerLayout>
        <Head :title="product.name" />

        <main class="min-h-screen bg-slate-50 dark:bg-slate-950">

            <!-- =====================================================
                 BREADCRUMB
            ====================================================== -->
            <div
                class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                    <div class="flex flex-wrap items-center gap-2 text-sm">
                        <Link
                            href="/"
                            class="text-slate-500 transition hover:text-green-600"
                        >
                            Home
                        </Link>

                        <span class="text-slate-300">/</span>

                        <Link
                            href="/shop"
                            class="text-slate-500 transition hover:text-green-600"
                        >
                            Shop
                        </Link>

                        <span class="text-slate-300">/</span>

                        <span
                            class="font-medium text-slate-900 dark:text-white"
                        >
                            {{ product.name }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- =====================================================
                 PRODUCT
            ====================================================== -->
            <section
                class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-16"
            >
                <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">

                    <!-- =================================================
                         PRODUCT IMAGE
                    ================================================== -->
                    <div>
                        <div
                            class="relative aspect-square overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                        >
                            <img
                                v-if="product.image"
                                :src="`/storage/${product.image}`"
                                :alt="product.name"
                                class="h-full w-full object-cover"
                            />

                            <div
                                v-else
                                class="flex h-full items-center justify-center bg-slate-100 dark:bg-slate-800"
                            >
                                <div class="text-center">
                                    <svg
                                        class="mx-auto h-16 w-16 text-slate-300 dark:text-slate-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>

                                    <p
                                        class="mt-3 text-sm text-slate-400"
                                    >
                                        Product image unavailable
                                    </p>
                                </div>
                            </div>

                            <!-- Prescription Badge -->
                            <div
                                v-if="product.requires_prescription"
                                class="absolute left-5 top-5 rounded-full bg-amber-100 px-4 py-2 text-xs font-bold uppercase tracking-wide text-amber-800"
                            >
                                Prescription Required
                            </div>
                        </div>
                    </div>

                    <!-- =================================================
                         PRODUCT INFORMATION
                    ================================================== -->
                    <div class="flex flex-col justify-center">

                        <!-- Category -->
                        <Link
                            v-if="product.category"
                            :href="`/shop?category=${product.category.slug}`"
                            class="inline-flex w-fit text-sm font-bold uppercase tracking-[0.18em] text-green-600"
                        >
                            {{ product.category.name }}
                        </Link>

                        <!-- Product Name -->
                        <h1
                            class="mt-4 text-4xl font-black tracking-tight text-slate-950 sm:text-5xl dark:text-white"
                        >
                            {{ product.name }}
                        </h1>

                        <!-- Brand -->
                        <p
                            v-if="product.brand"
                            class="mt-4 text-lg text-slate-500 dark:text-slate-400"
                        >
                            {{ product.brand }}
                        </p>

                        <!-- Generic Name -->
                        <div
                            v-if="product.generic_name"
                            class="mt-6"
                        >
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-slate-400"
                            >
                                Generic Name
                            </p>

                            <p
                                class="mt-1 text-slate-700 dark:text-slate-300"
                            >
                                {{ product.generic_name }}
                            </p>
                        </div>

                        <!-- Price -->
                        <div class="mt-8">
                            <span
                                class="text-4xl font-black text-slate-950 dark:text-white"
                            >
                                ₦{{ formattedPrice }}
                            </span>
                        </div>

                        <!-- =================================================
                             PRODUCT DETAILS
                        ================================================== -->
                        <div
                            v-if="product.strength || product.dosage_form"
                            class="mt-8 grid grid-cols-2 gap-4"
                        >
                            <!-- Strength -->
                            <div
                                v-if="product.strength"
                                class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900"
                            >
                                <p
                                    class="text-xs font-bold uppercase tracking-wider text-slate-400"
                                >
                                    Strength
                                </p>

                                <p
                                    class="mt-1 font-semibold text-slate-900 dark:text-white"
                                >
                                    {{ product.strength }}
                                </p>
                            </div>

                            <!-- Dosage Form -->
                            <div
                                v-if="product.dosage_form"
                                class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900"
                            >
                                <p
                                    class="text-xs font-bold uppercase tracking-wider text-slate-400"
                                >
                                    Dosage Form
                                </p>

                                <p
                                    class="mt-1 font-semibold text-slate-900 dark:text-white"
                                >
                                    {{ product.dosage_form }}
                                </p>
                            </div>
                        </div>

                        <!-- =================================================
                             DESCRIPTION
                        ================================================== -->
                        <div
                            v-if="product.description"
                            class="mt-8 border-t border-slate-200 pt-8 dark:border-slate-800"
                        >
                            <h2
                                class="font-bold text-slate-900 dark:text-white"
                            >
                                About this product
                            </h2>

                            <p
                                class="mt-3 leading-7 text-slate-600 dark:text-slate-400"
                            >
                                {{ product.description }}
                            </p>
                        </div>

                        <!-- =================================================
                             STOCK STATUS
                        ================================================== -->
                        <div class="mt-8">

                            <!-- In Stock -->
                            <div
                                v-if="isInStock && product.is_active"
                                class="flex flex-wrap items-center gap-2"
                            >
                                <span
                                    class="h-2.5 w-2.5 rounded-full bg-green-500"
                                ></span>

                                <span
                                    class="text-sm font-semibold text-green-700 dark:text-green-400"
                                >
                                    In stock
                                </span>

                                <span
                                    v-if="isLowStock"
                                    class="ml-2 text-sm text-amber-600 dark:text-amber-400"
                                >
                                    Limited availability
                                </span>
                            </div>

                            <!-- Out Of Stock -->
                            <div
                                v-else-if="product.is_active && !isInStock"
                                class="flex items-center gap-2 text-red-500"
                            >
                                <span
                                    class="h-3 w-3 rounded-full bg-red-500"
                                ></span>

                                <span class="font-semibold">
                                    Out of stock
                                </span>
                            </div>

                            <!-- Inactive -->
                            <div
                                v-else
                                class="flex items-center gap-2 text-red-500"
                            >
                                <span
                                    class="h-3 w-3 rounded-full bg-red-500"
                                ></span>

                                <span class="font-semibold">
                                    Currently unavailable
                                </span>
                            </div>
                        </div>

                        <!-- =================================================
                             PURCHASE
                        ================================================== -->
                        <div class="mt-8 flex flex-col gap-4 sm:flex-row">

                            <!-- Quantity -->
                            <div
                                v-if="isInStock && product.is_active"
                                class="flex h-14 shrink-0 items-center rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900"
                            >
                                <!-- Decrease -->
                                <button
                                    type="button"
                                    class="flex h-full w-12 items-center justify-center text-xl font-bold text-slate-500 transition hover:text-green-600 disabled:cursor-not-allowed disabled:opacity-40"
                                    @click="decreaseQuantity"
                                    :disabled="
                                        addingToCart ||
                                        quantity <= 1
                                    "
                                >
                                    −
                                </button>

                                <!-- Quantity -->
                                <span
                                    class="w-10 text-center font-bold text-slate-900 dark:text-white"
                                >
                                    {{ quantity }}
                                </span>

                                <!-- Increase -->
                                <button
                                    type="button"
                                    class="flex h-full w-12 items-center justify-center text-xl font-bold text-slate-500 transition hover:text-green-600 disabled:cursor-not-allowed disabled:opacity-40"
                                    @click="increaseQuantity"
                                    :disabled="
                                        addingToCart ||
                                        quantity >= availableQuantity
                                    "
                                >
                                    +
                                </button>
                            </div>

                            <!-- Add To Cart -->
                            <button
                                type="button"
                                :disabled="
                                    !product.is_active ||
                                    !isInStock ||
                                    addingToCart
                                "
                                @click="addToCart"
                                class="w-full rounded-xl px-6 py-4 text-base font-bold transition-all duration-200 sm:flex-1"
                                :class="
                                    product.is_active &&
                                    isInStock &&
                                    !addingToCart
                                        ? 'bg-green-600 text-white hover:bg-green-700 active:scale-[0.98]'
                                        : 'cursor-not-allowed bg-slate-300 text-white'
                                "
                            >
                                <span v-if="addingToCart">
                                    Adding to Cart...
                                </span>

                                <span
                                    v-else-if="!product.is_active"
                                >
                                    Currently Unavailable
                                </span>

                                <span
                                    v-else-if="!isInStock"
                                >
                                    Out of Stock
                                </span>

                                <span v-else>
                                    Add to Cart
                                </span>
                            </button>
                        </div>

                        <!-- =================================================
                             PRESCRIPTION NOTICE
                        ================================================== -->
                        <div
                            v-if="product.requires_prescription"
                            class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900/50 dark:bg-amber-950/20"
                        >
                            <div class="flex gap-3">
                                <svg
                                    class="mt-0.5 h-5 w-5 shrink-0 text-amber-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"
                                    />
                                </svg>

                                <div>
                                    <p
                                        class="font-bold text-amber-900 dark:text-amber-300"
                                    >
                                        Prescription required
                                    </p>

                                    <p
                                        class="mt-1 text-sm leading-6 text-amber-800 dark:text-amber-400"
                                    >
                                        A valid prescription may be required
                                        before this product can be dispensed.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 RELATED PRODUCTS
            ====================================================== -->
            <section
                v-if="relatedProducts.length"
                class="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8"
                >
                    <div class="mb-8">
                        <p
                            class="text-sm font-bold uppercase tracking-[0.18em] text-green-600"
                        >
                            You may also like
                        </p>

                        <h2
                            class="mt-2 text-3xl font-black text-slate-950 dark:text-white"
                        >
                            More from this category
                        </h2>
                    </div>

                    <div
                        class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <Link
                            v-for="item in relatedProducts"
                            :key="item.id"
                            :href="`/shop/${item.slug}`"
                            class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-950"
                        >
                            <!-- Image -->
                            <div
                                class="aspect-square bg-slate-100 dark:bg-slate-800"
                            >
                                <img
                                    v-if="item.image"
                                    :src="`/storage/${item.image}`"
                                    :alt="item.name"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                />

                                <div
                                    v-else
                                    class="flex h-full items-center justify-center text-sm text-slate-400"
                                >
                                    No image
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="p-5">
                                <p
                                    class="text-xs font-semibold uppercase tracking-wide text-green-600"
                                >
                                    {{ item.category?.name }}
                                </p>

                                <h3
                                    class="mt-2 font-bold text-slate-900 dark:text-white"
                                >
                                    {{ item.name }}
                                </h3>

                                <p
                                    class="mt-3 font-black text-slate-950 dark:text-white"
                                >
                                    ₦{{ Number(item.price ?? 0).toLocaleString('en-NG') }}
                                </p>
                            </div>
                        </Link>
                    </div>
                </div>
            </section>
        </main>
    </CustomerLayout>
</template>