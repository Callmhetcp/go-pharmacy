<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    orders: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
            payment_status: '',
        }),
    },

    statuses: {
        type: Array,
        default: () => [],
    },

    paymentStatuses: {
        type: Array,
        default: () => [],
    },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const paymentStatus = ref(props.filters.payment_status ?? '');

const applyFilters = () => {
    router.get(
        '/admin/orders',
        {
            search: search.value || undefined,
            status: status.value || undefined,
            payment_status: paymentStatus.value || undefined,
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
    status.value = '';
    paymentStatus.value = '';

    router.get(
        '/admin/orders',
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const hasFilters = computed(() => {
    return (
        search.value ||
        status.value ||
        paymentStatus.value
    );
});

const formatMoney = (value) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
    }).format(Number(value ?? 0));
};

const formatDate = (date) => {
    if (!date) {
        return '—';
    }

    return new Date(date).toLocaleDateString('en-NG', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const statusClass = (value) => {
    const classes = {
        pending: 'bg-amber-50 text-amber-700 ring-amber-600/20',
        confirmed: 'bg-blue-50 text-blue-700 ring-blue-600/20',
        processing: 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
        ready: 'bg-purple-50 text-purple-700 ring-purple-600/20',
        shipped: 'bg-cyan-50 text-cyan-700 ring-cyan-600/20',
        completed: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        cancelled: 'bg-red-50 text-red-700 ring-red-600/20',
    };

    return classes[value] ??
        'bg-slate-50 text-slate-600 ring-slate-600/20';
};

const paymentClass = (value) => {
    const classes = {
        awaiting_payment:
            'bg-amber-50 text-amber-700 ring-amber-600/20',

        payment_submitted:
            'bg-blue-50 text-blue-700 ring-blue-600/20',

        paid:
            'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

        rejected:
            'bg-red-50 text-red-700 ring-red-600/20',
    };

    return classes[value] ??
        'bg-slate-50 text-slate-600 ring-slate-600/20';
};

const formatStatus = (value) => {
    if (!value) {
        return '—';
    }

    return value
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
};
</script>

<template>
    <AdminLayout>
        <div class="min-h-screen bg-slate-50 text-slate-900">

            <!-- =====================================================
                 PAGE HEADER
            ====================================================== -->

            <div
                class="border-b border-slate-200 bg-white"
            >
                <div
                    class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h1
                                class="text-2xl font-bold tracking-tight text-slate-950"
                            >
                                Orders
                            </h1>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Manage customer orders and payment status.
                            </p>
                        </div>

                        <div
                            class="flex items-center gap-2"
                        >
                            <div
                                class="rounded-xl bg-green-50 px-4 py-2 text-sm font-semibold text-green-700"
                            >
                                {{ orders.total ?? 0 }} Orders
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =====================================================
                 CONTENT
            ====================================================== -->

            <div
                class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
            >

                <!-- =================================================
                     FILTERS
                ================================================== -->

                <div
                    class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div
                        class="grid gap-3 lg:grid-cols-[1fr_190px_210px_auto_auto]"
                    >

                        <!-- Search -->

                        <div class="relative">
                            <svg
                                class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
                                />
                            </svg>

                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search orders, customer..."
                                class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/10"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <!-- Order Status -->

                        <select
                            v-model="status"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/10"
                        >
                            <option value="">
                                All Order Status
                            </option>

                            <option
                                v-for="item in statuses"
                                :key="item"
                                :value="item"
                            >
                                {{ formatStatus(item) }}
                            </option>
                        </select>

                        <!-- Payment Status -->

                        <select
                            v-model="paymentStatus"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/10"
                        >
                            <option value="">
                                All Payment Status
                            </option>

                            <option
                                v-for="item in paymentStatuses"
                                :key="item"
                                :value="item"
                            >
                                {{ formatStatus(item) }}
                            </option>
                        </select>

                        <!-- Apply -->

                        <button
                            type="button"
                            @click="applyFilters"
                            class="rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700"
                        >
                            Filter
                        </button>

                        <!-- Clear -->

                        <button
                            v-if="hasFilters"
                            type="button"
                            @click="clearFilters"
                            class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                        >
                            Clear
                        </button>
                    </div>
                </div>

                <!-- =================================================
                     ORDERS TABLE
                ================================================== -->

                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >

                    <!-- Desktop -->

                    <div class="hidden overflow-x-auto md:block">

                        <table class="w-full text-left">
                            <thead
                                class="border-b border-slate-200 bg-slate-50"
                            >
                                <tr>
                                    <th
                                        class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Order
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Customer
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Items
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Total
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Order Status
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Payment
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Date
                                    </th>

                                    <th
                                        class="px-6 py-4"
                                    ></th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-slate-100"
                            >

                                <!-- Empty -->

                                <tr v-if="!orders.data?.length">
                                    <td
                                        colspan="8"
                                        class="px-6 py-16 text-center"
                                    >
                                        <div
                                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100"
                                        >
                                            <svg
                                                class="h-7 w-7 text-slate-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.8"
                                                    d="M7 4h10a2 2 0 0 1 2 2v14H5V6a2 2 0 0 1 2-2Zm2 5h6m-6 4h6m-6 4h4"
                                                />
                                            </svg>
                                        </div>

                                        <h3
                                            class="mt-4 text-sm font-bold text-slate-900"
                                        >
                                            No orders found
                                        </h3>

                                        <p
                                            class="mt-1 text-sm text-slate-500"
                                        >
                                            Orders will appear here when customers place them.
                                        </p>
                                    </td>
                                </tr>

                                <!-- Orders -->

                                <tr
                                    v-for="order in orders.data"
                                    :key="order.id"
                                    class="transition hover:bg-slate-50"
                                >
                                    <td class="px-6 py-4">
                                        <Link
                                            :href="`/admin/orders/${order.id}`"
                                            class="font-semibold text-slate-900 hover:text-green-600"
                                        >
                                            #{{ order.order_number }}
                                        </Link>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div
                                            class="font-medium text-slate-900"
                                        >
                                            {{ order.customer_name }}
                                        </div>

                                        <div
                                            class="mt-0.5 text-xs text-slate-500"
                                        >
                                            {{ order.customer_email }}
                                        </div>
                                    </td>

                                    <td
                                        class="px-6 py-4 text-sm text-slate-600"
                                    >
                                        {{ order.items_count ?? 0 }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="font-semibold text-slate-900"
                                        >
                                            {{ formatMoney(order.total) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                            :class="statusClass(order.status)"
                                        >
                                            {{ formatStatus(order.status) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                            :class="paymentClass(order.payment_status)"
                                        >
                                            {{ formatStatus(order.payment_status) }}
                                        </span>
                                    </td>

                                    <td
                                        class="px-6 py-4 text-sm text-slate-500"
                                    >
                                        {{ formatDate(order.created_at) }}
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <Link
                                            :href="`/admin/orders/${order.id}`"
                                            class="inline-flex rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-green-300 hover:bg-green-50 hover:text-green-700"
                                        >
                                            View
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- =================================================
                         MOBILE
                    ================================================== -->

                    <div class="divide-y divide-slate-100 md:hidden">

                        <div
                            v-if="!orders.data?.length"
                            class="px-5 py-14 text-center"
                        >
                            <p
                                class="text-sm font-semibold text-slate-900"
                            >
                                No orders found
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-500"
                            >
                                Customer orders will appear here.
                            </p>
                        </div>

                        <Link
                            v-for="order in orders.data"
                            :key="order.id"
                            :href="`/admin/orders/${order.id}`"
                            class="block p-5 transition hover:bg-slate-50"
                        >
                            <div
                                class="flex items-start justify-between gap-4"
                            >
                                <div>
                                    <p
                                        class="font-bold text-slate-900"
                                    >
                                        #{{ order.order_number }}
                                    </p>

                                    <p
                                        class="mt-1 text-sm text-slate-600"
                                    >
                                        {{ order.customer_name }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-400"
                                    >
                                        {{ formatDate(order.created_at) }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p
                                        class="font-bold text-slate-900"
                                    >
                                        {{ formatMoney(order.total) }}
                                    </p>

                                    <span
                                        class="mt-2 inline-flex rounded-full px-2 py-1 text-[11px] font-semibold ring-1 ring-inset"
                                        :class="statusClass(order.status)"
                                    >
                                        {{ formatStatus(order.status) }}
                                    </span>
                                </div>
                            </div>

                            <div
                                class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3"
                            >
                                <span
                                    class="text-xs text-slate-500"
                                >
                                    {{ order.items_count ?? 0 }} item(s)
                                </span>

                                <span
                                    class="text-xs font-semibold"
                                    :class="
                                        order.payment_status === 'paid'
                                            ? 'text-emerald-600'
                                            : 'text-amber-600'
                                    "
                                >
                                    {{ formatStatus(order.payment_status) }}
                                </span>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- =================================================
                     PAGINATION
                ================================================== -->

                <div
                    v-if="orders.links?.length > 3"
                    class="mt-6 flex flex-wrap items-center justify-center gap-1"
                >
                    <template
                        v-for="link in orders.links"
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
                                    : 'text-slate-600 hover:bg-white hover:text-slate-900'
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
    </AdminLayout>
</template>