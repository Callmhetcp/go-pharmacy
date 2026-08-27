<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    supplier: {
        type: Object,
        required: true,
    },
});

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
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Header -->
            <div
                class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between"
            >
                <div>
                    <Link
                        href="/admin/suppliers"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-green-600"
                    >
                        ← Back to Suppliers
                    </Link>

                    <p class="mt-6 text-sm font-semibold text-green-600">
                        Supplier Management
                    </p>

                    <h1
                        class="mt-1 text-3xl font-bold tracking-tight text-slate-950"
                    >
                        {{ supplier.name }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        View supplier information, contact details and
                        purchasing information.
                    </p>
                </div>

                <Link
                    :href="`/admin/suppliers/${supplier.id}/edit`"
                    class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-green-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700"
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
                            stroke-width="2"
                            d="M11 5h2m-1-1v2m8.5 7.5L12 21H5v-7l8.5-8.5a2.121 2.121 0 0 1 3 0l3 3a2.121 2.121 0 0 1 0 3Z"
                        />
                    </svg>

                    Edit Supplier
                </Link>
            </div>

            <div class="space-y-6">
                <!-- Supplier Overview -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7"
                >
                    <div
                        class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-green-50 text-xl font-bold text-green-600"
                            >
                                {{
                                    supplier.name
                                        ? supplier.name
                                              .charAt(0)
                                              .toUpperCase()
                                        : 'S'
                                }}
                            </div>

                            <div>
                                <h2 class="text-xl font-bold text-slate-900">
                                    {{ supplier.name }}
                                </h2>

                                <p
                                    v-if="supplier.company_name"
                                    class="mt-1 text-sm text-slate-500"
                                >
                                    {{ supplier.company_name }}
                                </p>
                            </div>
                        </div>

                        <span
                            v-if="supplier.is_active"
                            class="inline-flex w-fit items-center gap-1.5 rounded-full bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-green-500"
                            ></span>
                            Active
                        </span>

                        <span
                            v-else
                            class="inline-flex w-fit items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-slate-400"
                            ></span>
                            Inactive
                        </span>
                    </div>
                </section>

                <!-- Contact Information -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7"
                >
                    <div class="mb-7">
                        <h2 class="text-lg font-bold text-slate-900">
                            Contact Information
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Supplier contact and company details.
                        </p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Supplier Name -->
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-wide text-slate-400"
                            >
                                Supplier Name
                            </p>

                            <p class="mt-2 text-sm font-semibold text-slate-900">
                                {{ supplier.name || '—' }}
                            </p>
                        </div>

                        <!-- Company -->
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-wide text-slate-400"
                            >
                                Company Name
                            </p>

                            <p class="mt-2 text-sm font-semibold text-slate-900">
                                {{ supplier.company_name || '—' }}
                            </p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-wide text-slate-400"
                            >
                                Phone Number
                            </p>

                            <p class="mt-2 text-sm font-semibold text-slate-900">
                                {{ supplier.phone || '—' }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-wide text-slate-400"
                            >
                                Email Address
                            </p>

                            <p class="mt-2 break-all text-sm font-semibold text-slate-900">
                                {{ supplier.email || '—' }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Address Information -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7"
                >
                    <div class="mb-7">
                        <h2 class="text-lg font-bold text-slate-900">
                            Address Information
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Registered supplier location.
                        </p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <p
                                class="text-xs font-bold uppercase tracking-wide text-slate-400"
                            >
                                Address
                            </p>

                            <p
                                class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700"
                            >
                                {{ supplier.address || 'No address provided.' }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-wide text-slate-400"
                            >
                                City
                            </p>

                            <p class="mt-2 text-sm font-semibold text-slate-900">
                                {{ supplier.city || '—' }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-wide text-slate-400"
                            >
                                State
                            </p>

                            <p class="mt-2 text-sm font-semibold text-slate-900">
                                {{ supplier.state || '—' }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Notes -->
                <section
                    v-if="supplier.notes"
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7"
                >
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-slate-900">
                            Additional Notes
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Internal information about this supplier.
                        </p>
                    </div>

                    <div
                        class="rounded-xl border border-slate-200 bg-slate-50 p-5"
                    >
                        <p
                            class="whitespace-pre-line text-sm leading-6 text-slate-700"
                        >
                            {{ supplier.notes }}
                        </p>
                    </div>
                </section>

                <!-- Record Information -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7"
                >
                    <div class="mb-7">
                        <h2 class="text-lg font-bold text-slate-900">
                            Record Information
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Supplier account record details.
                        </p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-wide text-slate-400"
                            >
                                Supplier ID
                            </p>

                            <p
                                class="mt-2 font-mono text-sm font-semibold text-slate-900"
                            >
                                #{{ supplier.id }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-wide text-slate-400"
                            >
                                Status
                            </p>

                            <p class="mt-2 text-sm font-semibold text-slate-900">
                                {{
                                    supplier.is_active
                                        ? 'Active supplier'
                                        : 'Inactive supplier'
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-wide text-slate-400"
                            >
                                Created
                            </p>

                            <p class="mt-2 text-sm text-slate-700">
                                {{ formatDate(supplier.created_at) }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-wide text-slate-400"
                            >
                                Last Updated
                            </p>

                            <p class="mt-2 text-sm text-slate-700">
                                {{ formatDate(supplier.updated_at) }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Bottom Actions -->
                <div
                    class="flex flex-col-reverse gap-3 pb-4 sm:flex-row sm:justify-end"
                >
                    <Link
                        href="/admin/suppliers"
                        class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-6 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Back to Suppliers
                    </Link>

                    <Link
                        :href="`/admin/suppliers/${supplier.id}/edit`"
                        class="inline-flex h-12 items-center justify-center rounded-xl bg-green-600 px-7 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700"
                    >
                        Edit Supplier
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>