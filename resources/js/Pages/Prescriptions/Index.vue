<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const props = defineProps({
    prescriptions: {
        type: Object,
        required: true,
    },
});

const prescriptionList = computed(() => {
    return props.prescriptions?.data ?? [];
});

const prescriptionCount = computed(() => {
    return props.prescriptions?.total ?? prescriptionList.value.length;
});

const statusClass = (status) => {
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
        classes[status] ??
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
</script>

<template>
    <CustomerLayout>
        <main
            class="min-h-screen bg-slate-50 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
        >
            <!-- =====================================================
                 PAGE HEADER
            ====================================================== -->
            <section
                class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <!-- Breadcrumb -->
                    <nav
                        class="mb-5 flex items-center gap-2 text-sm"
                        aria-label="Breadcrumb"
                    >
                        <Link
                            :href="route('home')"
                            class="font-medium text-slate-500 transition hover:text-green-600 dark:text-slate-400 dark:hover:text-green-400"
                        >
                            Home
                        </Link>

                        <svg
                            class="h-4 w-4 text-slate-400 dark:text-slate-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="m9 18 6-6-6-6"
                            />
                        </svg>

                        <span
                            class="font-semibold text-slate-900 dark:text-white"
                            aria-current="page"
                        >
                            My Prescriptions
                        </span>
                    </nav>

                    <div
                        class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <p
                                class="mb-2 text-sm font-semibold text-green-600 dark:text-green-400"
                            >
                                Customer Account
                            </p>

                            <h1
                                class="text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white sm:text-4xl"
                            >
                                My Prescriptions
                            </h1>

                            <p
                                class="mt-3 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400 sm:text-base"
                            >
                                View and track your prescription submissions,
                                reviews, and prescribed medicines.
                            </p>
                        </div>

                        <Link
                            :href="route('prescriptions.create')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700"
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
                                    d="M12 16V4m0 0-4 4m4-4 4 4M5 16v3h14v-3"
                                />
                            </svg>

                            Upload Prescription
                        </Link>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 CONTENT
            ====================================================== -->
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <!-- =================================================
                     STAT CARD
                ================================================== -->
                <div
                    class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400"
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
                                    d="M9 12h6m-6 4h6M8 3h8l3 3v14H5V3h3Zm4 0v4h4"
                                />
                            </svg>
                        </div>

                        <div>
                            <p
                                class="text-2xl font-extrabold text-slate-950 dark:text-white"
                            >
                                {{ prescriptionCount }}
                            </p>

                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                Prescription submission{{
                                    prescriptionCount === 1 ? '' : 's'
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- =================================================
                     EMPTY STATE
                ================================================== -->
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
                        No prescriptions yet
                    </h2>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        Upload your doctor's prescription and our pharmacy
                        team will review it before your medicines are
                        prepared.
                    </p>

                    <Link
                        :href="route('prescriptions.create')"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700"
                    >
                        Upload Your Prescription
                    </Link>
                </div>

                <!-- =================================================
                     PRESCRIPTION LIST
                ================================================== -->
                <div v-else class="space-y-4">
                    <article
                        v-for="prescription in prescriptionList"
                        :key="prescription.id"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="p-5 sm:p-6">
                            <div
                                class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
                            >
                                <div class="min-w-0 flex-1">
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <span
                                            class="font-mono text-sm font-bold text-slate-950 dark:text-white"
                                        >
                                            {{
                                                prescription.reference_number
                                            }}
                                        </span>

                                        <span
                                            class="rounded-full border px-2.5 py-1 text-xs font-semibold"
                                            :class="
                                                statusClass(
                                                    prescription.status,
                                                )
                                            "
                                        >
                                            {{
                                                readableStatus(
                                                    prescription.status,
                                                )
                                            }}
                                        </span>
                                    </div>

                                    <div
                                        class="mt-4 grid gap-4 text-sm sm:grid-cols-3"
                                    >
                                        <div>
                                            <p
                                                class="text-xs text-slate-400 dark:text-slate-500"
                                            >
                                                Doctor
                                            </p>

                                            <p
                                                class="mt-1 font-medium text-slate-700 dark:text-slate-200"
                                            >
                                                {{
                                                    prescription.doctor_name ??
                                                    'Not provided'
                                                }}
                                            </p>
                                        </div>

                                        <div>
                                            <p
                                                class="text-xs text-slate-400 dark:text-slate-500"
                                            >
                                                Hospital / Clinic
                                            </p>

                                            <p
                                                class="mt-1 font-medium text-slate-700 dark:text-slate-200"
                                            >
                                                {{
                                                    prescription.hospital_name ??
                                                    'Not provided'
                                                }}
                                            </p>
                                        </div>

                                        <div>
                                            <p
                                                class="text-xs text-slate-400 dark:text-slate-500"
                                            >
                                                Prescription Date
                                            </p>

                                            <p
                                                class="mt-1 font-medium text-slate-700 dark:text-slate-200"
                                            >
                                                {{
                                                    formatDate(
                                                        prescription.prescription_date,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <p
                                        v-if="prescription.notes"
                                        class="mt-4 line-clamp-2 text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        {{ prescription.notes }}
                                    </p>
                                </div>

                                <Link
                                    :href="
                                        route(
                                            'prescriptions.show',
                                            prescription.id,
                                        )
                                    "
                                    class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                >
                                    View Prescription

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
                                            d="m9 18 6-6-6-6"
                                        />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- =================================================
                     PAGINATION
                ================================================== -->
                <div
                    v-if="props.prescriptions?.links?.length > 3"
                    class="mt-6 flex flex-wrap justify-center gap-2"
                >
                    <template
                        v-for="(link, index) in props.prescriptions.links"
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
                                    : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800'
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
    </CustomerLayout>
</template>
