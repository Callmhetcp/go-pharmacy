<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineProps({
    category: {
        type: Object,
        required: true,
    },

    products: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <CustomerLayout>
        <main
            class="min-h-screen bg-white py-12 text-slate-900 dark:bg-slate-950 dark:text-white"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Header -->
                <div>
                    <a
                        href="/categories"
                        class="text-sm font-semibold text-green-600 hover:text-green-700"
                    >
                        ← All categories
                    </a>

                    <p
                        class="mt-6 text-sm font-bold uppercase tracking-[0.2em] text-green-600"
                    >
                        Category
                    </p>

                    <h1 class="mt-2 text-3xl font-bold sm:text-4xl">
                        {{ category.name }}
                    </h1>

                    <p
                        v-if="category.description"
                        class="mt-3 max-w-2xl text-slate-500 dark:text-slate-400"
                    >
                        {{ category.description }}
                    </p>
                </div>

                <!-- Products -->
                <div
                    v-if="products.data.length"
                    class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <a
                        v-for="product in products.data"
                        :key="product.id"
                        :href="`/shop/${product.slug}`"
                        class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:-translate-y-1 hover:border-green-300 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900"
                    >
                        <!-- Image -->
                        <div
                            class="aspect-square overflow-hidden bg-slate-100 dark:bg-slate-800"
                        >
                            <img
                                v-if="product.image"
                                :src="`/storage/${product.image}`"
                                :alt="product.name"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                            />

                            <div
                                v-else
                                class="flex h-full items-center justify-center text-sm text-slate-400"
                            >
                                No image
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-5">

                            <p
                                v-if="product.brand"
                                class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                {{ product.brand }}
                            </p>

                            <h2
                                class="mt-1 font-bold text-slate-900 group-hover:text-green-600 dark:text-white"
                            >
                                {{ product.name }}
                            </h2>

                            <p
                                v-if="product.strength"
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{ product.strength }}
                            </p>

                            <div class="mt-4">
                                <span class="text-lg font-bold text-green-600">
                                    ₦{{ Number(product.price).toLocaleString() }}
                                </span>
                            </div>

                        </div>
                    </a>
                </div>

                <!-- Empty -->
                <div
                    v-else
                    class="mt-10 rounded-2xl border border-slate-200 p-12 text-center dark:border-slate-800"
                >
                    <h2 class="text-lg font-bold">
                        No products in this category
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Products will appear here when they are added to this category.
                    </p>
                </div>

                <!-- Pagination -->
                <div
                    v-if="products.links && products.links.length > 3"
                    class="mt-10 flex flex-wrap justify-center gap-2"
                >
                    <a
                        v-for="link in products.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        class="rounded-lg border px-3 py-2 text-sm"
                        :class="{
                            'border-green-600 bg-green-600 text-white': link.active,
                            'border-slate-200 dark:border-slate-800': !link.active,
                            'pointer-events-none opacity-40': !link.url,
                        }"
                        v-html="link.label"
                    />
                </div>

            </div>
        </main>
    </CustomerLayout>
</template>