<script setup>
defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});

const getCategoryIcon = (categoryName) => {
    const name = categoryName.toLowerCase();

    if (
        name.includes('medicine') ||
        name.includes('drug') ||
        name.includes('pharmacy')
    ) {
        return 'medicine';
    }

    if (
        name.includes('vitamin') ||
        name.includes('supplement') ||
        name.includes('wellness')
    ) {
        return 'vitamins';
    }

    if (
        name.includes('personal') ||
        name.includes('care') ||
        name.includes('hygiene')
    ) {
        return 'care';
    }

    if (
        name.includes('baby') ||
        name.includes('mother') ||
        name.includes('maternity')
    ) {
        return 'baby';
    }

    if (
        name.includes('medical') ||
        name.includes('equipment') ||
        name.includes('supplies')
    ) {
        return 'medical';
    }

    if (
        name.includes('beauty') ||
        name.includes('skin') ||
        name.includes('cosmetic')
    ) {
        return 'beauty';
    }

    return 'medical';
};

const getCategoryUrl = (category) => {
    return `/categories/${category.slug}`;
};
</script>

<template>
    <section
        class="bg-white py-20 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-slate-100"
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
                        Explore our range
                    </p>

                    <h2
                        class="mt-3 text-3xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-4xl"
                    >
                        Shop by category
                    </h2>

                    <p
                        class="mt-4 text-base leading-7 text-slate-600 dark:text-slate-400"
                    >
                        Find the healthcare, wellness and everyday essentials
                        you need, all in one place.
                    </p>
                </div>

                <a
                    href="/categories"
                    class="inline-flex shrink-0 items-center gap-2 text-sm font-bold text-green-600 transition hover:text-green-700 dark:text-green-400 dark:hover:text-green-300"
                >
                    View all categories

                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M5 12h14m-6-6 6 6-6 6"
                        />
                    </svg>
                </a>
            </div>

            <!-- =====================================================
                 EMPTY STATE
            ====================================================== -->

            <div
                v-if="!categories.length"
                class="mt-10 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center dark:border-slate-700 dark:bg-slate-900"
            >
                <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                    No categories available
                </p>

                <p class="mt-1 text-sm text-slate-400">
                    Categories will appear here when they are added.
                </p>
            </div>

            <!-- =====================================================
                 CATEGORY GRID
            ====================================================== -->

            <div
                v-else
                class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <a
                    v-for="category in categories"
                    :key="category.id"
                    :href="getCategoryUrl(category)"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-green-200 hover:shadow-xl hover:shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-green-800 dark:hover:shadow-black/30"
                >

                    <!-- =================================================
                         TOP ROW
                    ================================================== -->

                    <div
                        class="flex items-start justify-between gap-5"
                    >

                        <!-- Icon -->

                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-green-50 text-green-600 transition duration-300 group-hover:bg-green-600 group-hover:text-white dark:bg-green-950/50 dark:text-green-400 dark:group-hover:bg-green-600 dark:group-hover:text-white"
                        >

                            <!-- Medicine -->

                            <svg
                                v-if="getCategoryIcon(category.name) === 'medicine'"
                                class="h-7 w-7"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="m14.5 4.5-10 10a3.54 3.54 0 0 0 5 5l10-10a3.54 3.54 0 0 0-5-5Z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="m12 7 5 5"
                                />
                            </svg>

                            <!-- Vitamins -->

                            <svg
                                v-else-if="getCategoryIcon(category.name) === 'vitamins'"
                                class="h-7 w-7"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M7.5 4.5h9a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2h-9a2 2 0 0 1-2-2v-11a2 2 0 0 1 2-2Z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M9 4.5V3h6v1.5M9 9h6M12 6v6"
                                />
                            </svg>

                            <!-- Personal Care -->

                            <svg
                                v-else-if="getCategoryIcon(category.name) === 'care'"
                                class="h-7 w-7"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M12 21s7-3.5 7-9.25V5.5L12 3 5 5.5v6.25C5 17.5 12 21 12 21Z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M9 12h6M12 9v6"
                                />
                            </svg>

                            <!-- Baby -->

                            <svg
                                v-else-if="getCategoryIcon(category.name) === 'baby'"
                                class="h-7 w-7"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="8.5"
                                    stroke-width="1.8"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-width="1.8"
                                    d="M8.5 10.5h.01M15.5 10.5h.01M9.5 14.5c1.5 1.5 3.5 1.5 5 0"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-width="1.8"
                                    d="M9 3.5c.5-1 1.5-1.5 3-1.5s2.5.5 3 1.5"
                                />
                            </svg>

                            <!-- Medical -->

                            <svg
                                v-else-if="getCategoryIcon(category.name) === 'medical'"
                                class="h-7 w-7"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <rect
                                    x="4"
                                    y="5"
                                    width="16"
                                    height="14"
                                    rx="2"
                                    stroke-width="1.8"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M8 12h8M12 8v8"
                                />
                            </svg>

                            <!-- Beauty -->

                            <svg
                                v-else
                                class="h-7 w-7"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M9 3h6M10 3v4l-4 7.5A4 4 0 0 0 9.5 20h5a4 4 0 0 0 3.5-5.5L14 7V3"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-width="1.8"
                                    d="M8 14h8"
                                />
                            </svg>
                        </div>

                        <!-- Arrow -->

                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 transition duration-300 group-hover:border-green-200 group-hover:bg-green-50 group-hover:text-green-600 dark:border-slate-700 dark:text-slate-500 dark:group-hover:border-green-800 dark:group-hover:bg-green-950/50 dark:group-hover:text-green-400"
                        >
                            <svg
                                class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5"
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

                    <!-- =================================================
                         CATEGORY CONTENT
                    ================================================== -->

                    <div class="mt-6">
                        <h3
                            class="text-lg font-bold text-slate-950 transition group-hover:text-green-600 dark:text-white dark:group-hover:text-green-400"
                        >
                            {{ category.name }}
                        </h3>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            {{ category.description || 'Explore our products in this category.' }}
                        </p>
                    </div>

                    <!-- =================================================
                         CATEGORY IMAGE
                    ================================================== -->

                    <div
                        v-if="category.image"
                        class="mt-5 overflow-hidden rounded-xl"
                    >
                        <img
                            :src="`/storage/${category.image}`"
                            :alt="category.name"
                            class="h-32 w-full object-cover transition duration-500 group-hover:scale-105"
                        />
                    </div>

                    <!-- Hover accent -->

                    <div
                        class="absolute bottom-0 left-0 h-1 w-0 bg-green-600 transition-all duration-300 group-hover:w-full"
                    ></div>
                </a>
            </div>
        </div>
    </section>
</template>
