<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <CustomerLayout>
        <main
            class="min-h-screen bg-white text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
        >
            <!-- Header -->
            <section class="border-b border-slate-200 dark:border-slate-800">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                    <p
                        class="text-sm font-bold uppercase tracking-[0.2em] text-green-600 dark:text-green-400"
                    >
                        Explore our range
                    </p>

                    <h1
                        class="mt-3 text-4xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-5xl"
                    >
                        Shop by category
                    </h1>

                    <p
                        class="mt-4 max-w-2xl text-base leading-7 text-slate-600 dark:text-slate-400"
                    >
                        Find medicines, wellness products, personal care
                        essentials and more.
                    </p>
                </div>
            </section>

            <!-- Categories -->
            <section class="py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                    <div
                        v-if="categories.length"
                        class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <a
                            v-for="category in categories"
                            :key="category.id"
                            :href="`/categories/${category.slug}`"
                            class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition duration-300 hover:-translate-y-1 hover:border-green-300 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900 dark:hover:border-green-700"
                        >
                            <!-- Image -->
                            <div
                                v-if="category.image"
                                class="h-48 overflow-hidden bg-slate-100 dark:bg-slate-800"
                            >
                                <img
                                    :src="`/storage/${category.image}`"
                                    :alt="category.name"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                />
                            </div>

                            <!-- Content -->
                            <div class="p-6">
                                <h2
                                    class="text-xl font-bold text-slate-950 transition group-hover:text-green-600 dark:text-white dark:group-hover:text-green-400"
                                >
                                    {{ category.name }}
                                </h2>

                                <p
                                    class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                                >
                                    {{
                                        category.description ||
                                        'Explore products in this category.'
                                    }}
                                </p>

                                <div
                                    class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-green-600 dark:text-green-400"
                                >
                                    Shop category

                                    <svg
                                        class="h-4 w-4 transition-transform group-hover:translate-x-1"
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
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-else
                        class="rounded-2xl border border-dashed border-slate-300 px-6 py-16 text-center dark:border-slate-700"
                    >
                        <h2
                            class="text-lg font-bold text-slate-900 dark:text-white"
                        >
                            No categories available
                        </h2>

                        <p
                            class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                        >
                            There are currently no active categories.
                        </p>
                    </div>
                </div>
            </section>
        </main>
    </CustomerLayout>
</template>