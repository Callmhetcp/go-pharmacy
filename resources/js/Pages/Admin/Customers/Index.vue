<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    customers: {
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

const applyFilters = () => {
    router.get(
        route('admin.customers.index'),
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const clearFilters = () => {
    search.value = '';

    router.get(
        route('admin.customers.index'),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const formatDate = (date) => {
    if (!date) {
        return '—';
    }

    return new Intl.DateTimeFormat('en-NG', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date));
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));
};
</script>

<template>
    <AdminLayout>
        <div
            class="min-h-screen bg-slate-50 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
        >
            <!-- Page Header -->
            <section
                class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <!-- Breadcrumb -->
                    <nav
                        class="mb-4 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400"
                    >
                        <Link
                            :href="route('admin.dashboard')"
                            class="transition hover:text-green-600 dark:hover:text-green-400"
                        >
                            Admin
                        </Link>

                        <span>/</span>

                        <span
                            class="font-medium text-slate-700 dark:text-slate-200"
                        >
                            Customers
                        </span>
                    </nav>

                    <div>
                        <p
                            class="text-sm font-medium text-green-600 dark:text-green-400"
                        >
                            Admin Panel
                        </p>

                        <h1
                            class="mt-1 text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white"
                        >
                            Customers
                        </h1>

                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            View Go Pharmacy customers, their orders,
                            prescriptions, and purchasing history.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Content -->
            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <!-- Search -->
                <div
                    class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="flex flex-col gap-4 sm:flex-row">
                        <div class="flex-1">
                            <label
                                for="search"
                                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                            >
                                Search customers
                            </label>

                            <input
                                id="search"
                                v-model="search"
                                type="search"
                                placeholder="Name, email or phone..."
                                @keyup.enter="applyFilters"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500"
                            />
                        </div>

                        <div class="flex items-end gap-3">
                            <button
                                type="button"
                                @click="applyFilters"
                                class="rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700"
                            >
                                Search
                            </button>

                            <button
                                type="button"
                                @click="clearFilters"
                                class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="!customers?.data?.length"
                    class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900"
                >
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400"
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
                                d="M15 19a4 4 0 0 0-8 0m4-8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm6 8a4 4 0 0 0-3-3.87M16 5a3 3 0 0 1 0 5.83"
                            />
                        </svg>
                    </div>

                    <h2
                        class="mt-5 text-lg font-bold text-slate-950 dark:text-white"
                    >
                        No customers found
                    </h2>

                    <p
                        class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                    >
                        No customers match your current search.
                    </p>
                </div>

                <!-- Table -->
                <div
                    v-else
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead
                                class="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950"
                            >
                                <tr>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                    >
                                        Customer
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                    >
                                        Phone
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                    >
                                        Orders
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                    >
                                        Prescriptions
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                    >
                                        Total Spent
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                    >
                                        Joined
                                    </th>

                                    <th
                                        class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                    >
                                        Action
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <tr
                                    v-for="customer in customers.data"
                                    :key="customer.id"
                                    class="transition hover:bg-slate-50 dark:hover:bg-slate-800/50"
                                >
                                    <!-- Customer -->
                                    <td class="px-6 py-5">
                                        <div
                                            class="font-semibold text-slate-900 dark:text-white"
                                        >
                                            {{ customer.name }}
                                        </div>

                                        <div
                                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                        >
                                            {{ customer.email }}
                                        </div>
                                    </td>

                                    <!-- Phone -->
                                    <td
                                        class="whitespace-nowrap px-6 py-5 text-sm text-slate-700 dark:text-slate-300"
                                    >
                                        {{ customer.phone ?? '—' }}
                                    </td>

                                    <!-- Orders -->
                                    <td class="whitespace-nowrap px-6 py-5">
                                        <span
                                            class="font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{ customer.orders_count ?? 0 }}
                                        </span>
                                    </td>

                                    <!-- Prescriptions -->
                                    <td class="whitespace-nowrap px-6 py-5">
                                        <span
                                            class="font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{
                                                customer.prescriptions_count ??
                                                0
                                            }}
                                        </span>
                                    </td>

                                    <!-- Total -->
                                    <td class="whitespace-nowrap px-6 py-5">
                                        <span
                                            class="font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{
                                                formatCurrency(
                                                    customer.orders_sum_total,
                                                )
                                            }}
                                        </span>
                                    </td>

                                    <!-- Joined -->
                                    <td
                                        class="whitespace-nowrap px-6 py-5 text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        {{ formatDate(customer.created_at) }}
                                    </td>

                                    <!-- Action -->
                                    <td
                                        class="whitespace-nowrap px-6 py-5 text-right"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'admin.customers.show',
                                                    customer.id,
                                                )
                                            "
                                            class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-green-500 hover:text-green-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-green-500 dark:hover:text-green-400"
                                        >
                                            View
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="customers.links?.length > 3"
                        class="flex flex-wrap items-center justify-center gap-2 border-t border-slate-200 px-6 py-5 dark:border-slate-800"
                    >
                        <template
                            v-for="(link, index) in customers.links"
                            :key="`${link.label}-${index}`"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-scroll
                                class="rounded-lg border px-3 py-2 text-sm font-medium transition"
                                :class="
                                    link.active
                                        ? 'border-green-600 bg-green-600 text-white'
                                        : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700'
                                "
                                v-html="link.label"
                            />

                            <span
                                v-else
                                class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-400 dark:border-slate-800"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </main>
        </div>
    </AdminLayout>
</template>