<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    reviews: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            status: 'all',
        }),
    },
});

const approvingId = ref(null);
const rejectingId = ref(null);
const deletingId = ref(null);
const selectedReview = ref(null);

const applyStatus = (status) => {
    router.get(
        route('admin.reviews.index'),
        {
            status: status === 'all' ? undefined : status,
        },
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

const openReview = (review) => {
    selectedReview.value = review;
};

const closeReview = () => {
    selectedReview.value = null;
};

const approveReview = (review) => {
    if (
        approvingId.value ||
        rejectingId.value ||
        deletingId.value
    ) {
        return;
    }

    if (!confirm('Are you sure you want to approve this review?')) {
        return;
    }

    approvingId.value = review.id;

    router.post(
        route('admin.reviews.approve', review.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                approvingId.value = null;
            },
        },
    );
};

const rejectReview = (review) => {
    if (
        approvingId.value ||
        rejectingId.value ||
        deletingId.value
    ) {
        return;
    }

    if (!confirm('Are you sure you want to reject this review?')) {
        return;
    }

    rejectingId.value = review.id;

    router.post(
        route('admin.reviews.reject', review.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                rejectingId.value = null;
            },
        },
    );
};

const deleteReview = (review) => {
    if (
        approvingId.value ||
        rejectingId.value ||
        deletingId.value
    ) {
        return;
    }

    if (
        !confirm(
            'Are you sure you want to permanently delete this review?',
        )
    ) {
        return;
    }

    deletingId.value = review.id;

    router.delete(
        route('admin.reviews.destroy', review.id),
        {
            preserveScroll: true,
            onFinish: () => {
                deletingId.value = null;
            },
        },
    );
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
                        <span>Admin</span>

                    <span>/</span>

                    <span
                        class="font-medium text-slate-700 dark:text-slate-200"
                    >
                        Reviews
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
                        Customer Reviews
                    </h1>

                    <p
                        class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        Review and moderate customer feedback submitted
                        for Go Pharmacy products.
                    </p>
                </div>
            </div>
        </section>

        <!-- Content -->
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Status Filters -->
            <div
                class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            Review status
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Filter reviews by moderation status.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            @click="applyStatus('all')"
                            class="rounded-xl px-4 py-2.5 text-sm font-semibold transition"
                            :class="
                                filters.status === 'all'
                                    ? 'bg-green-600 text-white'
                                    : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700'
                            "
                        >
                            All
                        </button>

                        <button
                            type="button"
                            @click="applyStatus('pending')"
                            class="rounded-xl px-4 py-2.5 text-sm font-semibold transition"
                            :class="
                                filters.status === 'pending'
                                    ? 'bg-amber-500 text-white'
                                    : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700'
                            "
                        >
                            Pending
                        </button>

                        <button
                            type="button"
                            @click="applyStatus('approved')"
                            class="rounded-xl px-4 py-2.5 text-sm font-semibold transition"
                            :class="
                                filters.status === 'approved'
                                    ? 'bg-green-600 text-white'
                                    : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700'
                            "
                        >
                            Approved
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="!reviews?.data?.length"
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
                            d="M12 3 14.78 8.63 21 9.53l-4.5 4.38 1.06 6.2L12 17.18l-5.56 2.93 1.06-6.2L3 9.53l6.22-.9L12 3Z"
                        />
                    </svg>
                </div>

                <h2
                    class="mt-5 text-lg font-bold text-slate-950 dark:text-white"
                >
                    No reviews found
                </h2>

                <p
                    class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                >
                    There are no reviews matching the selected status.
                </p>
            </div>

            <!-- Reviews Table -->
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
                                    Product
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Rating
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Review
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Date
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-for="review in reviews.data"
                                :key="review.id"
                                class="transition hover:bg-slate-50 dark:hover:bg-slate-800/50"
                            >
                                <!-- Customer -->
                                <td class="px-6 py-5">
                                    <div
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ review.user?.name ?? 'Unknown' }}
                                    </div>

                                    <div
                                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        {{ review.user?.email ?? '—' }}
                                    </div>
                                </td>

                                <!-- Product -->
                                <td class="px-6 py-5">
                                    <div
                                        class="max-w-48 font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            review.reviewable?.name ??
                                            'Product unavailable'
                                        }}
                                    </div>
                                </td>

                                <!-- Rating -->
                                <td class="whitespace-nowrap px-6 py-5">
                                    <div
                                        class="flex items-center gap-1 text-amber-500"
                                    >
                                        <span
                                            v-for="star in 5"
                                            :key="star"
                                            class="text-sm"
                                        >
                                            {{
                                                star <= review.rating
                                                    ? '★'
                                                    : '☆'
                                            }}
                                        </span>
                                    </div>

                                    <div
                                        class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        {{ review.rating }}/5
                                    </div>
                                </td>

                                <!-- Review -->
                                <td class="px-6 py-5">
                                    <p
                                        class="max-w-sm text-sm leading-6 text-slate-600 dark:text-slate-300"
                                    >
                                        {{
                                            review.comment ||
                                            'No written comment.'
                                        }}
                                    </p>
                                </td>

                                <!-- Status -->
                                <td class="whitespace-nowrap px-6 py-5">
                                    <span
                                        v-if="review.is_approved"
                                        class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400"
                                    >
                                        Approved
                                    </span>

                                    <span
                                        v-else
                                        class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-400"
                                    >
                                        Pending
                                    </span>
                                </td>

                                <!-- Date -->
                                <td
                                    class="whitespace-nowrap px-6 py-5 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    {{ formatDate(review.created_at) }}
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-5">
                                    <div
                                        class="flex flex-wrap justify-end gap-2"
                                    >
                                        <button
                                            type="button"
                                            @click="openReview(review)"
                                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-green-500 hover:text-green-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-green-500 dark:hover:text-green-400"
                                        >
                                            View
                                        </button>

                                        <button
                                            v-if="!review.is_approved"
                                            type="button"
                                            :disabled="
                                                approvingId === review.id ||
                                                rejectingId !== null ||
                                                deletingId !== null
                                            "
                                            @click="approveReview(review)"
                                            class="rounded-xl bg-green-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            {{
                                                approvingId === review.id
                                                    ? 'Approving...'
                                                    : 'Approve'
                                            }}
                                        </button>

                                        <button
                                            v-else
                                            type="button"
                                            :disabled="
                                                approvingId !== null ||
                                                rejectingId === review.id ||
                                                deletingId !== null
                                            "
                                            @click="rejectReview(review)"
                                            class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-amber-900/50 dark:bg-amber-900/20 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                        >
                                            {{
                                                rejectingId === review.id
                                                    ? 'Rejecting...'
                                                    : 'Reject'
                                            }}
                                        </button>

                                        <button
                                            type="button"
                                            :disabled="
                                                approvingId !== null ||
                                                rejectingId !== null ||
                                                deletingId === review.id
                                            "
                                            @click="deleteReview(review)"
                                            class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30"
                                        >
                                            {{
                                                deletingId === review.id
                                                    ? 'Deleting...'
                                                    : 'Delete'
                                            }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="reviews.links?.length > 3"
                    class="flex flex-wrap items-center justify-center gap-2 border-t border-slate-200 px-6 py-5 dark:border-slate-800"
                >
                    <template
                        v-for="(link, index) in reviews.links"
                        :key="`${link.label}-${index}`"
                    >
                        <button
                            v-if="link.url"
                            type="button"
                            @click="
                                router.get(
                                    link.url,
                                    {},
                                    {
                                        preserveScroll: true,
                                        preserveState: true,
                                    },
                                )
                            "
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

        <!-- Review Details Modal -->
        <div
            v-if="selectedReview"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
            @click.self="closeReview"
        >
            <div
                class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900"
            >
                <!-- Modal Header -->
                <div
                    class="flex items-start justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-800"
                >
                    <div>
                        <p
                            class="text-sm font-medium text-green-600 dark:text-green-400"
                        >
                            Customer Review
                        </p>

                        <h2
                            class="mt-1 text-xl font-bold text-slate-950 dark:text-white"
                        >
                            Review Details
                        </h2>
                    </div>

                    <button
                        type="button"
                        @click="closeReview"
                        class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white"
                        aria-label="Close review"
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
                                d="M6 18 18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="space-y-6 px-6 py-6">
                    <!-- Customer -->
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                        >
                            Customer
                        </p>

                        <div class="mt-2">
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{
                                    selectedReview.user?.name ?? 'Unknown'
                                }}
                            </p>

                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    selectedReview.user?.email ?? '—'
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Product -->
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                        >
                            Product
                        </p>

                        <p
                            class="mt-2 font-semibold text-slate-900 dark:text-white"
                        >
                            {{
                                selectedReview.reviewable?.name ??
                                'Product unavailable'
                            }}
                        </p>
                    </div>

                    <!-- Rating and Status -->
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                            >
                                Rating
                            </p>

                            <div
                                class="mt-2 flex items-center gap-1 text-lg text-amber-500"
                            >
                                <span
                                    v-for="star in 5"
                                    :key="star"
                                >
                                    {{
                                        star <= selectedReview.rating
                                            ? '★'
                                            : '☆'
                                    }}
                                </span>

                                <span
                                    class="ml-2 text-sm font-semibold text-slate-600 dark:text-slate-300"
                                >
                                    {{ selectedReview.rating }}/5
                                </span>
                            </div>
                        </div>

                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                            >
                                Status
                            </p>

                            <div class="mt-2">
                                <span
                                    v-if="selectedReview.is_approved"
                                    class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400"
                                >
                                    Approved
                                </span>

                                <span
                                    v-else
                                    class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-400"
                                >
                                    Pending
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Comment -->
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                        >
                            Customer Comment
                        </p>

                        <div
                            class="mt-2 rounded-xl bg-slate-50 p-4 dark:bg-slate-800"
                        >
                            <p
                                class="whitespace-pre-wrap text-sm leading-7 text-slate-700 dark:text-slate-300"
                            >
                                {{
                                    selectedReview.comment ||
                                    'No written comment.'
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Date -->
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                        >
                            Submitted
                        </p>

                        <p
                            class="mt-2 text-sm text-slate-600 dark:text-slate-300"
                        >
                            {{
                                formatDate(selectedReview.created_at)
                            }}
                        </p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="flex justify-end border-t border-slate-200 px-6 py-4 dark:border-slate-800"
                >
                    <button
                        type="button"
                        @click="closeReview"
                        class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>

</template>
