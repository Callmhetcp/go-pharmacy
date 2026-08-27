<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    customer: {
        type: Object,
        required: true,
    },

    totalSpent: {
        type: Number,
        default: 0,
    },
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));
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

const formatDateTime = (date) => {
    if (!date) {
        return '—';
    }

    return new Intl.DateTimeFormat('en-NG', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(date));
};

const formatStatus = (value) => {
    if (!value) {
        return 'Unknown';
    }

    return String(value)
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
};

const statusClass = (status) => {
    const classes = {
        pending:
            'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-400',

        confirmed:
            'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/30 dark:text-blue-400',

        processing:
            'border-indigo-200 bg-indigo-50 text-indigo-700 dark:border-indigo-900/50 dark:bg-indigo-950/30 dark:text-indigo-400',

        completed:
            'border-green-200 bg-green-50 text-green-700 dark:border-green-900/50 dark:bg-green-950/30 dark:text-green-400',

        cancelled:
            'border-red-200 bg-red-50 text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400',

        paid:
            'border-green-200 bg-green-50 text-green-700 dark:border-green-900/50 dark:bg-green-950/30 dark:text-green-400',

        unpaid:
            'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300',
    };

    return (
        classes[status] ??
        'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'
    );
};
</script>

<template>
    <AdminLayout>
        <div
            class="min-h-screen bg-slate-50 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
        >
            <!-- Header -->
            <section
                class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <!-- Breadcrumb -->
                    <nav
                        class="mb-4 flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400"
                    >
                        <Link
                            :href="route('admin.dashboard')"
                            class="transition hover:text-green-600 dark:hover:text-green-400"
                        >
                            Admin
                        </Link>

                        <span>/</span>

                        <Link
                            :href="route('admin.customers.index')"
                            class="transition hover:text-green-600 dark:hover:text-green-400"
                        >
                            Customers
                        </Link>

                        <span>/</span>

                        <span
                            class="font-medium text-slate-700 dark:text-slate-200"
                        >
                            {{ customer.name }}
                        </span>
                    </nav>

                    <div
                        class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
                    >
                        <div>
                            <p
                                class="text-sm font-medium text-green-600 dark:text-green-400"
                            >
                                Customer Details
                            </p>

                            <h1
                                class="mt-1 text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white"
                            >
                                {{ customer.name }}
                            </h1>

                            <p
                                class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Customer since
                                {{ formatDate(customer.created_at) }}
                            </p>
                        </div>

                        <Link
                            :href="route('admin.customers.index')"
                            class="inline-flex w-fit items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-green-500 hover:text-green-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-green-500 dark:hover:text-green-400"
                        >
                            ← Back to Customers
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Content -->
            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <!-- Stats -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-sm font-medium text-slate-500 dark:text-slate-400"
                        >
                            Orders
                        </p>

                        <p
                            class="mt-2 text-2xl font-bold text-slate-950 dark:text-white"
                        >
                            {{ customer.orders_count ?? 0 }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-sm font-medium text-slate-500 dark:text-slate-400"
                        >
                            Prescriptions
                        </p>

                        <p
                            class="mt-2 text-2xl font-bold text-slate-950 dark:text-white"
                        >
                            {{ customer.prescriptions_count ?? 0 }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-sm font-medium text-slate-500 dark:text-slate-400"
                        >
                            Total Spent
                        </p>

                        <p
                            class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400"
                        >
                            {{ formatCurrency(totalSpent) }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-sm font-medium text-slate-500 dark:text-slate-400"
                        >
                            Joined
                        </p>

                        <p
                            class="mt-2 text-lg font-bold text-slate-950 dark:text-white"
                        >
                            {{ formatDate(customer.created_at) }}
                        </p>
                    </div>
                </div>

                <!-- Customer Information + Recent Orders -->
                <div class="mt-6 grid gap-6 lg:grid-cols-3">
                    <!-- Customer Information -->
                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2
                            class="text-lg font-bold text-slate-950 dark:text-white"
                        >
                            Customer Information
                        </h2>

                        <div class="mt-6 space-y-5">
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                                >
                                    Name
                                </p>

                                <p
                                    class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-200"
                                >
                                    {{ customer.name }}
                                </p>
                            </div>

                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                                >
                                    Email
                                </p>

                                <p
                                    class="mt-1 break-all text-sm font-medium text-slate-800 dark:text-slate-200"
                                >
                                    {{ customer.email }}
                                </p>
                            </div>

                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                                >
                                    Phone
                                </p>

                                <p
                                    class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-200"
                                >
                                    {{ customer.phone ?? 'Not provided' }}
                                </p>
                            </div>

                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                                >
                                    Email Verified
                                </p>

                                <span
                                    class="mt-2 inline-flex rounded-full border px-3 py-1 text-xs font-semibold"
                                    :class="
                                        customer.email_verified_at
                                            ? 'border-green-200 bg-green-50 text-green-700 dark:border-green-900/50 dark:bg-green-950/30 dark:text-green-400'
                                            : 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-400'
                                    "
                                >
                                    {{
                                        customer.email_verified_at
                                            ? 'Verified'
                                            : 'Not Verified'
                                    }}
                                </span>
                            </div>
                        </div>
                    </section>

                    <!-- Recent Orders -->
                    <section
                        class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-2"
                    >
                        <div
                            class="border-b border-slate-200 px-6 py-5 dark:border-slate-800"
                        >
                            <h2
                                class="text-lg font-bold text-slate-950 dark:text-white"
                            >
                                Recent Orders
                            </h2>
                        </div>

                        <div
                            v-if="!customer.orders?.length"
                            class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400"
                        >
                            This customer has no orders yet.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead
                                    class="bg-slate-50 dark:bg-slate-950"
                                >
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                        >
                                            Order
                                        </th>

                                        <th
                                            class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                        >
                                            Total
                                        </th>

                                        <th
                                            class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                        >
                                            Status
                                        </th>

                                        <th
                                            class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                        >
                                            Date
                                        </th>

                                        <th
                                            class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                        >
                                            Action
                                        </th>
                                    </tr>
                                </thead>

                                <tbody
                                    class="divide-y divide-slate-100 dark:divide-slate-800"
                                >
                                    <tr
                                        v-for="order in customer.orders"
                                        :key="order.id"
                                    >
                                        <td
                                            class="whitespace-nowrap px-6 py-4 font-mono text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{ order.order_number }}
                                        </td>

                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{ formatCurrency(order.total) }}
                                        </td>

                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span
                                                class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold"
                                                :class="
                                                    statusClass(order.status)
                                                "
                                            >
                                                {{
                                                    formatStatus(order.status)
                                                }}
                                            </span>
                                        </td>

                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm text-slate-500 dark:text-slate-400"
                                        >
                                            {{ formatDate(order.created_at) }}
                                        </td>

                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-right"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'admin.orders.show',
                                                        order.id,
                                                    )
                                                "
                                                class="text-sm font-semibold text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300"
                                            >
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <!-- Recent Prescriptions -->
                <section
                    class="mt-6 rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="border-b border-slate-200 px-6 py-5 dark:border-slate-800"
                    >
                        <h2
                            class="text-lg font-bold text-slate-950 dark:text-white"
                        >
                            Recent Prescriptions
                        </h2>
                    </div>

                    <div
                        v-if="!customer.prescriptions?.length"
                        class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400"
                    >
                        This customer has no prescriptions.
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-slate-50 dark:bg-slate-950">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                    >
                                        Reference
                                    </th>

                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                    >
                                        Status
                                    </th>

                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                    >
                                        Submitted
                                    </th>

                                    <th
                                        class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                    >
                                        Action
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <tr
                                    v-for="prescription in customer.prescriptions"
                                    :key="prescription.id"
                                >
                                    <td
                                        class="whitespace-nowrap px-6 py-4 font-mono text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{
                                            prescription.reference_number ??
                                            `#${prescription.id}`
                                        }}
                                    </td>

                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span
                                            class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold"
                                            :class="
                                                statusClass(
                                                    prescription.status,
                                                )
                                            "
                                        >
                                            {{
                                                formatStatus(
                                                    prescription.status,
                                                )
                                            }}
                                        </span>
                                    </td>

                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        {{
                                            formatDateTime(
                                                prescription.created_at,
                                            )
                                        }}
                                    </td>

                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-right"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'admin.prescriptions.show',
                                                    prescription.id,
                                                )
                                            "
                                            class="text-sm font-semibold text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300"
                                        >
                                            Review
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </AdminLayout>
</template>