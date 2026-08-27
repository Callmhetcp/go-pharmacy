<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    product: {
        type: Object,
        required: true,
    },

    relatedProducts: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <header class="border-b bg-white">
            <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                <Link
                    href="/shop"
                    class="text-sm font-semibold text-green-600 hover:text-green-700"
                >
                    ← Back to Shop
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-2">
                <!-- Product Image -->
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                    <img
                        v-if="product.image"
                        :src="product.image_url ?? `/storage/${product.image}`"
                        :alt="product.name"
                        class="h-[500px] w-full object-cover"
                    />

                    <div
                        v-else
                        class="flex h-[500px] items-center justify-center bg-slate-100 text-slate-400"
                    >
                        No image available
                    </div>
                </div>

                <!-- Product Information -->
                <div class="flex flex-col justify-center">
                    <p
                        v-if="product.category"
                        class="text-sm font-semibold uppercase tracking-wide text-green-600"
                    >
                        {{ product.category.name }}
                    </p>

                    <h1 class="mt-2 text-3xl font-bold text-slate-950 sm:text-4xl">
                        {{ product.name }}
                    </h1>

                    <p
                        v-if="product.description"
                        class="mt-5 leading-7 text-slate-600"
                    >
                        {{ product.description }}
                    </p>

                    <div class="mt-6">
                        <span class="text-3xl font-bold text-green-600">
                            ₦{{ Number(product.price ?? 0).toLocaleString() }}
                        </span>
                    </div>

                    <button
                        type="button"
                        class="mt-8 w-full rounded-xl bg-green-600 px-6 py-4 text-sm font-bold text-white transition hover:bg-green-700 sm:w-auto"
                    >
                        Add to Cart
                    </button>
                </div>
            </div>

            <!-- Related Products -->
            <section
                v-if="relatedProducts.length"
                class="mt-16"
            >
                <h2 class="text-2xl font-bold text-slate-950">
                    Related Products
                </h2>

                <div
                    class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <Link
                        v-for="item in relatedProducts"
                        :key="item.id"
                        :href="`/shop/${item.slug ?? item.id}`"
                        class="overflow-hidden rounded-2xl bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md"
                    >
                        <img
                            v-if="item.image"
                            :src="
                                item.image_url ??
                                `/storage/${item.image}`
                            "
                            :alt="item.name"
                            class="h-48 w-full object-cover"
                        />

                        <div class="p-4">
                            <h3 class="font-semibold text-slate-900">
                                {{ item.name }}
                            </h3>

                            <p class="mt-2 font-bold text-green-600">
                                ₦{{
                                    Number(
                                        item.price ?? 0
                                    ).toLocaleString()
                                }}
                            </p>
                        </div>
                    </Link>
                </div>
            </section>
        </main>
    </div>
</template>