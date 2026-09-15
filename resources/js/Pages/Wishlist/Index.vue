<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const props = defineProps({
    wishlist: {
        type: Array,
        default: () => [],
    },
});

/*
|--------------------------------------------------------------------------
| Formatting
|--------------------------------------------------------------------------
*/

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        maximumFractionDigits: 0,
    }).format(Number(price ?? 0));
};

/*
|--------------------------------------------------------------------------
| Product image
|--------------------------------------------------------------------------
*/

const imageUrl = (image) => {
    if (!image) {
        return null;
    }

    if (
        image.startsWith('http://') ||
        image.startsWith('https://') ||
        image.startsWith('/')
    ) {
        return image;
    }

    return `/storage/${image}`;
};

/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

const csrfToken = () => {
    return document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');
};

/*
|--------------------------------------------------------------------------
| Wishlist actions
|--------------------------------------------------------------------------
*/

const removeItem = async (item) => {
    try {
        const response = await fetch(
            route('wishlist.destroy', item.product.id),
            {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }
        );

        if (!response.ok) {
            return;
        }

        router.reload({
            only: ['wishlist'],
            preserveScroll: true,
        });
    } catch (error) {
        console.error(
            'Failed to remove wishlist item.',
            error
        );
    }
};

const clearWishlist = async () => {
    try {
        const response = await fetch(
            route('wishlist.clear'),
            {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }
        );

        if (!response.ok) {
            return;
        }

        router.reload({
            only: ['wishlist'],
            preserveScroll: true,
        });
    } catch (error) {
        console.error(
            'Failed to clear wishlist.',
            error
        );
    }
};

/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
*/

const addToCart = (item) => {
    if (!item.product?.is_active) {
        return;
    }

    router.post(
        route('cart.store'),
        {
            product_id: item.product.id,
            quantity: 1,
        },
        {
            preserveScroll: true,
        }
    );
};
</script>

<template>
    <Head title="My Wishlist" />

    <CustomerLayout>
        <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
            <!-- Header -->
            <section
                class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8"
                >
                    <p
                        class="text-sm font-bold uppercase tracking-[0.2em] text-green-600"
                    >
                        Go Pharmacy
                    </p>

                    <h1
                        class="mt-2 text-3xl font-extrabold text-slate-950 dark:text-white"
                    >
                        My Wishlist
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        {{ wishlist.length }}
                        {{
                            wishlist.length === 1
                                ? 'product'
                                : 'products'
                        }}
                        saved for later.
                    </p>
                </div>
            </section>

            <!-- Empty Wishlist -->
            <section
                v-if="wishlist.length === 0"
                class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8"
            >
                <div
                    class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-20 text-center dark:border-slate-700 dark:bg-slate-900"
                >
                    <div
                        class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-green-50 text-3xl dark:bg-green-950"
                    >
                        ♡
                    </div>

                    <h2
                        class="mt-6 text-2xl font-extrabold text-slate-950 dark:text-white"
                    >
                        Your wishlist is empty
                    </h2>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500"
                    >
                        Save medicines and healthcare products
                        you want to come back to later.
                    </p>

                    <Link
                        :href="route('shop.index')"
                        class="mt-7 inline-flex rounded-xl bg-green-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-green-700"
                    >
                        Browse Products
                    </Link>
                </div>
            </section>

            <!-- Wishlist -->
            <section
                v-else
                class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
            >
                <!-- Actions -->
                <div
                    class="mb-6 flex items-center justify-between gap-4"
                >
                    <Link
                        :href="route('shop.index')"
                        class="text-sm font-bold text-green-600 hover:text-green-700"
                    >
                        ← Continue Shopping
                    </Link>

                    <button
                        type="button"
                        class="text-sm font-semibold text-red-500 transition hover:text-red-600"
                        @click="clearWishlist"
                    >
                        Clear Wishlist
                    </button>
                </div>

                <!-- Products -->
                <div
                    class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <article
                        v-for="item in wishlist"
                        :key="item.id"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:-translate-y-1 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900"
                    >
                        <!-- Product Image -->
                        <div
                            class="relative flex h-56 items-center justify-center overflow-hidden bg-slate-100 dark:bg-slate-950"
                        >
                            <img
                                v-if="imageUrl(item.product?.image)"
                                :src="
                                    imageUrl(item.product.image)
                                "
                                :alt="item.product?.name"
                                class="h-full w-full object-contain p-6"
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

                            <!-- Prescription Badge -->
                            <span
                                v-if="
                                    item.product
                                        ?.requires_prescription
                                "
                                class="absolute left-3 top-3 rounded-full bg-amber-100 px-3 py-1 text-[11px] font-bold text-amber-700"
                            >
                                Prescription
                            </span>

                            <!-- Remove Wishlist -->
                            <button
                                type="button"
                                aria-label="Remove from wishlist"
                                class="absolute right-3 top-3 flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white/95 text-red-500 shadow-sm backdrop-blur transition hover:bg-red-50 dark:border-slate-700 dark:bg-slate-900/95 dark:hover:bg-slate-800"
                                @click="removeItem(item)"
                            >
                                <svg
                                    class="h-5 w-5 fill-current"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"
                                    />
                                </svg>
                            </button>
                        </div>

                        <!-- Product Details -->
                        <div class="p-5">
                            <h2
                                class="font-bold text-slate-950 dark:text-white"
                            >
                                {{ item.product?.name }}
                            </h2>

                            <p
                                v-if="item.product?.sku"
                                class="mt-1 text-xs text-slate-500"
                            >
                                SKU: {{ item.product.sku }}
                            </p>

                            <p
                                v-if="item.product?.generic_name"
                                class="mt-2 text-xs text-slate-500"
                            >
                                {{ item.product.generic_name }}
                            </p>

                            <p
                                class="mt-3 text-lg font-extrabold text-green-600"
                            >
                                {{
                                    formatPrice(
                                        item.product?.sale_price ??
                                            item.product?.price
                                    )
                                }}
                            </p>

                            <!-- Actions -->
                            <div class="mt-5 flex gap-3">
                                <button
                                    type="button"
                                    :disabled="
                                        !item.product?.is_active
                                    "
                                    class="flex-1 rounded-xl bg-slate-950 px-4 py-3 text-xs font-bold text-white transition hover:bg-green-600 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400 dark:bg-white dark:text-slate-950 dark:hover:bg-green-500 dark:hover:text-white"
                                    @click="addToCart(item)"
                                >
                                    {{
                                        item.product?.is_active
                                            ? 'Add to cart'
                                            : 'Unavailable'
                                    }}
                                </button>

                                <Link
                                    v-if="item.product?.slug"
                                    :href="
                                        route(
                                            'shop.show',
                                            item.product.slug
                                        )
                                    "
                                    class="flex items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-xs font-bold text-slate-700 transition hover:border-green-500 hover:text-green-600 dark:border-slate-700 dark:text-slate-300"
                                >
                                    View
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>
            </section>
        </div>
    </CustomerLayout>
</template>