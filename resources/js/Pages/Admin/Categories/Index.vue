<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();

const categories = computed(() => page.props.categories);

const deletingId = ref(null);

const deleteCategory = (category) => {
    if (category.products_count > 0) {
        alert(
            'This category cannot be deleted because it has products assigned to it.'
        );

        return;
    }

    if (
        !confirm(
            `Are you sure you want to delete "${category.name}"?`
        )
    ) {
        return;
    }

    deletingId.value = category.id;

    router.delete(
        `/admin/categories/${category.id}`,
        {
            preserveScroll: true,
            onFinish: () => {
                deletingId.value = null;
            },
        }
    );
};
</script>

<template>
    <AdminLayout>
        <div class="min-h-screen bg-slate-50">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

                <!-- Header -->
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p class="text-sm font-semibold text-green-600">
                            Go Pharmacy Administration
                        </p>

                        <h1
                            class="mt-1 text-3xl font-bold tracking-tight text-slate-900"
                        >
                            Categories
                        </h1>

                        <p class="mt-2 text-sm text-slate-500">
                            Organise your pharmacy products into categories.
                        </p>
                    </div>

                    <Link
                        href="/admin/categories/create"
                        class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700"
                    >
                        + Add Category
                    </Link>
                </div>

                <!-- Category Card -->
                <div
                    class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <!-- Table Header -->
                    <div
                        class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">
                                All Categories
                            </h2> 

                            <p class="mt-1 text-sm text-slate-500">
                                Manage your pharmacy catalogue categories.
                            </p>
                        </div>

                        <div
                            class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600"
                        >
                            {{ categories.total ?? 0 }} categories
                        </div>
                    </div>

                    <!-- Empty -->
                    <div
                        v-if="!categories.data?.length"
                        class="px-6 py-16 text-center"
                    >
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-green-50 text-green-600"
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
                                    d="M4 5a1 1 0 0 1 1-1h5l2 3h7a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5Z"
                                />
                            </svg>
                        </div>

                        <h3
                            class="mt-5 text-lg font-semibold text-slate-900"
                        >
                            No categories yet
                        </h3>

                        <p
                            class="mx-auto mt-2 max-w-md text-sm text-slate-500"
                        >
                            Create your first category to start organising
                            your pharmacy products.
                        </p>

                        <Link
                            href="/admin/categories/create"
                            class="mt-6 inline-flex rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700"
                        >
                            Create Category
                        </Link>
                    </div>

                    <!-- Desktop Table -->
                    <div
                        v-else
                        class="hidden overflow-x-auto md:block"
                    >
                        <table class="w-full">
                            <thead>
                                <tr
                                    class="border-b border-slate-200 bg-slate-50"
                                >
                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Category
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Products
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Status
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Order
                                    </th>

                                    <th
                                        class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="category in categories.data"
                                    :key="category.id"
                                    class="transition hover:bg-slate-50"
                                >
                                    <!-- Category -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="h-12 w-12 shrink-0 overflow-hidden rounded-xl bg-slate-100"
                                            >
                                                <img
                                                    v-if="category.image"
                                                    :src="`/storage/${category.image}`"
                                                    :alt="category.name"
                                                    class="h-full w-full object-cover"
                                                />

                                                <div
                                                    v-else
                                                    class="flex h-full w-full items-center justify-center text-slate-400"
                                                >
                                                    <svg
                                                        class="h-6 w-6"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="1.8"
                                                            d="M4 5a1 1 0 0 1 1-1h5l2 3h7a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5Z"
                                                        />
                                                    </svg>
                                                </div>
                                            </div>

                                            <div class="min-w-0">
                                                <p
                                                    class="truncate font-semibold text-slate-900"
                                                >
                                                    {{ category.name }}
                                                </p>

                                                <p
                                                    class="mt-1 truncate text-xs text-slate-400"
                                                >
                                                    /{{ category.slug }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Products -->
                                    <td class="px-6 py-4">
                                        <span
                                            class="font-semibold text-slate-700"
                                        >
                                            {{ category.products_count ?? 0 }}
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4">
                                        <span
                                            v-if="category.is_active"
                                            class="inline-flex rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700"
                                        >
                                            Active
                                        </span>

                                        <span
                                            v-else
                                            class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500"
                                        >
                                            Inactive
                                        </span>
                                    </td>

                                    <!-- Sort -->
                                    <td
                                        class="px-6 py-4 text-sm text-slate-500"
                                    >
                                        {{ category.sort_order }}
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4">
                                        <div
                                            class="flex justify-end gap-2"
                                        >
                                            <Link
                                                :href="`/admin/categories/${category.id}/edit`"
                                                class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-green-50 hover:text-green-600"
                                            >
                                                Edit
                                            </Link>

                                            <button
                                                type="button"
                                                :disabled="
                                                    deletingId === category.id
                                                "
                                                @click="deleteCategory(category)"
                                                class="rounded-lg px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                                {{
                                                    deletingId === category.id
                                                        ? 'Deleting...'
                                                        : 'Delete'
                                                }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div
                        v-if="categories.data?.length"
                        class="divide-y divide-slate-100 md:hidden"
                    >
                        <div
                            v-for="category in categories.data"
                            :key="category.id"
                            class="p-5"
                        >
                            <div class="flex gap-4">
                                <div
                                    class="h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-slate-100"
                                >
                                    <img
                                        v-if="category.image"
                                        :src="`/storage/${category.image}`"
                                        :alt="category.name"
                                        class="h-full w-full object-cover"
                                    />

                                    <div
                                        v-else
                                        class="flex h-full w-full items-center justify-center text-slate-400"
                                    >
                                        <svg
                                            class="h-6 w-6"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.8"
                                                d="M4 5a1 1 0 0 1 1-1h5l2 3h7a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5Z"
                                            />
                                        </svg>
                                    </div>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div
                                        class="flex items-start justify-between gap-3"
                                    >
                                        <div>
                                            <h3
                                                class="font-semibold text-slate-900"
                                            >
                                                {{ category.name }}
                                            </h3>

                                            <p
                                                class="mt-1 text-xs text-slate-400"
                                            >
                                                /{{ category.slug }}
                                            </p>
                                        </div>

                                        <span
                                            v-if="category.is_active"
                                            class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700"
                                        >
                                            Active
                                        </span>

                                        <span
                                            v-else
                                            class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500"
                                        >
                                            Inactive
                                        </span>
                                    </div>

                                    <p
                                        class="mt-3 text-sm text-slate-500"
                                    >
                                        {{ category.products_count ?? 0 }}
                                        products
                                    </p>

                                    <div class="mt-4 flex gap-2">
                                        <Link
                                            :href="`/admin/categories/${category.id}/edit`"
                                            class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-center text-sm font-semibold text-slate-700 hover:border-green-300 hover:text-green-600"
                                        >
                                            Edit
                                        </Link>

                                        <button
                                            type="button"
                                            :disabled="
                                                deletingId === category.id
                                            "
                                            @click="deleteCategory(category)"
                                            class="flex-1 rounded-lg border border-red-100 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 disabled:opacity-50"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="
                            categories.links &&
                            categories.links.length > 3
                        "
                        class="border-t border-slate-200 px-6 py-4"
                    >
                        <div class="flex flex-wrap gap-2">
                            <template
                                v-for="link in categories.links"
                                :key="link.label"
                            >
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    preserve-scroll
                                    class="rounded-lg px-3 py-2 text-sm font-medium transition"
                                    :class="
                                        link.active
                                            ? 'bg-green-600 text-white'
                                            : 'border border-slate-200 bg-white text-slate-600 hover:border-green-300 hover:text-green-600'
                                    "
                                    v-html="link.label"
                                />

                                <span
                                    v-else
                                    class="rounded-lg px-3 py-2 text-sm text-slate-300"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>