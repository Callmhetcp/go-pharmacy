<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const formatMoney = (value) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
    }).format(Number(value ?? 0));
};

const imageUrl = (image) => {
    if (!image) {
        return null;
    }

    return image.startsWith('http')
        ? image
        : `/storage/${image}`;
};
</script>

<template>
    <CustomerLayout>
        <Head title="Order Payment" />

        <div
            class="min-h-screen bg-slate-50 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
        >
            <!-- Page Header -->
            <section
                class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.18em] text-green-600 dark:text-green-400"
                            >
                                Order Created
                            </p>

                            <h1
                                class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white"
                            >
                                Your order is awaiting payment
                            </h1>

                            <p
                                class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Order {{ order.order_number }}
                            </p>
                        </div>

                        <Link
                            :href="route('orders.show', order.id)"
                            class="inline-flex w-fit items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-green-300 hover:text-green-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-green-700 dark:hover:text-green-400"
                        >
                            View Order
                        </Link>
                    </div>
                </div>
            </section>

            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
                <div class="grid gap-8 lg:grid-cols-[1fr_380px]">
                    <!-- Payment Status -->
                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8"
                    >
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400"
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
                                    stroke-width="1.8"
                                    d="M12 8v4m0 4h.01M5.1 19h13.8c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.37 16c-.77 1.33.19 3 1.73 3Z"
                                />
                            </svg>
                        </div>

                        <h2
                            class="mt-6 text-2xl font-extrabold text-slate-950 dark:text-white"
                        >
                            Payment is not available yet
                        </h2>

                        <p
                            class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-400"
                        >
                            Your order has been successfully created and is currently
                            awaiting payment. Online payment will be enabled once the
                            payment provider for Go Pharmacy has been approved and
                            configured.
                        </p>

                        <div
                            class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900/50 dark:bg-amber-950/30"
                        >
                            <p
                                class="text-sm font-bold text-amber-900 dark:text-amber-300"
                            >
                                No payment has been taken.
                            </p>

                            <p
                                class="mt-1 text-sm leading-6 text-amber-800 dark:text-amber-400"
                            >
                                You have not been charged for this order. Payment
                                processing will be connected after the appropriate
                                payment provider is selected and configured.
                            </p>
                        </div>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <Link
                                :href="route('orders.show', order.id)"
                                class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-green-700"
                            >
                                View My Order
                            </Link>

                            <Link
                                :href="route('shop.index')"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-green-300 hover:text-green-600 dark:border-slate-700 dark:text-slate-200"
                            >
                                Continue Shopping
                            </Link>
                        </div>
                    </section>

                    <!-- Order Summary -->
                    <aside class="h-fit lg:sticky lg:top-24">
                        <div
                            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                        >
                            <h2
                                class="text-lg font-bold text-slate-950 dark:text-white"
                            >
                                Order Summary
                            </h2>

                            <!-- Products -->
                            <div class="mt-6 space-y-4">
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="flex gap-3"
                                >
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800"
                                    >
                                        <img
                                            v-if="imageUrl(item.product?.image)"
                                            :src="imageUrl(item.product.image)"
                                            :alt="item.product_name"
                                            class="h-full w-full object-cover"
                                        />

                                        <svg
                                            v-else
                                            class="h-5 w-5 text-slate-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.8"
                                                d="M12 3 4 7v10l8 4 8-4V7l-8-4Z"
                                            />
                                        </svg>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="line-clamp-2 text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            {{ item.product_name }}
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            Qty: {{ item.quantity }}
                                            {{ item.selling_unit }}
                                        </p>
                                    </div>

                                    <p
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ formatMoney(item.subtotal) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Totals -->
                            <div
                                class="mt-6 space-y-3 border-t border-slate-100 pt-5 dark:border-slate-800"
                            >
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500 dark:text-slate-400">
                                        Subtotal
                                    </span>

                                    <span
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ formatMoney(order.subtotal) }}
                                    </span>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500 dark:text-slate-400">
                                        Delivery
                                    </span>

                                    <span
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ formatMoney(order.delivery_fee) }}
                                    </span>
                                </div>

                                <div
                                    v-if="Number(order.discount) > 0"
                                    class="flex justify-between text-sm"
                                >
                                    <span class="text-slate-500 dark:text-slate-400">
                                        Discount
                                    </span>

                                    <span
                                        class="font-semibold text-green-600 dark:text-green-400"
                                    >
                                        -{{ formatMoney(order.discount) }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-800"
                                >
                                    <span
                                        class="text-base font-bold text-slate-950 dark:text-white"
                                    >
                                        Total
                                    </span>

                                    <span
                                        class="text-xl font-extrabold text-green-600 dark:text-green-400"
                                    >
                                        {{ formatMoney(order.total) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </main>
        </div>
    </CustomerLayout>
</template>