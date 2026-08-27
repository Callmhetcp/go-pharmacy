<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    sales: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({
            search: '',
            payment_method: '',
            date_from: '',
            date_to: '',
        }),
    },

    summary: {
        type: Object,
        default: () => ({
            total_sales: 0,
            total_amount: 0,
        }),
    },

    paymentMethods: {
        type: Array,
        default: () => [],
    },
});

const search = ref(props.filters.search ?? '');
const paymentMethod = ref(
    props.filters.payment_method ?? ''
);
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');

let searchTimer = null;

const formatMoney = (value) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
    }).format(Number(value ?? 0));
};

const submitFilters = () => {
    router.get(
        route('admin.pos.history'),
        {
            search: search.value || undefined,
            payment_method:
                paymentMethod.value || undefined,
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

watch(search, () => {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        submitFilters();
    }, 400);
});

const clearFilters = () => {
    search.value = '';
    paymentMethod.value = '';
    dateFrom.value = '';
    dateTo.value = '';

    router.get(
        route('admin.pos.history'),
        {},
        {
            preserveState: false,
            preserveScroll: true,
            replace: true,
        }
    );
};

const paymentLabel = (value) => {
    const method = props.paymentMethods.find(
        (item) => item.value === value
    );

    return method?.label ?? '—';
};

const paymentClass = (value) => {
    if (value === 'cash') {
        return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400';
    }

    if (value === 'transfer') {
        return 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400';
    }

    if (value === 'card') {
        return 'bg-purple-100 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400';
    }

    return 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400';
};

const statusClass = computed(() => {
    return (status) => {
        if (status === 'completed') {
            return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400';
        }

        if (status === 'cancelled') {
            return 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400';
        }

        return 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400';
    };
});

const goToPage = (url) => {
    if (!url) {
        return;
    }

    router.get(
        url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};
</script>

<template>
    <AdminLayout>
        <div
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 transition-colors dark:bg-slate-950 dark:text-white sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-7xl">

                <!-- Breadcrumbs -->
                <div class="mb-6 flex items-center gap-2 text-sm">
                    <Link
                        :href="route('admin.dashboard')"
                        class="text-slate-500 transition hover:text-green-600 dark:text-slate-400 dark:hover:text-green-400"
                    >
                        Dashboard
                    </Link>

                    <svg
                        class="h-4 w-4 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m9 5 7 7-7 7"
                        />
                    </svg>

                    <Link
                        :href="route('admin.pos.index')"
                        class="text-slate-500 transition hover:text-green-600 dark:text-slate-400 dark:hover:text-green-400"
                    >
                        Point of Sale
                    </Link>

                    <svg
                        class="h-4 w-4 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m9 5 7 7-7 7"
                        />
                    </svg>

                    <span
                        class="font-semibold text-slate-900 dark:text-white"
                    >
                        Sales History
                    </span>
                </div>

                <!-- Header -->
                <div
                    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h1
                            class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                        >
                            POS Sales History
                        </h1>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            View and manage completed walk-in sales.
                        </p>
                    </div>

                    <Link
                        :href="route('admin.pos.index')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700"
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

                        New POS Sale
                    </Link>
                </div>

                <!-- Summary -->
                <div class="mb-6 grid gap-4 sm:grid-cols-2">
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                >
                                    Total Sales
                                </p>

                                <p
                                    class="mt-1 text-2xl font-bold text-slate-900 dark:text-white"
                                >
                                    {{ summary.total_sales }}
                                </p>
                            </div>

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-100 text-green-600 dark:bg-green-500/15 dark:text-green-400"
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
                                        d="M3 7h18M5 7v12h14V7M8 4h8v3H8V4Zm1 7h6"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                >
                                    Total Amount
                                </p>

                                <p
                                    class="mt-1 text-2xl font-bold text-slate-900 dark:text-white"
                                >
                                    {{ formatMoney(summary.total_amount) }}
                                </p>
                            </div>

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400"
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
                                        d="M12 3v18m5-14H9.5a2.5 2.5 0 0 0 0 5h5a2.5 2.5 0 0 1 0 5H7"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div
                    class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="grid gap-4 lg:grid-cols-[2fr_1fr_1fr_1fr_auto]"
                    >
                        <!-- Search -->
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                            >
                                Search
                            </label>

                            <div class="relative">
                                <svg
                                    class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="m21 21-4.3-4.3m2.3-5.2a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"
                                    />
                                </svg>

                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Order number or customer..."
                                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:ring-green-500/10"
                                />
                            </div>
                        </div>

                        <!-- Payment -->
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                            >
                                Payment
                            </label>

                            <select
                                v-model="paymentMethod"
                                @change="submitFilters"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-green-500/10"
                            >
                                <option value="">
                                    All Methods
                                </option>

                                <option
                                    v-for="method in paymentMethods"
                                    :key="method.value"
                                    :value="method.value"
                                >
                                    {{ method.label }}
                                </option>
                            </select>
                        </div>

                        <!-- From -->
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                            >
                                From
                            </label>

                            <input
                                v-model="dateFrom"
                                type="date"
                                @change="submitFilters"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-green-500/10"
                            />
                        </div>

                        <!-- To -->
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                            >
                                To
                            </label>

                            <input
                                v-model="dateTo"
                                type="date"
                                @change="submitFilters"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-green-500/10"
                            />
                        </div>

                        <!-- Clear -->
                        <div class="flex items-end">
                            <button
                                type="button"
                                @click="clearFilters"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-red-500/30 dark:hover:bg-red-500/10 dark:hover:text-red-400 lg:w-auto"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sales Table -->
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead
                                class="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950"
                            >
                                <tr>
                                    <th
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                    >
                                        Sale
                                    </th>

                                    <th
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                    >
                                        Customer
                                    </th>

                                    <th
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                    >
                                        Cashier
                                    </th>

                                    <th
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                    >
                                        Payment
                                    </th>

                                    <th
                                        class="px-5 py-4 text-right text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                    >
                                        Total
                                    </th>

                                    <th
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                    >
                                        Status
                                    </th>

                                    <th
                                        class="px-5 py-4 text-right text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                v-if="sales.data.length"
                                class="divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <tr
                                    v-for="sale in sales.data"
                                    :key="sale.id"
                                    class="transition hover:bg-slate-50 dark:hover:bg-slate-800/50"
                                >
                                    <!-- Sale -->
                                    <td class="px-5 py-4">
                                        <p
                                            class="font-semibold text-slate-900 dark:text-white"
                                        >
                                            {{ sale.order_number }}
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{ sale.created_at_formatted }}
                                        </p>
                                    </td>

                                    <!-- Customer -->
                                    <td class="px-5 py-4">
                                        <p
                                            class="text-sm font-medium text-slate-900 dark:text-white"
                                        >
                                            {{ sale.customer_name }}
                                        </p>

                                        <p
                                            v-if="sale.customer_phone"
                                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{ sale.customer_phone }}
                                        </p>
                                    </td>

                                    <!-- Cashier -->
                                    <td class="px-5 py-4">
                                        <div v-if="sale.cashier">
                                            <p
                                                class="text-sm font-semibold text-slate-900 dark:text-white"
                                            >
                                                {{ sale.cashier.name }}
                                            </p>

                                            <p
                                                v-if="sale.cashier.email"
                                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                {{ sale.cashier.email }}
                                            </p>
                                        </div>

                                        <span
                                            v-else
                                            class="text-sm text-slate-400"
                                        >
                                            Unknown
                                        </span>
                                    </td>

                                    <!-- Payment -->
                                    <td class="px-5 py-4">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                            :class="
                                                paymentClass(
                                                    sale.payment_method
                                                )
                                            "
                                        >
                                            {{
                                                paymentLabel(
                                                    sale.payment_method
                                                )
                                            }}
                                        </span>
                                    </td>

                                    <!-- Total -->
                                    <td
                                        class="px-5 py-4 text-right"
                                    >
                                        <p
                                            class="font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ formatMoney(sale.total) }}
                                        </p>

                                        <p
                                            v-if="sale.discount > 0"
                                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            Discount:
                                            {{
                                                formatMoney(
                                                    sale.discount
                                                )
                                            }}
                                        </p>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-5 py-4">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize"
                                            :class="
                                                statusClass(
                                                    sale.status
                                                )
                                            "
                                        >
                                            {{ sale.status }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-5 py-4">
                                        <div
                                            class="flex justify-end gap-2"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'admin.pos.show',
                                                        sale.id
                                                    )
                                                "
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-green-300 hover:bg-green-50 hover:text-green-700 dark:border-slate-700 dark:text-slate-300 dark:hover:border-green-500/30 dark:hover:bg-green-500/10 dark:hover:text-green-400"
                                            >
                                                <svg
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"
                                                    />
                                                    <circle
                                                        cx="12"
                                                        cy="12"
                                                        r="2.5"
                                                    />
                                                </svg>

                                                Details
                                            </Link>

                                            <Link
                                                :href="
                                                    route(
                                                        'admin.pos.receipt',
                                                        sale.id
                                                    )
                                                "
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:text-slate-300 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/10 dark:hover:text-blue-400"
                                            >
                                                <svg
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M6 3h12v18H6V3Zm3 4h6m-6 4h6m-6 4h4"
                                                    />
                                                </svg>

                                                Receipt
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- Empty -->
                            <tbody v-else>
                                <tr>
                                    <td
                                        colspan="7"
                                        class="px-6 py-16 text-center"
                                    >
                                        <div
                                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800"
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
                                                    d="M7 3h10a2 2 0 0 1 2 2v14H5V5a2 2 0 0 1 2-2Zm3 4h4m-4 4h4m-4 4h4"
                                                />
                                            </svg>
                                        </div>

                                        <h3
                                            class="mt-4 text-sm font-bold text-slate-900 dark:text-white"
                                        >
                                            No POS sales found
                                        </h3>

                                        <p
                                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                        >
                                            Try changing your search or filters.
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="sales.links?.length > 3"
                        class="flex flex-col gap-4 border-t border-slate-200 px-5 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <p
                            class="text-sm text-slate-500 dark:text-slate-400"
                        >
                            Showing
                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ sales.from ?? 0 }}
                            </span>

                            to

                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ sales.to ?? 0 }}
                            </span>

                            of

                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ sales.total ?? 0 }}
                            </span>

                            sales
                        </p>

                        <div class="flex flex-wrap gap-1">
                            <button
                                v-for="link in sales.links"
                                :key="link.label"
                                type="button"
                                :disabled="!link.url"
                                @click="goToPage(link.url)"
                                class="min-w-9 rounded-lg px-3 py-2 text-sm font-medium transition"
                                :class="
                                    link.active
                                        ? 'bg-green-600 text-white'
                                        : link.url
                                            ? 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'
                                            : 'cursor-not-allowed text-slate-300 dark:text-slate-700'
                                "
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>