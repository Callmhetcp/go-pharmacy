<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    company_name: '',
    phone: '',
    email: '',
    address: '',
    city: '',
    state: '',
    notes: '',
    is_active: true,
});

const submit = () => {
    form.post('/admin/suppliers');
};
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="mb-8">
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
                    Add Supplier
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Add a new supplier to your pharmacy supplier directory.
                </p>
            </div>

            <form
                @submit.prevent="submit"
                class="space-y-6"
            >

                <!-- Supplier Information -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-slate-900">
                            Supplier Information
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Basic contact and company information for this supplier.
                        </p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">

                        <!-- Supplier Name -->
                        <div>
                            <label
                                for="name"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Supplier Name
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. Chinedu Okafor"
                                class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p
                                v-if="form.errors.name"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Company Name -->
                        <div>
                            <label
                                for="company_name"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Company Name
                            </label>

                            <input
                                id="company_name"
                                v-model="form.company_name"
                                type="text"
                                placeholder="e.g. Emzor Pharmaceutical Industries"
                                class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p
                                v-if="form.errors.company_name"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.company_name }}
                            </p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label
                                for="phone"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Phone Number
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                placeholder="e.g. 08012345678"
                                class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p
                                v-if="form.errors.phone"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.phone }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label
                                for="email"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Email Address
                            </label>

                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="e.g. supplier@example.com"
                                class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p
                                v-if="form.errors.email"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Address Information -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-slate-900">
                            Address Information
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Add the supplier's business or contact location.
                        </p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">

                        <!-- Address -->
                        <div class="md:col-span-2">
                            <label
                                for="address"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Address
                            </label>

                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="4"
                                placeholder="Enter supplier address..."
                                class="w-full resize-none rounded-xl border border-slate-300 px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            ></textarea>

                            <p
                                v-if="form.errors.address"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.address }}
                            </p>
                        </div>

                        <!-- City -->
                        <div>
                            <label
                                for="city"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                City
                            </label>

                            <input
                                id="city"
                                v-model="form.city"
                                type="text"
                                placeholder="e.g. Port Harcourt"
                                class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p
                                v-if="form.errors.city"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.city }}
                            </p>
                        </div>

                        <!-- State -->
                        <div>
                            <label
                                for="state"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                State
                            </label>

                            <input
                                id="state"
                                v-model="form.state"
                                type="text"
                                placeholder="e.g. Rivers State"
                                class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            />

                            <p
                                v-if="form.errors.state"
                                class="mt-2 text-xs font-medium text-red-600"
                            >
                                {{ form.errors.state }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Additional Information -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-slate-900">
                            Additional Information
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Add any notes or information that may be useful later.
                        </p>
                    </div>

                    <div>
                        <label
                            for="notes"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Notes
                        </label>

                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="5"
                            placeholder="Add supplier notes, payment terms, delivery information, or other useful details..."
                            class="w-full resize-none rounded-xl border border-slate-300 px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-100"
                        ></textarea>

                        <p
                            v-if="form.errors.notes"
                            class="mt-2 text-xs font-medium text-red-600"
                        >
                            {{ form.errors.notes }}
                        </p>
                    </div>
                </section>

                <!-- Supplier Status -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-slate-900">
                            Supplier Status
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Control whether this supplier is currently available.
                        </p>
                    </div>

                    <label class="flex cursor-pointer items-start gap-3">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                        />

                        <span>
                            <span class="block text-sm font-semibold text-slate-800">
                                Active supplier
                            </span>

                            <span class="mt-1 block text-xs leading-5 text-slate-500">
                                Keep this supplier active and available for
                                purchasing and inventory records.
                            </span>
                        </span>
                    </label>

                    <p
                        v-if="form.errors.is_active"
                        class="mt-2 text-xs font-medium text-red-600"
                    >
                        {{ form.errors.is_active }}
                    </p>
                </section>

                <!-- Actions -->
                <div
                    class="flex flex-col-reverse gap-3 pb-4 sm:flex-row sm:justify-end"
                >
                    <Link
                        href="/admin/suppliers"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Cancel
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center rounded-xl bg-green-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{
                            form.processing
                                ? 'Creating Supplier...'
                                : 'Create Supplier'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>