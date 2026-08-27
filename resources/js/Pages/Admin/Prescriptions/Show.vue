<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({
    layout: AdminLayout,
});

const props = defineProps({
    prescription: {
        type: Object,
        required: true,
    },

    products: {
        type: Array,
        default: () => [],
    },
});

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const showAddItem = ref(false);
const editingItemId = ref(null);
const processing = ref(false);
const creatingOrder = ref(false);

const itemForm = ref({
    product_id: '',
    medicine_name: '',
    dosage: '',
    frequency: '',
    duration: '',
    quantity: '',
    instructions: '',
});

const reviewForm = ref({
    status: props.prescription.status ?? 'pending',
    review_notes: props.prescription.review_notes ?? '',
    rejection_reason: props.prescription.rejection_reason ?? '',
});

/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const prescription = computed(() => props.prescription);

const items = computed(() => prescription.value.items ?? []);

const isApproved = computed(
    () => prescription.value.status === 'approved'
);

const isRejected = computed(
    () => prescription.value.status === 'rejected'
);

const isFulfilled = computed(
    () => prescription.value.status === 'fulfilled'
);

const hasItems = computed(
    () => items.value.length > 0
);

const totalQuantity = computed(() =>
    items.value.reduce(
        (total, item) => total + Number(item.quantity || 0),
        0
    )
);

const formattedPrescriptionDate = computed(() => {
    if (!prescription.value.prescription_date) {
        return '—';
    }

    return formatDate(
        prescription.value.prescription_date
    );
});

const formattedReviewedAt = computed(() => {
    if (!prescription.value.reviewed_at) {
        return '—';
    }

    return formatDateTime(
        prescription.value.reviewed_at
    );
});

/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

const statusClasses = {
    pending:
        'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-950/30 dark:text-amber-300',

    under_review:
        'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-950/30 dark:text-blue-300',

    approved:
        'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950/30 dark:text-emerald-300',

    rejected:
        'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20 dark:bg-red-950/30 dark:text-red-300',

    fulfilled:
        'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-500/20 dark:bg-slate-800 dark:text-slate-300',
};

const statusLabel = {
    pending: 'Pending',
    under_review: 'Under Review',
    approved: 'Approved',
    rejected: 'Rejected',
    fulfilled: 'Fulfilled',
};

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function formatStatus(status) {
    return statusLabel[status] ?? status;
}

function getStatusClass(status) {
    return (
        statusClasses[status] ??
        'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
    );
}

function formatDate(value) {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat('en-NG', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));
}

function formatDateTime(value) {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat('en-NG', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(new Date(value));
}

/*
|--------------------------------------------------------------------------
| Item Form
|--------------------------------------------------------------------------
*/

function resetItemForm() {
    itemForm.value = {
        product_id: '',
        medicine_name: '',
        dosage: '',
        frequency: '',
        duration: '',
        quantity: '',
        instructions: '',
    };

    editingItemId.value = null;
}

/*
|--------------------------------------------------------------------------
| Product Selection
|--------------------------------------------------------------------------
*/

function selectProduct() {
    const product = props.products.find(
        (item) =>
            Number(item.id) ===
            Number(itemForm.value.product_id)
    );

    if (!product) {
        return;
    }

    itemForm.value.medicine_name = product.name;
}

/*
|--------------------------------------------------------------------------
| Add Item
|--------------------------------------------------------------------------
*/

function openAddItem() {
    resetItemForm();
    showAddItem.value = true;
}

function closeAddItem() {
    showAddItem.value = false;
    resetItemForm();
}

function addItem() {
    if (processing.value) {
        return;
    }

    processing.value = true;

    router.post(
        route(
            'admin.prescriptions.items.store',
            prescription.value.id
        ),
        itemForm.value,
        {
            preserveScroll: true,

            onSuccess: () => {
                closeAddItem();
            },

            onFinish: () => {
                processing.value = false;
            },
        }
    );
}

/*
|--------------------------------------------------------------------------
| Edit Item
|--------------------------------------------------------------------------
*/

function startEditItem(item) {
    editingItemId.value = item.id;

    itemForm.value = {
        product_id: item.product_id ?? '',
        medicine_name: item.medicine_name ?? '',
        dosage: item.dosage ?? '',
        frequency: item.frequency ?? '',
        duration: item.duration ?? '',
        quantity: item.quantity ?? '',
        instructions: item.instructions ?? '',
    };

    showAddItem.value = true;
}

function updateItem() {
    if (
        processing.value ||
        !editingItemId.value
    ) {
        return;
    }

    processing.value = true;

    router.patch(
        route(
            'admin.prescriptions.items.update',
            [
                prescription.value.id,
                editingItemId.value,
            ]
        ),
        itemForm.value,
        {
            preserveScroll: true,

            onSuccess: () => {
                closeAddItem();
            },

            onFinish: () => {
                processing.value = false;
            },
        }
    );
}

/*
|--------------------------------------------------------------------------
| Delete Item
|--------------------------------------------------------------------------
*/

function deleteItem(item) {
    if (processing.value) {
        return;
    }

    if (
        !confirm(
            `Remove "${item.medicine_name}" from this prescription?`
        )
    ) {
        return;
    }

    processing.value = true;

    router.delete(
        route(
            'admin.prescriptions.items.destroy',
            [
                prescription.value.id,
                item.id,
            ]
        ),
        {
            preserveScroll: true,

            onFinish: () => {
                processing.value = false;
            },
        }
    );
}

/*
|--------------------------------------------------------------------------
| Review Prescription
|--------------------------------------------------------------------------
*/

function updatePrescription() {
    if (processing.value) {
        return;
    }

    if (
        reviewForm.value.status === 'rejected' &&
        !reviewForm.value.rejection_reason.trim()
    ) {
        alert('Please provide a rejection reason.');
        return;
    }

    processing.value = true;

    router.put(
        route(
            'admin.prescriptions.update',
            prescription.value.id
        ),
        reviewForm.value,
        {
            preserveScroll: true,

            onFinish: () => {
                processing.value = false;
            },
        }
    );
}

/*
|--------------------------------------------------------------------------
| Create Order
|--------------------------------------------------------------------------
*/

function createOrder() {
    if (creatingOrder.value) {
        return;
    }

    if (!hasItems.value) {
        alert(
            'Add at least one medicine before creating the order.'
        );

        return;
    }

    if (
        !confirm(
            'Create an order from this approved prescription?'
        )
    ) {
        return;
    }

    creatingOrder.value = true;

    router.post(
        route(
            'admin.prescriptions.create-order',
            prescription.value.id
        ),
        {},
        {
            preserveScroll: true,

            onFinish: () => {
                creatingOrder.value = false;
            },
        }
    );
}
</script>

<template>
    <Head
        :title="`Prescription ${prescription.reference_number}`"
    />

    <div class="space-y-6">
        <!-- Page Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <div
                    class="mb-2 flex flex-wrap items-center gap-2 text-sm"
                >
                    <Link
                        :href="
                            route(
                                'admin.prescriptions.index'
                            )
                        "
                        class="font-medium text-slate-500 transition hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400"
                    >
                        Prescriptions
                    </Link>

                    <span class="text-slate-400">
                        /
                    </span>

                    <span
                        class="text-slate-500 dark:text-slate-400"
                    >
                        {{ prescription.reference_number }}
                    </span>
                </div>

                <div
                    class="flex flex-wrap items-center gap-3"
                >
                    <h1
                        class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                    >
                        Prescription Details
                    </h1>

                    <span
                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                        :class="
                            getStatusClass(
                                prescription.status
                            )
                        "
                    >
                        {{
                            formatStatus(
                                prescription.status
                            )
                        }}
                    </span>
                </div>

                <p
                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                >
                    {{ prescription.reference_number }}
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <Link
                    :href="
                        route(
                            'admin.prescriptions.index'
                        )
                    "
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    Back
                </Link>

                <button
                    v-if="isApproved"
                    type="button"
                    :disabled="
                        creatingOrder || !hasItems
                    "
                    @click="createOrder"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <svg
                        v-if="creatingOrder"
                        class="h-4 w-4 animate-spin"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                            stroke="currentColor"
                            stroke-width="2"
                            class="opacity-25"
                        />

                        <path
                            d="M21 12a9 9 0 0 0-9-9"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        />
                    </svg>

                    {{
                        creatingOrder
                            ? 'Creating Order...'
                            : 'Create Order'
                    }}
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div
            class="grid gap-6 xl:grid-cols-3"
        >
            <!-- Main Column -->
            <div
                class="space-y-6 xl:col-span-2"
            >
                <!-- Customer Information -->
                <section
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="border-b border-slate-200 px-6 py-5 dark:border-slate-800"
                    >
                        <h2
                            class="text-lg font-semibold text-slate-900 dark:text-white"
                        >
                            Customer Information
                        </h2>
                    </div>

                    <div
                        class="grid gap-5 p-6 sm:grid-cols-2"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                            >
                                Customer
                            </p>

                            <p
                                class="mt-1 font-medium text-slate-900 dark:text-white"
                            >
                                {{
                                    prescription.user?.name ??
                                    'Guest Customer'
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                            >
                                Email
                            </p>

                            <p
                                class="mt-1 text-slate-900 dark:text-white"
                            >
                                {{
                                    prescription.user?.email ??
                                    '—'
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                            >
                                Doctor
                            </p>

                            <p
                                class="mt-1 text-slate-900 dark:text-white"
                            >
                                {{
                                    prescription.doctor_name ||
                                    'Not provided'
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                            >
                                Hospital / Clinic
                            </p>

                            <p
                                class="mt-1 text-slate-900 dark:text-white"
                            >
                                {{
                                    prescription.hospital_name ||
                                    'Not provided'
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                            >
                                Prescription Date
                            </p>

                            <p
                                class="mt-1 text-slate-900 dark:text-white"
                            >
                                {{ formattedPrescriptionDate }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                            >
                                Submitted
                            </p>

                            <p
                                class="mt-1 text-slate-900 dark:text-white"
                            >
                                {{
                                    formatDateTime(
                                        prescription.created_at
                                    )
                                }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Prescription Document -->
                <section
                    class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                Prescription Document
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Review the uploaded prescription
                                before approving it.
                            </p>
                        </div>

                        <a
                            v-if="prescription.file_path"
                            :href="
                                `/storage/${prescription.file_path}`
                            "
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100"
                        >
                            View Prescription
                        </a>
                    </div>

                    <div
                        v-if="prescription.notes"
                        class="mt-5 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                        >
                            Customer Notes
                        </p>

                        <p
                            class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700 dark:text-slate-300"
                        >
                            {{ prescription.notes }}
                        </p>
                    </div>
                </section>

                <!-- Medicines -->
                <section
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
                    >
                        <div>
                            <h2
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                Medicines
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{ items.length }} medicine(s)
                                ·
                                {{ totalQuantity }}
                                total unit(s)
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="openAddItem"
                            class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"
                        >
                            + Add Medicine
                        </button>
                    </div>

                    <!-- Medicines List -->
                    <div
                        v-if="hasItems"
                        class="divide-y divide-slate-200 dark:divide-slate-800"
                    >
                        <div
                            v-for="item in items"
                            :key="item.id"
                            class="p-6"
                        >
                            <div
                                class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between"
                            >
                                <div
                                    class="min-w-0 flex-1"
                                >
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <h3
                                            class="font-semibold text-slate-900 dark:text-white"
                                        >
                                            {{
                                                item.medicine_name
                                            }}
                                        </h3>

                                        <span
                                            v-if="item.product"
                                            class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-300"
                                        >
                                            Linked
                                        </span>
                                    </div>

                                    <p
                                        v-if="
                                            item.product?.sku
                                        "
                                        class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        SKU:
                                        {{ item.product.sku }}
                                    </p>

                                    <div
                                        class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
                                    >
                                        <div>
                                            <p
                                                class="text-xs font-medium text-slate-500 dark:text-slate-400"
                                            >
                                                Dosage
                                            </p>

                                            <p
                                                class="mt-1 text-sm text-slate-900 dark:text-white"
                                            >
                                                {{
                                                    item.dosage ||
                                                    '—'
                                                }}
                                            </p>
                                        </div>

                                        <div>
                                            <p
                                                class="text-xs font-medium text-slate-500 dark:text-slate-400"
                                            >
                                                Frequency
                                            </p>

                                            <p
                                                class="mt-1 text-sm text-slate-900 dark:text-white"
                                            >
                                                {{
                                                    item.frequency ||
                                                    '—'
                                                }}
                                            </p>
                                        </div>

                                        <div>
                                            <p
                                                class="text-xs font-medium text-slate-500 dark:text-slate-400"
                                            >
                                                Duration
                                            </p>

                                            <p
                                                class="mt-1 text-sm text-slate-900 dark:text-white"
                                            >
                                                {{
                                                    item.duration ||
                                                    '—'
                                                }}
                                            </p>
                                        </div>

                                        <div>
                                            <p
                                                class="text-xs font-medium text-slate-500 dark:text-slate-400"
                                            >
                                                Quantity
                                            </p>

                                            <p
                                                class="mt-1 text-sm font-semibold text-slate-900 dark:text-white"
                                            >
                                                {{ item.quantity }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        v-if="item.instructions"
                                        class="mt-5 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60"
                                    >
                                        <p
                                            class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                                        >
                                            Instructions
                                        </p>

                                        <p
                                            class="mt-1 text-sm leading-6 text-slate-700 dark:text-slate-300"
                                        >
                                            {{
                                                item.instructions
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="flex shrink-0 gap-2"
                                >
                                    <button
                                        type="button"
                                        @click="
                                            startEditItem(item)
                                        "
                                        class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        @click="
                                            deleteItem(item)
                                        "
                                        class="rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-medium text-red-600 shadow-sm transition hover:bg-red-50 dark:border-red-900/50 dark:bg-slate-900 dark:text-red-400 dark:hover:bg-red-950/30"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-else
                        class="p-10 text-center"
                    >
                        <div
                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                        >
                            +
                        </div>

                        <h3
                            class="mt-4 font-semibold text-slate-900 dark:text-white"
                        >
                            No medicines added
                        </h3>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Add the medicines from the prescription
                            before approving it.
                        </p>
                    </div>
                </section>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Review -->
                <section
                    class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        Review Prescription
                    </h2>

                    <div class="mt-5 space-y-5">
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Status
                            </label>

                            <select
                                v-model="
                                    reviewForm.status
                                "
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            >
                                <option value="pending">
                                    Pending
                                </option>

                                <option
                                    value="under_review"
                                >
                                    Under Review
                                </option>

                                <option value="approved">
                                    Approved
                                </option>

                                <option value="rejected">
                                    Rejected
                                </option>

                                <option value="fulfilled">
                                    Fulfilled
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Review Notes
                            </label>

                            <textarea
                                v-model="
                                    reviewForm.review_notes
                                "
                                rows="4"
                                placeholder="Add notes about this review..."
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                        </div>

                        <div
                            v-if="
                                reviewForm.status ===
                                'rejected'
                            "
                        >
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Rejection Reason
                            </label>

                            <textarea
                                v-model="
                                    reviewForm.rejection_reason
                                "
                                rows="4"
                                placeholder="Explain why the prescription is being rejected..."
                                class="w-full rounded-xl border border-red-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-red-900/50 dark:bg-slate-900 dark:text-white"
                            />
                        </div>

                        <button
                            type="button"
                            :disabled="processing"
                            @click="updatePrescription"
                            class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                processing
                                    ? 'Saving...'
                                    : 'Save Review'
                            }}
                        </button>
                    </div>
                </section>

                <!-- Review Information -->
                <section
                    class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        Review Information
                    </h2>

                    <div class="mt-5 space-y-5">
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                            >
                                Reviewed By
                            </p>

                            <p
                                class="mt-1 text-sm text-slate-900 dark:text-white"
                            >
                                {{
                                    prescription.reviewer
                                        ?.name ??
                                    'Not reviewed'
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                            >
                                Reviewed At
                            </p>

                            <p
                                class="mt-1 text-sm text-slate-900 dark:text-white"
                            >
                                {{ formattedReviewedAt }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Rejection -->
                <section
                    v-if="
                        isRejected &&
                        prescription.rejection_reason
                    "
                    class="rounded-xl border border-red-200 bg-red-50 p-6 shadow-sm dark:border-red-900/40 dark:bg-red-950/20"
                >
                    <h2
                        class="font-semibold text-red-800 dark:text-red-300"
                    >
                        Rejection Reason
                    </h2>

                    <p
                        class="mt-2 whitespace-pre-line text-sm leading-6 text-red-700 dark:text-red-300"
                    >
                        {{
                            prescription.rejection_reason
                        }}
                    </p>
                </section>

                <!-- Approved -->
                <section
                    v-if="isApproved"
                    class="rounded-xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20"
                >
                    <h2
                        class="font-semibold text-emerald-800 dark:text-emerald-300"
                    >
                        Prescription Approved
                    </h2>

                    <p
                        class="mt-2 text-sm leading-6 text-emerald-700 dark:text-emerald-300"
                    >
                        The prescription is approved. Once the
                        medicines have been verified, create an
                        order for the customer.
                    </p>

                    <button
                        type="button"
                        :disabled="
                            creatingOrder ||
                            !hasItems
                        "
                        @click="createOrder"
                        class="mt-4 w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{
                            creatingOrder
                                ? 'Creating Order...'
                                : 'Create Customer Order'
                        }}
                    </button>
                </section>

                <!-- Fulfilled -->
                <section
                    v-if="isFulfilled"
                    class="rounded-xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20"
                >
                    <h2
                        class="font-semibold text-emerald-800 dark:text-emerald-300"
                    >
                        Prescription Fulfilled
                    </h2>

                    <p
                        class="mt-2 text-sm leading-6 text-emerald-700 dark:text-emerald-300"
                    >
                        An order has been created from this
                        prescription and the customer can continue
                        with payment and delivery.
                    </p>
                </section>
            </div>
        </div>

        <!-- Add / Edit Medicine Modal -->
        <div
            v-if="showAddItem"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
            @click.self="closeAddItem"
        >
            <div
                class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <!-- Modal Header -->
                <div
                    class="flex items-center justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-800"
                >
                    <div>
                        <h2
                            class="text-lg font-semibold text-slate-900 dark:text-white"
                        >
                            {{
                                editingItemId
                                    ? 'Edit Medicine'
                                    : 'Add Medicine'
                            }}
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Add the medicine and prescription
                            instructions.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeAddItem"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-2xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                    >
                        ×
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="space-y-5 p-6">
                    <!-- Product -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                        >
                            Product
                        </label>

                        <select
                            v-model="
                                itemForm.product_id
                            "
                            @change="selectProduct"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        >
                            <option value="">
                                Select medicine
                            </option>

                            <option
                                v-for="product in products"
                                :key="product.id"
                                :value="product.id"
                            >
                                {{ product.name }}

                                {{
                                    product.sku
                                        ? ` — ${product.sku}`
                                        : ''
                                }}
                            </option>
                        </select>
                    </div>

                    <!-- Medicine Name -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                        >
                            Medicine Name
                        </label>

                        <input
                            v-model="
                                itemForm.medicine_name
                            "
                            type="text"
                            placeholder="Medicine name"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        />
                    </div>

                    <!-- Dosage / Frequency -->
                    <div
                        class="grid gap-5 sm:grid-cols-2"
                    >
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Dosage
                            </label>

                            <input
                                v-model="
                                    itemForm.dosage
                                "
                                type="text"
                                placeholder="e.g. 500mg"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Frequency
                            </label>

                            <input
                                v-model="
                                    itemForm.frequency
                                "
                                type="text"
                                placeholder="e.g. Twice daily"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Duration
                            </label>

                            <input
                                v-model="
                                    itemForm.duration
                                "
                                type="text"
                                placeholder="e.g. 7 days"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Quantity
                            </label>

                            <input
                                v-model="
                                    itemForm.quantity
                                "
                                type="number"
                                min="1"
                                placeholder="e.g. 20"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                        >
                            Instructions
                        </label>

                        <textarea
                            v-model="
                                itemForm.instructions
                            "
                            rows="4"
                            placeholder="e.g. Take after meals with water."
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        />
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="flex justify-end gap-3 border-t border-slate-200 px-6 py-5 dark:border-slate-800"
                >
                    <button
                        type="button"
                        @click="closeAddItem"
                        class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Cancel
                    </button>

                    <button
                        v-if="!editingItemId"
                        type="button"
                        :disabled="processing"
                        @click="addItem"
                        class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{
                            processing
                                ? 'Adding...'
                                : 'Add Medicine'
                        }}
                    </button>

                    <button
                        v-else
                        type="button"
                        :disabled="processing"
                        @click="updateItem"
                        class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{
                            processing
                                ? 'Saving...'
                                : 'Save Changes'
                        }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>