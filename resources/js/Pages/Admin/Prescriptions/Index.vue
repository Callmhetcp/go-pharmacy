<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    prescriptions: {
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

const search = ref(props.filters?.search ?? '');
const status = ref(props.filters?.status ?? '');

const prescriptionList = computed(() => {
    return props.prescriptions?.data ?? [];
});

const formatStatus = (value) => {
    if (!value) {
        return 'Unknown';
    }

    return String(value)
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
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

const statusClass = (value) => {
    const classes = {
        pending:
            'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-400',

        under_review:
            'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/30 dark:text-blue-400',

        approved:
            'border-green-200 bg-green-50 text-green-700 dark:border-green-900/50 dark:bg-green-950/30 dark:text-green-400',

        rejected:
            'border-red-200 bg-red-50 text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400',

        fulfilled:
            'border-purple-200 bg-purple-50 text-purple-700 dark:border-purple-900/50 dark:bg-purple-950/30 dark:text-purple-400',
    };

    return (
        classes[value] ??
        'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'
    );
};

const applyFilters = () => {
    router.get(
        route('admin.prescriptions.index'),
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
};

const clearFilters = () => {
    search.value = '';
    status.value = '';

    router.get(
        route('admin.prescriptions.index'),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};
</script>

<template>
    <AdminLayout>
        <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
            <!-- Page Header -->
            <div
                class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-sm font-semibold text-green-600 dark:text-green-400"
                            >
                                Pharmacy Management
                            </p>

                            <h1
                                class="mt-1 text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white"
                            >
                                Prescriptions
                            </h1>

                            <p
                                class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Review and manage customer prescription
                                submissions.
                            </p>
                        </div>

                        <div
                            class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 dark:border-green-900/40 dark:bg-green-950/20"
                        >
                            <p
                                class="text-xs font-medium text-green-700 dark:text-green-400"
                            >
                                Total Prescriptions
                            </p>

                            <p
                                class="mt-1 text-2xl font-bold text-green-800 dark:text-green-300"
                            >
                                {{ prescriptions?.total ?? 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div
                    class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="grid gap-4 md:grid-cols-[1fr_220px_auto_auto]"
                    >
                        <!-- Search -->
                        <div>
                            <label
                                for="search"
                                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                            >
                                Search
                            </label>

                            <input
                                id="search"
                                v-model="search"
                                type="search"
                                placeholder="Reference, customer, doctor..."
                                @keyup.enter="applyFilters"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500"
                            />
                        </div>

                        <!-- Status -->
                        <div>
                            <label
                                for="status"
                                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                            >
                                Status
                            </label>

                            <select
                                id="status"
                                v-model="status"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            >
                                <option value="">All statuses</option>

                                <option
                                    v-for="item in statuses"
                                    :key="item"
                                    :value="item"
                                >
                                    {{ formatStatus(item) }}
                                </option>
                            </select>
                        </div>

                        <!-- Filter -->
                        <div class="flex items-end">
                            <button
                                type="button"
                                @click="applyFilters"
                                class="w-full rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-green-500/20 md:w-auto"
                            >
                                Filter
                            </button>
                        </div>

                        <!-- Clear -->
                        <div class="flex items-end">
                            <button
                                type="button"
                                @click="clearFilters"
                                class="w-full rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 md:w-auto"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="prescriptionList.length === 0"
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
                                d="M9 12h6m-6 4h6M8 3h8l3 3v14H5V3h3Zm4 0v4h4"
                            />
                        </svg>
                    </div>

                    <h2
                        class="mt-5 text-lg font-bold text-slate-950 dark:text-white"
                    >
                        No prescriptions found
                    </h2>

                    <p
                        class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                    >
                        There are currently no prescription submissions
                        matching your filters.
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
                                        Prescription
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                    >
                                        Customer
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                    >
                                        Doctor / Hospital
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                    >
                                        Items
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                    >
                                        Status
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
                                    v-for="prescription in prescriptionList"
                                    :key="prescription.id"
                                    class="transition hover:bg-slate-50 dark:hover:bg-slate-800/50"
                                >
                                    <!-- Prescription -->
                                    <td class="whitespace-nowrap px-6 py-5">
                                        <div
                                            class="font-mono text-sm font-bold text-slate-950 dark:text-white"
                                        >
                                            {{
                                                prescription.reference_number ??
                                                '—'
                                            }}
                                        </div>

                                        <div
                                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                formatDate(
                                                    prescription.created_at,
                                                )
                                            }}
                                        </div>
                                    </td>

                                    <!-- Customer -->
                                    <td class="px-6 py-5">
                                        <div
                                            class="font-semibold text-slate-800 dark:text-slate-100"
                                        >
                                            {{
                                                prescription.user?.name ??
                                                'Guest Customer'
                                            }}
                                        </div>

                                        <div
                                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                prescription.user?.email ??
                                                'No email'
                                            }}
                                        </div>
                                    </td>

                                    <!-- Doctor / Hospital -->
                                    <td class="px-6 py-5">
                                        <div
                                            class="font-medium text-slate-800 dark:text-slate-200"
                                        >
                                            {{
                                                prescription.doctor_name ??
                                                'Not provided'
                                            }}
                                        </div>

                                        <div
                                            class="mt-1 max-w-xs truncate text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                prescription.hospital_name ??
                                                'Hospital not provided'
                                            }}
                                        </div>
                                    </td>

                                    <!-- Items -->
                                    <td class="whitespace-nowrap px-6 py-5">
                                        <span
                                            class="font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            {{
                                                prescription.items_count ?? 0
                                            }}
                                        </span>

                                        <span
                                            class="ml-1 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            item(s)
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="whitespace-nowrap px-6 py-5">
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

                                    <!-- Action -->
                                    <td
                                        class="whitespace-nowrap px-6 py-5 text-right"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'admin.prescriptions.show',
                                                    prescription.id,
                                                )
                                            "
                                            class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-green-500 hover:text-green-700 focus:outline-none focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-green-500 dark:hover:text-green-400"
                                        >
                                            Review
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="prescriptions?.links?.length > 3"
                        class="flex flex-wrap items-center justify-center gap-2 border-t border-slate-200 px-6 py-5 dark:border-slate-800"
                    >
                        <template
                            v-for="(link, index) in prescriptions.links"
                            :key="`${link.label}-${index}`"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-scroll
                                class="rounded-lg border px-3 py-2 text-sm font-medium transition focus:outline-none focus:ring-4 focus:ring-green-500/10"
                                :class="
                                    link.active
                                        ? 'border-green-600 bg-green-600 text-white'
                                        : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700'
                                "
                                v-html="link.label"
                            />

                            <span
                                v-else
                                class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-400 dark:border-slate-800 dark:text-slate-600"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </main>
        </div>
    </AdminLayout>
</template>
