<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    purchases: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
        }),
    },

    statuses: {
        type: Array,
        default: () => [],
    },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

let searchTimeout = null;

const applyFilters = () => {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        router.get(
            '/admin/purchases',
            {
                search: search.value || undefined,
                status: status.value || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }, 300);
};

watch(search, applyFilters);

watch(status, () => {
    router.get(
        '/admin/purchases',
        {
            search: search.value || undefined,
            status: status.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
});

const formatPrice = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
    }).format(Number(amount || 0));
};

const formatDate = (date) => {
    if (!date) {
        return '—';
    }

    return new Date(date).toLocaleDateString('en-NG', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const statusLabel = (value) => {
    const labels = {
        draft: 'Draft',
        ordered: 'Ordered',
        received: 'Received',
        cancelled: 'Cancelled',
    };

    return labels[value] ?? value;
};

const statusClass = (value) => {
    const classes = {
        draft: 'bg-slate-100 text-slate-700',
        ordered: 'bg-blue-50 text-blue-700',
        received: 'bg-green-50 text-green-700',
        cancelled: 'bg-red-50 text-red-700',
    };

    return classes[value] ?? 'bg-slate-100 text-slate-700';
};

const canEdit = (purchase) => {
    return ['draft', 'ordered'].includes(purchase.status);
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
                        Procurement Management
                    </p>

                    <h1
                        class="mt-1 text-3xl font-bold tracking-tight text-slate-950"
                    >
                        Purchase Orders
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Manage supplier purchases, stock orders and procurement
                        records.
                    </p>
                </div>

                <Link
                    href="/admin/purchases/create"
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

                    New Purchase
                </Link>
            </div>

            <!-- Main Card -->
            <div
                class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <!-- Card Header -->
                <div class="border-b border-slate-200 px-6 py-5">
                    <div
                        class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                    >
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">
                                Purchase Orders
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ purchases.total ?? 0 }}
                                {{
                                    (purchases.total ?? 0) === 1
                                        ? 'record'
                                        : 'records'
                                }}
                            </p>
                        </div>

                        <!-- Filters -->
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <!-- Search -->
                            <div class="relative">
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
                                        d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
                                    />
                                </svg>

                                <input
                                    v-model="search"
                                    type="search"
                                    placeholder="Search purchases..."
                                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-100 sm:w-64"
                                />
                            </div>

                            <!-- Status -->
                            <select
                                v-model="status"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            >
                                <option value="">
                                    All Statuses
                                </option>

                                <option
                                    v-for="item in statuses"
                                    :key="item"
                                    :value="item"
                                >
                                    {{ statusLabel(item) }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="!purchases.data?.length"
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
                                d="M3 7.5 12 3l9 4.5M3 7.5V17l9 4 9-4V7.5M12 12l9-4.5M12 12 3 7.5M12 12v9"
                            />
                        </svg>
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-slate-900">
                        No purchase orders found
                    </h3>

                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                        Create your first purchase order to start managing
                        supplier procurement.
                    </p>

                    <Link
                        href="/admin/purchases/create"
                        class="mt-6 inline-flex rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700"
                    >
                        Create Purchase
                    </Link>
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
                                    Reference
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Supplier
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Date
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Items
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Total
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-100 bg-white"
                        >
                            <tr
                                v-for="purchase in purchases.data"
                                :key="purchase.id"
                                class="transition hover:bg-slate-50"
                            >
                                <!-- Reference -->
                                <td class="whitespace-nowrap px-6 py-5">
                                    <p
                                        class="font-mono text-sm font-semibold text-slate-900"
                                    >
                                        {{ purchase.reference }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        #{{ purchase.id }}
                                    </p>
                                </td>

                                <!-- Supplier -->
                                <td class="px-6 py-5">
                                    <p
                                        class="text-sm font-semibold text-slate-900"
                                    >
                                        {{
                                            purchase.supplier?.company_name ||
                                            purchase.supplier?.name ||
                                            '—'
                                        }}
                                    </p>

                                    <p
                                        v-if="
                                            purchase.supplier?.company_name &&
                                            purchase.supplier?.name
                                        "
                                        class="mt-1 text-xs text-slate-500"
                                    >
                                        {{ purchase.supplier.name }}
                                    </p>
                                </td>

                                <!-- Date -->
                                <td
                                    class="whitespace-nowrap px-6 py-5 text-sm text-slate-600"
                                >
                                    {{ formatDate(purchase.purchase_date) }}
                                </td>

                                <!-- Items -->
                                <td
                                    class="whitespace-nowrap px-6 py-5 text-sm text-slate-600"
                                >
                                    {{ purchase.items_count ?? 0 }}
                                    {{
                                        (purchase.items_count ?? 0) === 1
                                            ? 'item'
                                            : 'items'
                                    }}
                                </td>

                                <!-- Total -->
                                <td class="whitespace-nowrap px-6 py-5">
                                    <span
                                        class="text-sm font-bold text-slate-900"
                                    >
                                        {{
                                            formatPrice(
                                                purchase.total_amount,
                                            )
                                        }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="whitespace-nowrap px-6 py-5">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-bold"
                                        :class="
                                            statusClass(purchase.status)
                                        "
                                    >
                                        {{ statusLabel(purchase.status) }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td
                                    class="whitespace-nowrap px-6 py-5 text-right"
                                >
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <Link
                                            :href="`/admin/purchases/${purchase.id}`"
                                            class="rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                                        >
                                            View
                                        </Link>

                                        <Link
                                            v-if="canEdit(purchase)"
                                            :href="`/admin/purchases/${purchase.id}/edit`"
                                            class="rounded-lg bg-green-50 px-3 py-2 text-xs font-semibold text-green-700 transition hover:bg-green-100"
                                        >
                                            Edit
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div
                    v-if="purchases.data?.length"
                    class="divide-y divide-slate-100 md:hidden"
                >
                    <div
                        v-for="purchase in purchases.data"
                        :key="purchase.id"
                        class="p-5"
                    >
                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <div class="min-w-0">
                                <p
                                    class="font-mono text-sm font-bold text-slate-900"
                                >
                                    {{ purchase.reference }}
                                </p>

                                <p
                                    class="mt-1 truncate text-sm text-slate-600"
                                >
                                    {{
                                        purchase.supplier?.company_name ||
                                        purchase.supplier?.name ||
                                        'No supplier'
                                    }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    {{ formatDate(purchase.purchase_date) }}
                                </p>
                            </div>

                            <span
                                class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold"
                                :class="statusClass(purchase.status)"
                            >
                                {{ statusLabel(purchase.status) }}
                            </span>
                        </div>

                        <div
                            class="mt-4 flex items-center justify-between"
                        >
                            <div>
                                <p class="text-xs text-slate-400">
                                    {{ purchase.items_count ?? 0 }}
                                    {{
                                        (purchase.items_count ?? 0) === 1
                                            ? 'item'
                                            : 'items'
                                    }}
                                </p>

                                <p
                                    class="mt-1 font-bold text-slate-900"
                                >
                                    {{
                                        formatPrice(
                                            purchase.total_amount,
                                        )
                                    }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <Link
                                    :href="`/admin/purchases/${purchase.id}`"
                                    class="rounded-lg bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-200"
                                >
                                    View
                                </Link>

                                <Link
                                    v-if="canEdit(purchase)"
                                    :href="`/admin/purchases/${purchase.id}/edit`"
                                    class="rounded-lg bg-green-50 px-4 py-2 text-xs font-semibold text-green-700 transition hover:bg-green-100"
                                >
                                    Edit
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="
                        purchases.links &&
                        purchases.links.length > 3
                    "
                    class="flex flex-wrap items-center justify-center gap-2 border-t border-slate-200 px-6 py-5"
                >
                    <template
                        v-for="(link, index) in purchases.links"
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