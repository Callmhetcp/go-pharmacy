<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});

const products = computed(() => props.products);

const formatPrice = (price) => {
    return Number(price ?? 0).toLocaleString('en-NG', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });
};

const availableQuantity = (product) => {
    const inventory = product.inventory;

    if (!inventory) {
        return 0;
    }

    return Math.max(
        0,
        Number(inventory.quantity ?? 0) -
            Number(inventory.reserved_quantity ?? 0),
    );
};

const isInStock = (product) => {
    return availableQuantity(product) > 0;
};

const isLowStock = (product) => {
    const inventory = product.inventory;

    if (!inventory) {
        return false;
    }

    const quantity = availableQuantity(product);

    return (
        quantity > 0 &&
        quantity <= Number(inventory.minimum_stock ?? product.minimum_stock ?? 0)
    );
};

const stockLabel = (product) => {
    if (!isInStock(product)) {
        return 'Out of Stock';
    }

    if (isLowStock(product)) {
        return `Only ${availableQuantity(product)} left`;
    }

    return 'In Stock';
};

const stockClass = (product) => {
    if (!isInStock(product)) {
        return 'text-red-600 dark:text-red-400';
    }

    if (isLowStock(product)) {
        return 'text-amber-600 dark:text-amber-400';
    }

    return 'text-green-600 dark:text-green-400';
};

const stockDotClass = (product) => {
    if (!isInStock(product)) {
        return 'bg-red-500';
    }

    if (isLowStock(product)) {
        return 'bg-amber-500';
    }

    return 'bg-green-500';
};

const productImage = (product) => {
    if (!product.image) {
        return null;
    }

    if (
        product.image.startsWith('http://') ||
        product.image.startsWith('https://') ||
        product.image.startsWith('/')
    ) {
        return product.image;
    }

    return `/storage/${product.image}`;
};

const addToCart = (product) => {
    if (!isInStock(product)) {
        return;
    }

    router.post(
        route('cart.store'),
        {
            product_id: product.id,
            quantity: 1,
        },
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <section
        class="bg-slate-50 py-20 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- =====================================================
                 SECTION HEADER
            ====================================================== -->

            <div
                class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between"
            >
                <div class="max-w-2xl">
                    <p
                        class="text-sm font-bold uppercase tracking-[0.2em] text-green-600 dark:text-green-400"
                    >
                        Carefully selected for you
                    </p>

                    <h2
                        class="mt-3 text-3xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-4xl"
                    >
                        Featured products
                    </h2>

                    <p
                        class="mt-4 text-base leading-7 text-slate-600 dark:text-slate-400"
                    >
                        Explore some of our popular healthcare and wellness
                        essentials.
                    </p>
                </div>

                <Link
                    :href="route('shop.index')"
                    class="inline-flex shrink-0 items-center gap-2 text-sm font-bold text-green-600 transition hover:text-green-700 dark:text-green-400 dark:hover:text-green-300"
                >
                    Shop all products

                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M5 12h14m-5-5 5 5-5 5"
                        />
                    </svg>
                </Link>
            </div>

            <!-- =====================================================
                 EMPTY STATE
            ====================================================== -->

            <div
                v-if="products.length === 0"
                class="mt-10 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900"
            >
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-green-50 text-green-600 dark:bg-green-950 dark:text-green-400"
                >
                    <svg
                        class="h-8 w-8"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.7"
                            d="M20 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2ZM8 7V5a4 4 0 0 1 8 0v2"
                        />
                    </svg>
                </div>

                <h3
                    class="mt-5 text-lg font-bold text-slate-950 dark:text-white"
                >
                    No featured products yet
                </h3>

                <p
                    class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                >
                    Featured products will appear here once they are added
                    from the admin panel.
                </p>
            </div>

            <!-- =====================================================
                 PRODUCTS
            ====================================================== -->

            <div
                v-else
                class="mt-10 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4"
            >
                <article
                    v-for="product in products"
                    :key="product.id"
                    class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition duration-300 hover:-translate-y-1 hover:border-green-300 hover:shadow-xl hover:shadow-slate-300/20 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-green-700 dark:hover:shadow-black/30"
                >
                    <!-- =================================================
                         IMAGE
                    ================================================== -->

                    <div
                        class="relative flex h-64 items-center justify-center overflow-hidden bg-slate-100 dark:bg-slate-800"
                    >
                        <!-- Prescription Badge -->

                        <span
                            v-if="product.requires_prescription"
                            class="absolute left-4 top-4 z-10 rounded-full bg-amber-500 px-3 py-1.5 text-xs font-bold text-white"
                        >
                            Prescription
                        </span>

                        <!-- Wishlist -->

                        <button
                            type="button"
                            class="absolute right-4 top-4 z-10 flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-green-200 hover:bg-green-50 hover:text-green-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:border-green-700 dark:hover:bg-green-950 dark:hover:text-green-400"
                            :aria-label="`Add ${product.name} to wishlist`"
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
                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"
                                />
                            </svg>
                        </button>

                        <!-- Product Image -->

                        <Link
                            :href="
                                route(
                                    'shop.show',
                                    product.slug,
                                )
                            "
                            class="flex h-full w-full items-center justify-center"
                        >
                            <img
                                v-if="productImage(product)"
                                :src="productImage(product)"
                                :alt="product.name"
                                class="h-full w-full object-contain p-8 transition duration-500 group-hover:scale-105"
                            />

                            <!-- Image Placeholder -->

                            <div
                                v-else
                                class="flex h-36 w-36 items-center justify-center rounded-3xl bg-white shadow-sm dark:bg-slate-900"
                            >
                                <div
                                    class="flex h-16 w-16 items-center justify-center rounded-2xl bg-green-50 text-green-600 dark:bg-green-950 dark:text-green-400"
                                >
                                    <svg
                                        class="h-8 w-8"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.7"
                                            d="m14.5 4.5-10 10a3.54 3.54 0 0 0 5 5l10-10a3.54 3.54 0 0 0-5-5Z"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.7"
                                            d="m12 7 5 5"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </Link>

                        <!-- Quick View -->

                        <div
                            class="absolute inset-x-0 bottom-0 translate-y-full p-4 transition duration-300 group-hover:translate-y-0"
                        >
                            <Link
                                :href="
                                    route(
                                        'shop.show',
                                        product.slug,
                                    )
                                "
                                class="block rounded-xl bg-slate-950/90 py-3 text-center text-sm font-bold text-white backdrop-blur transition hover:bg-green-600"
                            >
                                Quick view
                            </Link>
                        </div>
                    </div>

                    <!-- =================================================
                         DETAILS
                    ================================================== -->

                    <div class="p-5">
                        <p
                            class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"
                        >
                            {{ product.category?.name ?? 'Healthcare' }}
                        </p>

                        <Link
                            :href="
                                route(
                                    'shop.show',
                                    product.slug,
                                )
                            "
                        >
                            <h3
                                class="mt-2 min-h-[3rem] text-base font-bold leading-6 text-slate-950 transition group-hover:text-green-600 dark:text-white dark:group-hover:text-green-400"
                            >
                                {{ product.name }}
                            </h3>
                        </Link>

                        <!-- Price -->

                        <div class="mt-4">
                            <span
                                class="text-xl font-extrabold tracking-tight text-slate-950 dark:text-white"
                            >
                                ₦{{ formatPrice(product.price) }}
                            </span>
                        </div>

                        <!-- Stock -->

                        <div
                            class="mt-3 flex items-center gap-2 text-xs font-semibold"
                            :class="stockClass(product)"
                        >
                            <span
                                class="h-2 w-2 rounded-full"
                                :class="stockDotClass(product)"
                            ></span>

                            {{ stockLabel(product) }}
                        </div>

                        <!-- Add To Cart -->

                        <button
                            type="button"
                            :disabled="!isInStock(product)"
                            @click="addToCart(product)"
                            class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold text-white transition active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 dark:disabled:bg-slate-700 dark:disabled:text-slate-400"
                            :class="
                                isInStock(product)
                                    ? 'bg-green-600 hover:bg-green-700'
                                    : ''
                            "
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
                                    d="M3 3h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 7H6"
                                />

                                <circle
                                    cx="10"
                                    cy="20"
                                    r="1.5"
                                />

                                <circle
                                    cx="18"
                                    cy="20"
                                    r="1.5"
                                />
                            </svg>

                            {{
                                isInStock(product)
                                    ? 'Add to cart'
                                    : 'Out of stock'
                            }}
                        </button>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>
