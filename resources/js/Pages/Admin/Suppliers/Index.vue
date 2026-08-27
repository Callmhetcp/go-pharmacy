<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    suppliers: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({
            search: '',
        }),
    },
});

const search = ref(props.filters?.search ?? '');
const deletingId = ref(null);
const togglingId = ref(null);

const searchSuppliers = () => {
    router.get(
        '/admin/suppliers',
        {
            search: search.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const deleteSupplier = (supplier) => {
    if (
        !confirm(
            `Are you sure you want to delete "${supplier.name}"?`
        )
    ) {
        return;
    }

    deletingId.value = supplier.id;

    router.delete(`/admin/suppliers/${supplier.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deletingId.value = null;
        },
    });
};

const toggleStatus = (supplier) => {
    togglingId.value = supplier.id;

    router.patch(
        `/admin/suppliers/${supplier.id}/toggle-status`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                togglingId.value = null;
            },
        }
    );
};
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            <!-- Header -->
            <div
                class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <p class="text-sm font-semibold text-green-600">
                        Supplier Management
                    </p>

                    <h1
                        class="mt-1 text-3xl font-bold tracking-tight text-slate-950"
                    >
                        Suppliers
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Manage pharmacy suppliers and their contact information.
                    </p>
                </div>

                <Link
                    href="/admin/suppliers/create"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700"
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
                            stroke-width="2"
                            d="M12 5v14m-7-7h14"
                        />
                    </svg>

                    Add Supplier
                </Link>
            </div>

            <!-- Suppliers Card -->
            <div
                class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <!-- Card Header -->
                <div
                    class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            Supplier Directory
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ suppliers.total ?? 0 }} suppliers registered
                        </p>
                    </div>

                    <!-- Search -->
                    <form
                        @submit.prevent="searchSuppliers"
                        class="flex w-full gap-2 sm:w-auto"
                    >
                        <div class="relative w-full sm:w-72">
                            <svg
                                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
                                />
                            </svg>

                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search suppliers..."
                                class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />
                        </div>

                        <button
                            type="submit"
                            class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            Search
                        </button>
                    </form>
                </div>

                <!-- Showing -->
                <div
                    v-if="suppliers.data.length > 0"
                    class="border-b border-slate-200 bg-slate-50 px-6 py-3"
                >
                    <p class="text-xs font-medium text-slate-500">
                        Showing
                        {{ suppliers.from ?? 0 }}
                        -
                        {{ suppliers.to ?? 0 }}
                        of
                        {{ suppliers.total ?? 0 }}
                    </p>
                </div>

                <!-- Empty State -->
                <div
                    v-if="suppliers.data.length === 0"
                    class="px-6 py-20 text-center"
                >
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-green-50 text-green-600"
                    >
                        <svg
                            class="h-7 w-7"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M16 11a4 4 0 1 0-8 0m12 9a8 8 0 0 0-16 0m12-9a4 4 0 1 0-8 0"
                            />
                        </svg>
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-slate-900">
                        No suppliers found
                    </h3>

                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                        {{
                            search
                                ? 'Try adjusting your search to find a supplier.'
                                : 'Start managing your pharmacy suppliers by adding your first supplier.'
                        }}
                    </p>

                    <Link
                        v-if="!search"
                        href="/admin/suppliers/create"
                        class="mt-6 inline-flex rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700"
                    >
                        Add Your First Supplier
                    </Link>

                    <button
                        v-else
                        type="button"
                        @click="
                            search = '';
                            searchSuppliers();
                        "
                        class="mt-6 inline-flex rounded-xl bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-200"
                    >
                        Clear Search
                    </button>
                </div>

                <!-- Desktop Table -->
                <div
                    v-else
                    class="hidden overflow-x-auto md:block"
                >
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Supplier
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Contact
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Location
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr
                                v-for="supplier in suppliers.data"
                                :key="supplier.id"
                                class="transition hover:bg-slate-50"
                            >
                                <!-- Supplier -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-green-50 text-green-600"
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
                                                    stroke-width="1.7"
                                                    d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4M9 9h.01M12 9h.01M15 9h.01M9 12h.01M12 12h.01M15 12h.01"
                                                />
                                            </svg>
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="max-w-xs truncate text-sm font-semibold text-slate-900"
                                            >
                                                {{ supplier.name }}
                                            </p>

                                            <p
                                                v-if="supplier.company_name"
                                                class="mt-1 max-w-xs truncate text-xs text-slate-500"
                                            >
                                                {{ supplier.company_name }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact -->
                                <td class="px-6 py-4">
                                    <div>
                                        <p
                                            class="text-sm font-medium text-slate-700"
                                        >
                                            {{ supplier.phone }}
                                        </p>

                                        <p
                                            v-if="supplier.email"
                                            class="mt-1 text-xs text-slate-500"
                                        >
                                            {{ supplier.email }}
                                        </p>
                                    </div>
                                </td>

                                <!-- Location -->
                                <td class="px-6 py-4">
                                    <span class="text-sm text-slate-600">
                                        {{
                                            [
                                                supplier.city,
                                                supplier.state,
                                            ]
                                                .filter(Boolean)
                                                .join(', ') || '—'
                                        }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <span
                                        v-if="supplier.is_active"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full bg-green-500"
                                        ></span>

                                        Active
                                    </span>

                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full bg-slate-400"
                                        ></span>

                                        Inactive
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-right"
                                >
                                    <div
                                        class="flex items-center justify-end gap-1"
                                    >
                                        <Link
                                            :href="`/admin/suppliers/${supplier.id}`"
                                            class="rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                                        >
                                            View
                                        </Link>

                                        <Link
                                            :href="`/admin/suppliers/${supplier.id}/edit`"
                                            class="rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                                        >
                                            Edit
                                        </Link>

                                        <button
                                            type="button"
                                            :disabled="
                                                togglingId === supplier.id
                                            "
                                            @click="toggleStatus(supplier)"
                                            class="rounded-lg px-3 py-2 text-xs font-semibold transition disabled:cursor-not-allowed disabled:opacity-50"
                                            :class="
                                                supplier.is_active
                                                    ? 'text-amber-600 hover:bg-amber-50'
                                                    : 'text-green-600 hover:bg-green-50'
                                            "
                                        >
                                            {{
                                                togglingId === supplier.id
                                                    ? 'Updating...'
                                                    : supplier.is_active
                                                      ? 'Deactivate'
                                                      : 'Activate'
                                            }}
                                        </button>

                                        <button
                                            type="button"
                                            :disabled="
                                                deletingId === supplier.id
                                            "
                                            @click="deleteSupplier(supplier)"
                                            class="rounded-lg px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            {{
                                                deletingId === supplier.id
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
                    v-if="suppliers.data.length > 0"
                    class="divide-y divide-slate-100 md:hidden"
                >
                    <div
                        v-for="supplier in suppliers.data"
                        :key="supplier.id"
                        class="p-5"
                    >
                        <div class="flex gap-4">
                            <!-- Icon -->
                            <div
                                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-green-50 text-green-600"
                            >
                                <svg
                                    class="h-7 w-7"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.7"
                                        d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4M9 9h.01M12 9h.01M15 9h.01M9 12h.01M12 12h.01M15 12h.01"
                                    />
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div class="min-w-0">
                                        <h3
                                            class="truncate font-semibold text-slate-900"
                                        >
                                            {{ supplier.name }}
                                        </h3>

                                        <p
                                            v-if="supplier.company_name"
                                            class="mt-1 truncate text-xs text-slate-500"
                                        >
                                            {{ supplier.company_name }}
                                        </p>
                                    </div>

                                    <span
                                        v-if="supplier.is_active"
                                        class="shrink-0 rounded-full bg-green-50 px-2 py-1 text-[10px] font-bold text-green-700"
                                    >
                                        Active
                                    </span>

                                    <span
                                        v-else
                                        class="shrink-0 rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-600"
                                    >
                                        Inactive
                                    </span>
                                </div>

                                <div class="mt-3 space-y-1">
                                    <p class="text-sm text-slate-600">
                                        {{ supplier.phone }}
                                    </p>

                                    <p
                                        v-if="supplier.email"
                                        class="truncate text-xs text-slate-500"
                                    >
                                        {{ supplier.email }}
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        {{
                                            [
                                                supplier.city,
                                                supplier.state,
                                            ]
                                                .filter(Boolean)
                                                .join(', ') || 'No location'
                                        }}
                                    </p>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <Link
                                        :href="`/admin/suppliers/${supplier.id}`"
                                        class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700"
                                    >
                                        View
                                    </Link>

                                    <Link
                                        :href="`/admin/suppliers/${supplier.id}/edit`"
                                        class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700"
                                    >
                                        Edit
                                    </Link>

                                    <button
                                        type="button"
                                        :disabled="
                                            togglingId === supplier.id
                                        "
                                        @click="toggleStatus(supplier)"
                                        class="rounded-lg px-3 py-2 text-xs font-semibold disabled:opacity-50"
                                        :class="
                                            supplier.is_active
                                                ? 'bg-amber-50 text-amber-700'
                                                : 'bg-green-50 text-green-700'
                                        "
                                    >
                                        {{
                                            togglingId === supplier.id
                                                ? 'Updating...'
                                                : supplier.is_active
                                                  ? 'Deactivate'
                                                  : 'Activate'
                                        }}
                                    </button>

                                    <button
                                        type="button"
                                        :disabled="
                                            deletingId === supplier.id
                                        "
                                        @click="deleteSupplier(supplier)"
                                        class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 disabled:opacity-50"
                                    >
                                        {{
                                            deletingId === supplier.id
                                                ? 'Deleting...'
                                                : 'Delete'
                                        }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="suppliers.links && suppliers.links.length > 3"
                    class="flex flex-wrap items-center justify-center gap-2 border-t border-slate-200 px-6 py-5"
                >
                    <template
                        v-for="(link, index) in suppliers.links"
                        :key="index"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            class="rounded-lg px-3 py-2 text-sm font-medium transition"
                            :class="
                                link.active
                                    ? 'bg-green-600 text-white'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                            "
                            v-html="link.label"
                        />

                        <span
                            v-else
                            class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-300"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>