<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const props = defineProps({
    cart: {
        type: Array,
        default: () => [],
    },

    cartCount: {
        type: Number,
        default: 0,
    },

    cartSubtotal: {
        type: [Number, String],
        default: 0,
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
| Cart actions
|--------------------------------------------------------------------------
*/

const updateQuantity = (item, quantity) => {
    if (quantity < 1) {
        return;
    }

    router.patch(
        route('cart.update', item.product_id),
        {
            quantity,
        },
        {
            preserveScroll: true,
        }
    );
};

const removeItem = (item) => {
    router.delete(
        route('cart.destroy', item.product_id),
        {
            preserveScroll: true,
        }
    );
};

const clearCart = () => {
    router.delete(
        route('cart.clear'),
        {
            preserveScroll: true,
        }
    );
};

/*
|--------------------------------------------------------------------------
| Total
|--------------------------------------------------------------------------
*/

const total = computed(() => {
    return Number(props.cartSubtotal ?? 0);
});
</script>

<template>
    <Head title="Shopping Cart" />

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
                        Your Cart
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        {{ cartCount }}
                        {{ cartCount === 1 ? 'item' : 'items' }}
                        in your cart.
                    </p>
                </div>
            </section>

            <!-- Empty -->

            <section
                v-if="cart.length === 0"
                class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8"
            >
                <div
                    class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-20 text-center dark:border-slate-700 dark:bg-slate-900"
                >
                    <div
                        class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-green-50 text-3xl dark:bg-green-950"
                    >
                        🛒
                    </div>

                    <h2
                        class="mt-6 text-2xl font-extrabold text-slate-950 dark:text-white"
                    >
                        Your cart is empty
                    </h2>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500"
                    >
                        Browse our medicines, wellness products and
                        healthcare essentials and add what you need.
                    </p>

                    <Link
                        :href="route('shop.index')"
                        class="mt-7 inline-flex rounded-xl bg-green-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-green-700"
                    >
                        Continue Shopping
                    </Link>
                </div>
            </section>

            <!-- Cart -->

            <section
                v-else
                class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
            >
                <div
                    class="grid gap-8 lg:grid-cols-[1fr_360px]"
                >

                    <!-- Items -->

                    <div class="space-y-4">

                        <div
                            v-for="item in cart"
                            :key="item.product_id"
                            class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div
                                class="flex flex-col gap-5 sm:flex-row sm:items-center"
                            >

                                <!-- Image -->

                                <div
                                    class="flex h-28 w-28 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-950"
                                >
                                    <img
                                        v-if="imageUrl(item.image)"
                                        :src="imageUrl(item.image)"
                                        :alt="item.product_name"
                                        class="h-full w-full object-contain p-3"
                                    />

                                    <span
                                        v-else
                                        class="text-3xl"
                                    >
                                        💊
                                    </span>
                                </div>

                                <!-- Product -->

                                <div class="min-w-0 flex-1">
                                    <h2
                                        class="font-bold text-slate-950 dark:text-white"
                                    >
                                        {{ item.product_name }}
                                    </h2>

                                    <p
                                        v-if="item.product_sku"
                                        class="mt-1 text-xs text-slate-500"
                                    >
                                        SKU: {{ item.product_sku }}
                                    </p>

                                    <p
                                        class="mt-2 text-sm font-semibold text-green-600"
                                    >
                                        {{ formatPrice(item.unit_price) }}
                                    </p>
                                </div>

                                <!-- Quantity -->

                                <div
                                    class="flex w-fit items-center rounded-xl border border-slate-200 dark:border-slate-700"
                                >
                                    <button
                                        type="button"
                                        :disabled="item.quantity <= 1"
                                        class="px-4 py-2 text-lg font-bold text-slate-500 hover:text-green-600 disabled:cursor-not-allowed disabled:opacity-40"
                                        @click="
                                            updateQuantity(
                                                item,
                                                Number(item.quantity) - 1
                                            )
                                        "
                                    >
                                        −
                                    </button>

                                    <span
                                        class="min-w-10 text-center text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ item.quantity }}
                                    </span>

                                    <button
                                        type="button"
                                        class="px-4 py-2 text-lg font-bold text-slate-500 hover:text-green-600"
                                        @click="
                                            updateQuantity(
                                                item,
                                                Number(item.quantity) + 1
                                            )
                                        "
                                    >
                                        +
                                    </button>
                                </div>

                                <!-- Subtotal -->

                                <div
                                    class="text-left sm:min-w-28 sm:text-right"
                                >
                                    <p
                                        class="font-extrabold text-slate-950 dark:text-white"
                                    >
                                        {{ formatPrice(item.subtotal) }}
                                    </p>

                                    <button
                                        type="button"
                                        class="mt-2 text-xs font-semibold text-red-500 hover:text-red-600"
                                        @click="removeItem(item)"
                                    >
                                        Remove
                                    </button>
                                </div>

                            </div>
                        </div>

                        <!-- Cart actions -->

                        <div class="flex justify-between pt-2">
                            <Link
                                :href="route('shop.index')"
                                class="text-sm font-bold text-green-600 hover:text-green-700"
                            >
                                ← Continue Shopping
                            </Link>

                            <button
                                type="button"
                                class="text-sm font-semibold text-red-500 hover:text-red-600"
                                @click="clearCart"
                            >
                                Clear Cart
                            </button>
                        </div>

                    </div>

                    <!-- Summary -->

                    <aside
                        class="h-fit rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2
                            class="text-lg font-extrabold text-slate-950 dark:text-white"
                        >
                            Order Summary
                        </h2>

                        <div class="mt-6 space-y-4 text-sm">

                            <div class="flex justify-between">
                                <span class="text-slate-500">
                                    Items
                                </span>

                                <span
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    {{ cartCount }}
                                </span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-slate-500">
                                    Subtotal
                                </span>

                                <span
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    {{ formatPrice(total) }}
                                </span>
                            </div>

                            <div
                                class="border-t border-slate-200 pt-4 dark:border-slate-700"
                            >
                                <div class="flex justify-between">
                                    <span
                                        class="font-bold text-slate-950 dark:text-white"
                                    >
                                        Total
                                    </span>

                                    <span
                                        class="text-xl font-extrabold text-green-600"
                                    >
                                        {{ formatPrice(total) }}
                                    </span>
                                </div>
                            </div>

                        </div>

                        <!-- IMPORTANT: checkout.create -->

                        <Link
                            :href="route('checkout.create')"
                            class="mt-7 flex w-full items-center justify-center rounded-xl bg-green-600 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-green-700"
                        >
                            Proceed to Checkout
                        </Link>

                        <p
                            class="mt-4 text-center text-xs leading-5 text-slate-400"
                        >
                            Delivery fees and any applicable discounts
                            will be calculated at checkout.
                        </p>
                    </aside>

                </div>
            </section>

        </div>
    </CustomerLayout>
</template>