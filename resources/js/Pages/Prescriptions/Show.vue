<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    prescription: {
        type: Object,
        required: true,
    },
});

const addingProductId = ref(null);

const prescription = computed(() => props.prescription);

const items = computed(() => prescription.value?.items ?? []);

const statusClasses = {
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

const statusClass = (status) => {
    return (
        statusClasses[status] ??
        'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'
    );
};

const readableStatus = (status) => {
    if (!status) {
        return 'Unknown';
    }

    return String(status)
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

const formatPrice = (price) => {
    if (price === null || price === undefined) {
        return 'Price unavailable';
    }

    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
    }).format(Number(price));
};

const addToCart = (item) => {
    if (!item.product || addingProductId.value) {
        return;
    }

    addingProductId.value = item.product.id;

    router.post(
        route('cart.store'),
        {
            product_id: item.product.id,
            quantity: item.quantity ?? 1,
        },
        {
            preserveScroll: true,

            onFinish: () => {
                addingProductId.value = null;
            },
        },
    );
};
</script>

<template>
    <div
        class="min-h-screen bg-slate-50 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
    >
        <!-- =========================================================
             HEADER
        ========================================================== -->

        <header
            class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
        >
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <!-- Breadcrumb -->

                <nav
                    class="mb-5 flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400"
                >
                    <Link
                        href="/"
                        class="transition hover:text-green-600 dark:hover:text-green-400"
                    >
                        Home
                    </Link>

                    <span>/</span>

                    <Link
                        href="/prescriptions"
                        class="transition hover:text-green-600 dark:hover:text-green-400"
                    >
                        My Prescriptions
                    </Link>

                    <span>/</span>

                    <span class="font-medium text-slate-700 dark:text-slate-200">
                        {{ prescription.reference_number }}
                    </span>
                </nav>

                <div
                    class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h1
                                class="text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white sm:text-3xl"
                            >
                                Prescription Details
                            </h1>

                            <span
                                class="rounded-full border px-3 py-1 text-xs font-bold"
                                :class="statusClass(prescription.status)"
                            >
                                {{ readableStatus(prescription.status) }}
                            </span>
                        </div>

                        <p
                            class="mt-2 font-mono text-sm text-slate-500 dark:text-slate-400"
                        >
                            {{ prescription.reference_number }}
                        </p>
                    </div>

                    <Link
                        href="/prescriptions"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        ← My Prescriptions
                    </Link>
                </div>
            </div>
        </header>

        <!-- =========================================================
             CONTENT
        ========================================================== -->

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- =====================================================
                     MAIN
                ====================================================== -->

                <div class="space-y-6 lg:col-span-2">
                    <!-- Prescription Information -->

                    <section
                        class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="border-b border-slate-200 px-5 py-5 dark:border-slate-800 sm:px-6"
                        >
                            <h2
                                class="text-lg font-bold text-slate-950 dark:text-white"
                            >
                                Prescription Information
                            </h2>
                        </div>

                        <div
                            class="grid gap-5 px-5 py-6 sm:grid-cols-2 sm:px-6"
                        >
                            <div>
                                <p
                                    class="text-xs font-medium text-slate-400 dark:text-slate-500"
                                >
                                    Doctor
                                </p>

                                <p
                                    class="mt-1 font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    {{
                                        prescription.doctor_name ??
                                        'Not provided'
                                    }}
                                </p>
                            </div>

                            <div>
                                <p
                                    class="text-xs font-medium text-slate-400 dark:text-slate-500"
                                >
                                    Hospital / Clinic
                                </p>

                                <p
                                    class="mt-1 font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    {{
                                        prescription.hospital_name ??
                                        'Not provided'
                                    }}
                                </p>
                            </div>

                            <div>
                                <p
                                    class="text-xs font-medium text-slate-400 dark:text-slate-500"
                                >
                                    Prescription Date
                                </p>

                                <p
                                    class="mt-1 font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    {{
                                        formatDate(
                                            prescription.prescription_date,
                                        )
                                    }}
                                </p>
                            </div>

                            <div>
                                <p
                                    class="text-xs font-medium text-slate-400 dark:text-slate-500"
                                >
                                    Submitted
                                </p>

                                <p
                                    class="mt-1 font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    {{ formatDate(prescription.created_at) }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="prescription.notes"
                            class="border-t border-slate-200 px-5 py-5 dark:border-slate-800 sm:px-6"
                        >
                            <p
                                class="text-xs font-medium text-slate-400 dark:text-slate-500"
                            >
                                Your Notes
                            </p>

                            <p
                                class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                            >
                                {{ prescription.notes }}
                            </p>
                        </div>
                    </section>

                    <!-- =================================================
                         MEDICINES
                    ================================================== -->

                    <section
                        class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex flex-col gap-2 border-b border-slate-200 px-5 py-5 dark:border-slate-800 sm:px-6"
                        >
                            <h2
                                class="text-lg font-bold text-slate-950 dark:text-white"
                            >
                                Medicines
                            </h2>

                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                Medicines reviewed and prepared by our
                                pharmacy team.
                            </p>
                        </div>

                        <!-- No medicines -->

                        <div
                            v-if="items.length === 0"
                            class="px-6 py-12 text-center"
                        >
                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                No medicines have been added to this
                                prescription yet.
                            </p>
                        </div>

                        <!-- Medicines -->

                        <div v-else class="divide-y divide-slate-200 dark:divide-slate-800">
                            <article
                                v-for="item in items"
                                :key="item.id"
                                class="p-5 sm:p-6"
                            >
                                <div
                                    class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between"
                                >
                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <h3
                                                class="text-base font-bold text-slate-950 dark:text-white"
                                            >
                                                {{ item.medicine_name }}
                                            </h3>

                                            <span
                                                v-if="item.product"
                                                class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700 dark:bg-green-950/40 dark:text-green-400"
                                            >
                                                Available
                                            </span>

                                            <span
                                                v-else
                                                class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-400"
                                            >
                                                Pharmacy team will assist
                                            </span>
                                        </div>

                                        <div
                                            class="mt-4 grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4"
                                        >
                                            <div v-if="item.dosage">
                                                <p
                                                    class="text-xs text-slate-400 dark:text-slate-500"
                                                >
                                                    Dosage
                                                </p>

                                                <p
                                                    class="mt-1 font-medium text-slate-700 dark:text-slate-200"
                                                >
                                                    {{ item.dosage }}
                                                </p>
                                            </div>

                                            <div v-if="item.frequency">
                                                <p
                                                    class="text-xs text-slate-400 dark:text-slate-500"
                                                >
                                                    Frequency
                                                </p>

                                                <p
                                                    class="mt-1 font-medium text-slate-700 dark:text-slate-200"
                                                >
                                                    {{ item.frequency }}
                                                </p>
                                            </div>

                                            <div v-if="item.duration">
                                                <p
                                                    class="text-xs text-slate-400 dark:text-slate-500"
                                                >
                                                    Duration
                                                </p>

                                                <p
                                                    class="mt-1 font-medium text-slate-700 dark:text-slate-200"
                                                >
                                                    {{ item.duration }}
                                                </p>
                                            </div>

                                            <div v-if="item.quantity">
                                                <p
                                                    class="text-xs text-slate-400 dark:text-slate-500"
                                                >
                                                    Quantity
                                                </p>

                                                <p
                                                    class="mt-1 font-medium text-slate-700 dark:text-slate-200"
                                                >
                                                    {{ item.quantity }}
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            v-if="item.instructions"
                                            class="mt-4 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/70"
                                        >
                                            <p
                                                class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                                            >
                                                Instructions
                                            </p>

                                            <p
                                                class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                                            >
                                                {{ item.instructions }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Product -->

                                    <div
                                        v-if="item.product"
                                        class="shrink-0 sm:w-44"
                                    >
                                        <div
                                            class="rounded-xl border border-slate-200 p-4 dark:border-slate-700"
                                        >
                                            <p
                                                class="text-xs text-slate-400 dark:text-slate-500"
                                            >
                                                Go Pharmacy
                                            </p>

                                            <p
                                                class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                            >
                                                {{ item.product.name }}
                                            </p>

                                            <p
                                                class="mt-2 text-lg font-extrabold text-green-600 dark:text-green-400"
                                            >
                                                {{ formatPrice(item.product.price) }}
                                            </p>

                                            <button
                                                v-if="prescription.status === 'approved'"
                                                type="button"
                                                :disabled="
                                                    addingProductId ===
                                                    item.product.id
                                                "
                                                @click="addToCart(item)"
                                                class="mt-3 w-full rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                                            >
                                                {{
                                                    addingProductId ===
                                                    item.product.id
                                                        ? 'Adding...'
                                                        : 'Add to Cart'
                                                }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </section>

                    <!-- =================================================
                         REVIEW
                    ================================================== -->

                    <section
                        v-if="
                            prescription.review_notes ||
                            prescription.rejection_reason
                        "
                        class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="border-b border-slate-200 px-5 py-5 dark:border-slate-800 sm:px-6"
                        >
                            <h2
                                class="text-lg font-bold text-slate-950 dark:text-white"
                            >
                                Pharmacy Review
                            </h2>
                        </div>

                        <div class="space-y-5 px-5 py-6 sm:px-6">
                            <div v-if="prescription.review_notes">
                                <p
                                    class="text-xs font-semibold text-slate-400 dark:text-slate-500"
                                >
                                    Review Notes
                                </p>

                                <p
                                    class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                                >
                                    {{ prescription.review_notes }}
                                </p>
                            </div>

                            <div
                                v-if="prescription.rejection_reason"
                                class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-950/30"
                            >
                                <p
                                    class="text-xs font-semibold text-red-700 dark:text-red-400"
                                >
                                    Reason for Rejection
                                </p>

                                <p
                                    class="mt-2 whitespace-pre-line text-sm leading-6 text-red-700 dark:text-red-300"
                                >
                                    {{ prescription.rejection_reason }}
                                </p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- =====================================================
                     SIDEBAR
                ====================================================== -->

                <aside class="space-y-6">
                    <!-- Status -->

                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2
                            class="text-base font-bold text-slate-950 dark:text-white"
                        >
                            Prescription Status
                        </h2>

                        <div
                            class="mt-4 rounded-xl border px-4 py-4"
                            :class="statusClass(prescription.status)"
                        >
                            <p class="text-sm font-bold">
                                {{ readableStatus(prescription.status) }}
                            </p>

                            <p class="mt-1 text-xs leading-5 opacity-80">
                                <span
                                    v-if="prescription.status === 'pending'"
                                >
                                    Your prescription has been received and is
                                    waiting for review.
                                </span>

                                <span
                                    v-else-if="
                                        prescription.status === 'under_review'
                                    "
                                >
                                    Our pharmacy team is currently reviewing
                                    your prescription.
                                </span>

                                <span
                                    v-else-if="
                                        prescription.status === 'approved'
                                    "
                                >
                                    Your prescription has been approved.
                                    Available medicines can be added to your
                                    cart.
                                </span>

                                <span
                                    v-else-if="
                                        prescription.status === 'rejected'
                                    "
                                >
                                    Our pharmacy team could not approve this
                                    prescription.
                                </span>

                                <span
                                    v-else-if="
                                        prescription.status === 'fulfilled'
                                    "
                                >
                                    This prescription has been fulfilled.
                                </span>
                            </p>
                        </div>
                    </section>

                    <!-- Uploaded File -->

                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2
                            class="text-base font-bold text-slate-950 dark:text-white"
                        >
                            Uploaded Prescription
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Your original prescription document is available
                            here.
                        </p>

                        <a
                            v-if="prescription.file_path"
                            :href="`/storage/${prescription.file_path}`"
                            target="_blank"
                            rel="noopener"
                            class="mt-4 inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                        >
                            View Prescription
                        </a>
                    </section>

                    <!-- Help -->

                    <section
                        class="rounded-2xl border border-green-100 bg-green-50 p-5 dark:border-green-900/40 dark:bg-green-950/20"
                    >
                        <h2
                            class="text-base font-bold text-green-900 dark:text-green-300"
                        >
                            Need help?
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-green-800/80 dark:text-green-300/80"
                        >
                            If a medicine is unavailable or you have questions
                            about your prescription, our pharmacy team will
                            assist you.
                        </p>
                    </section>
                </aside>
            </div>
        </main>
    </div>
</template>