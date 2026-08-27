<script setup>
import { computed, ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    expiredProducts: {
        type: Array,
        default: () => [],
    },

    expiringSoon: {
        type: Array,
        default: () => [],
    },

    expiringWithin30Days: {
        type: Array,
        default: () => [],
    },

    summary: {
        type: Object,
        default: () => ({
            expired: 0,
            expiring_7_days: 0,
            expiring_30_days: 0,
        }),
    },
})

const activeTab = ref('expired')

const showReturnModal = ref(false)
const selectedBatch = ref(null)
const returnQuantity = ref(1)
const processing = ref(false)

const tabs = computed(() => [
    {
        key: 'expired',
        label: 'Expired',
        count: props.summary.expired ?? 0,
    },
    {
        key: '7days',
        label: 'Expiring Within 7 Days',
        count: props.summary.expiring_7_days ?? 0,
    },
    {
        key: '30days',
        label: 'Expiring Within 30 Days',
        count: props.summary.expiring_30_days ?? 0,
    },
])

const currentProducts = computed(() => {
    if (activeTab.value === 'expired') {
        return props.expiredProducts
    }

    if (activeTab.value === '7days') {
        return props.expiringSoon
    }

    return props.expiringWithin30Days
})

const formatDate = (date) => {
    if (!date) {
        return '—'
    }

    return new Date(`${date}T00:00:00`).toLocaleDateString('en-NG', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
}

const openReturnModal = (batch) => {
    selectedBatch.value = batch
    returnQuantity.value = batch.quantity > 0 ? batch.quantity : 1
    showReturnModal.value = true
}

const closeReturnModal = () => {
    if (processing.value) {
        return
    }

    showReturnModal.value = false
    selectedBatch.value = null
    returnQuantity.value = 1
}

const submitReturn = () => {
    if (!selectedBatch.value) {
        return
    }

    const quantity = Number(returnQuantity.value)

    if (!Number.isInteger(quantity) || quantity < 1) {
        return
    }

    if (quantity > Number(selectedBatch.value.quantity)) {
        return
    }

    processing.value = true

    router.post(
        route(
            'admin.expiry-reminders.return-to-supplier',
            selectedBatch.value.id
        ),
        {
            quantity,
        },
        {
            preserveScroll: true,

            onFinish: () => {
                processing.value = false
            },

            onSuccess: () => {
                closeReturnModal()
            },
        }
    )
}

const markExpired = (batch) => {
    if (!batch?.id) {
        return
    }

    if (
        !window.confirm(
            `Mark "${batch.product_name}" batch ${batch.batch_number || 'N/A'} as expired and remove ${batch.quantity} unit(s) from inventory?`
        )
    ) {
        return
    }

    router.post(
        route('admin.expiry-reminders.mark-expired', batch.id),
        {},
        {
            preserveScroll: true,
        }
    )
}

const statusClasses = (status) => {
    switch (status) {
        case 'active':
            return 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'

        case 'partially_returned':
            return 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'

        case 'returned':
            return 'bg-slate-100 text-slate-700 ring-1 ring-slate-200'

        case 'expired':
            return 'bg-red-50 text-red-700 ring-1 ring-red-200'

        default:
            return 'bg-slate-100 text-slate-600 ring-1 ring-slate-200'
    }
}

const statusLabel = (status) => {
    switch (status) {
        case 'partially_returned':
            return 'Partially Returned'

        default:
            return status
                ? status.replaceAll('_', ' ').replace(/\b\w/g, (char) =>
                      char.toUpperCase()
                  )
                : 'Unknown'
    }
}
</script>

<template>
    <Head title="Expiry Reminders" />

    <AdminLayout>
        <div class="min-h-screen bg-slate-50 px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <!-- Header -->
                <div
                    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h1
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            Expiry Reminders
                        </h1>

                        <p class="mt-1 text-sm text-slate-500">
                            Monitor expired and soon-to-expire purchase
                            batches.
                        </p>
                    </div>

                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-lg bg-white px-3 py-2 text-sm text-slate-600 shadow-sm ring-1 ring-slate-200"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-emerald-500"
                        ></span>

                        Inventory monitored by purchase batch
                    </div>
                </div>

                <!-- Summary Cards -->
                <div
                    class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3"
                >
                    <div
                        class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Expired Batches
                                </p>

                                <p
                                    class="mt-2 text-3xl font-bold text-red-600"
                                >
                                    {{ summary.expired ?? 0 }}
                                </p>
                            </div>

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-lg bg-red-50 text-red-600"
                            >
                                ⚠
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Expiring Within 7 Days
                                </p>

                                <p
                                    class="mt-2 text-3xl font-bold text-amber-600"
                                >
                                    {{ summary.expiring_7_days ?? 0 }}
                                </p>
                            </div>

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-lg bg-amber-50 text-amber-600"
                            >
                                !
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Expiring Within 30 Days
                                </p>

                                <p
                                    class="mt-2 text-3xl font-bold text-blue-600"
                                >
                                    {{ summary.expiring_30_days ?? 0 }}
                                </p>
                            </div>

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
                            >
                                ⏱
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div
                    class="mb-5 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200"
                >
                    <div class="flex overflow-x-auto">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            type="button"
                            @click="activeTab = tab.key"
                            class="flex min-w-fit items-center gap-2 border-b-2 px-5 py-4 text-sm font-semibold transition"
                            :class="
                                activeTab === tab.key
                                    ? 'border-emerald-600 text-emerald-700'
                                    : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'
                            "
                        >
                            <span>{{ tab.label }}</span>

                            <span
                                class="rounded-full px-2 py-0.5 text-xs"
                                :class="
                                    activeTab === tab.key
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-slate-100 text-slate-600'
                                "
                            >
                                {{ tab.count }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="currentProducts.length === 0"
                    class="rounded-xl bg-white px-6 py-16 text-center shadow-sm ring-1 ring-slate-200"
                >
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-2xl text-emerald-600"
                    >
                        ✓
                    </div>

                    <h2 class="mt-4 text-lg font-semibold text-slate-900">
                        No batches found
                    </h2>

                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                        There are currently no purchase batches in this expiry
                        category with remaining inventory.
                    </p>
                </div>

                <!-- Desktop Table -->
                <div
                    v-else
                    class="hidden overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200 lg:block"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th
                                        class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
                                    >
                                        Product
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
                                    >
                                        Batch
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
                                    >
                                        Quantity
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
                                    >
                                        Expiry Date
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
                                    >
                                        Status
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
                                    >
                                        Supplier
                                    </th>

                                    <th
                                        class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-slate-100 bg-white"
                            >
                                <tr
                                    v-for="batch in currentProducts"
                                    :key="batch.id"
                                    class="transition hover:bg-slate-50"
                                >
                                    <td class="px-5 py-4">
                                        <div
                                            class="font-semibold text-slate-900"
                                        >
                                            {{ batch.product_name }}
                                        </div>

                                        <div
                                            v-if="batch.sku"
                                            class="mt-1 text-xs text-slate-500"
                                        >
                                            SKU: {{ batch.sku }}
                                        </div>
                                    </td>

                                    <td
                                        class="whitespace-nowrap px-5 py-4 text-sm text-slate-700"
                                    >
                                        {{ batch.batch_number || 'N/A' }}
                                    </td>

                                    <td
                                        class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-900"
                                    >
                                        {{ batch.quantity }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div
                                            class="text-sm font-medium"
                                            :class="
                                                activeTab === 'expired'
                                                    ? 'text-red-600'
                                                    : activeTab === '7days'
                                                      ? 'text-amber-600'
                                                      : 'text-blue-600'
                                            "
                                        >
                                            {{ formatDate(batch.expiry_date) }}
                                        </div>

                                        <div
                                            v-if="
                                                activeTab === 'expired' &&
                                                batch.days_expired !== null
                                            "
                                            class="mt-1 text-xs text-red-500"
                                        >
                                            {{ batch.days_expired }} day(s)
                                            expired
                                        </div>

                                        <div
                                            v-else-if="
                                                batch.days_remaining !== null
                                            "
                                            class="mt-1 text-xs text-slate-500"
                                        >
                                            {{ batch.days_remaining }} day(s)
                                            remaining
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                            :class="
                                                statusClasses(batch.status)
                                            "
                                        >
                                            {{
                                                statusLabel(batch.status)
                                            }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div
                                            class="text-sm text-slate-700"
                                        >
                                            {{
                                                batch.supplier_name ||
                                                'Unknown supplier'
                                            }}
                                        </div>

                                        <div
                                            v-if="batch.purchase_reference"
                                            class="mt-1 text-xs text-slate-500"
                                        >
                                            {{
                                                batch.purchase_reference
                                            }}
                                        </div>
                                    </td>

                                    <td
                                        class="whitespace-nowrap px-5 py-4 text-right"
                                    >
                                        <div
                                            class="flex justify-end gap-2"
                                        >
                                            <button
                                                v-if="
                                                    activeTab !== 'expired'
                                                "
                                                type="button"
                                                @click="
                                                    openReturnModal(batch)
                                                "
                                                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                                            >
                                                Return
                                            </button>

                                            <button
                                                v-if="
                                                    activeTab === 'expired'
                                                "
                                                type="button"
                                                @click="
                                                    markExpired(batch)
                                                "
                                                class="rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-red-700"
                                            >
                                                Mark Expired
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Cards -->
                <div class="space-y-4 lg:hidden">
                    <div
                        v-for="batch in currentProducts"
                        :key="batch.id"
                        class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3
                                    class="font-semibold text-slate-900"
                                >
                                    {{ batch.product_name }}
                                </h3>

                                <p
                                    v-if="batch.sku"
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    SKU: {{ batch.sku }}
                                </p>
                            </div>

                            <span
                                class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="statusClasses(batch.status)"
                            >
                                {{ statusLabel(batch.status) }}
                            </span>
                        </div>

                        <div
                            class="mt-4 grid grid-cols-2 gap-3 text-sm"
                        >
                            <div>
                                <p class="text-xs text-slate-500">
                                    Batch
                                </p>

                                <p
                                    class="mt-1 font-medium text-slate-800"
                                >
                                    {{ batch.batch_number || 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">
                                    Quantity
                                </p>

                                <p
                                    class="mt-1 font-semibold text-slate-900"
                                >
                                    {{ batch.quantity }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">
                                    Expiry Date
                                </p>

                                <p
                                    class="mt-1 font-medium"
                                    :class="
                                        activeTab === 'expired'
                                            ? 'text-red-600'
                                            : 'text-slate-800'
                                    "
                                >
                                    {{ formatDate(batch.expiry_date) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">
                                    Supplier
                                </p>

                                <p
                                    class="mt-1 font-medium text-slate-800"
                                >
                                    {{ batch.supplier_name || 'Unknown' }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="activeTab === 'expired'"
                            class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700"
                        >
                            {{ batch.days_expired }} day(s) expired
                        </div>

                        <div
                            v-else
                            class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700"
                        >
                            {{ batch.days_remaining }} day(s) remaining
                        </div>

                        <div class="mt-4 flex gap-2">
                            <button
                                v-if="activeTab !== 'expired'"
                                type="button"
                                @click="openReturnModal(batch)"
                                class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700"
                            >
                                Return to Supplier
                            </button>

                            <button
                                v-if="activeTab === 'expired'"
                                type="button"
                                @click="markExpired(batch)"
                                class="flex-1 rounded-lg bg-red-600 px-3 py-2.5 text-sm font-semibold text-white"
                            >
                                Mark as Expired
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Return Modal -->
        <div
            v-if="showReturnModal && selectedBatch"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
            @click.self="closeReturnModal"
        >
            <div
                class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl"
            >
                <div
                    class="flex items-start justify-between gap-4"
                >
                    <div>
                        <h2
                            class="text-lg font-bold text-slate-900"
                        >
                            Return to Supplier
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Return stock from this purchase batch.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeReturnModal"
                        class="text-2xl leading-none text-slate-400 hover:text-slate-700"
                    >
                        &times;
                    </button>
                </div>

                <div
                    class="mt-5 rounded-xl bg-slate-50 p-4"
                >
                    <p
                        class="font-semibold text-slate-900"
                    >
                        {{ selectedBatch.product_name }}
                    </p>

                    <div
                        class="mt-2 grid grid-cols-2 gap-3 text-sm"
                    >
                        <div>
                            <p class="text-xs text-slate-500">
                                Batch
                            </p>

                            <p
                                class="mt-1 font-medium text-slate-800"
                            >
                                {{
                                    selectedBatch.batch_number ||
                                    'N/A'
                                }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500">
                                Available
                            </p>

                            <p
                                class="mt-1 font-semibold text-slate-900"
                            >
                                {{ selectedBatch.quantity }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <label
                        for="return-quantity"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Quantity to return
                    </label>

                    <input
                        id="return-quantity"
                        v-model.number="returnQuantity"
                        type="number"
                        min="1"
                        :max="selectedBatch.quantity"
                        class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                    />

                    <p class="mt-1 text-xs text-slate-500">
                        Maximum:
                        {{ selectedBatch.quantity }} unit(s)
                    </p>
                </div>

                <div
                    class="mt-6 flex justify-end gap-3"
                >
                    <button
                        type="button"
                        @click="closeReturnModal"
                        :disabled="processing"
                        class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-50"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        @click="submitReturn"
                        :disabled="
                            processing ||
                            !Number.isInteger(Number(returnQuantity)) ||
                            Number(returnQuantity) < 1 ||
                            Number(returnQuantity) >
                                Number(selectedBatch.quantity)
                        "
                        class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{
                            processing
                                ? 'Processing...'
                                : 'Confirm Return'
                        }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>