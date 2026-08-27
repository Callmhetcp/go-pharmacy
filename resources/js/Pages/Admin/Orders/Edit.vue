<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    status: props.order.status ?? 'pending',
    payment_status: props.order.payment_status ?? 'awaiting_payment',

    customer_name: props.order.customer_name ?? '',
    customer_email: props.order.customer_email ?? '',
    customer_phone: props.order.customer_phone ?? '',

    delivery_address: props.order.delivery_address ?? '',
    notes: props.order.notes ?? '',
});

const items = computed(() => props.order.items ?? []);

const subtotal = computed(() => {
    return items.value.reduce((total, item) => {
        const itemSubtotal =
            item.subtotal ??
            (
                Number(item.unit_price ?? 0) *
                Number(item.quantity ?? 0)
            );

        return total + Number(itemSubtotal);
    }, 0);
});

const deliveryFee = computed(() => {
    return Number(props.order.delivery_fee ?? 0);
});

const calculatedTotal = computed(() => {
    if (props.order.total !== null && props.order.total !== undefined) {
        return Number(props.order.total);
    }

    return subtotal.value + deliveryFee.value;
});

const formatMoney = (amount) => {
    const number = Number(amount);

    if (!Number.isFinite(number)) {
        return '₦0.00';
    }

    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(number);
};

const itemPrice = (item) => {
    return Number(item.unit_price ?? 0);
};

const itemTotal = (item) => {
    if (
        item.subtotal !== null &&
        item.subtotal !== undefined
    ) {
        return Number(item.subtotal);
    }

    return (
        itemPrice(item) *
        Number(item.quantity ?? 0)
    );
};

const submit = () => {
    form.put(`/admin/orders/${props.order.id}`);
};
</script>

<template>
    <Head :title="`Edit Order ${order.order_number ?? order.id}`" />

    <div
        class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white sm:px-6 lg:px-8"
    >
        <!-- Header -->
        <div
            class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <div
                    class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400"
                >
                    <Link
                        href="/admin/orders"
                        class="transition hover:text-green-600 dark:hover:text-green-400"
                    >
                        Orders
                    </Link>

                    <span>/</span>

                    <span class="text-slate-700 dark:text-slate-300">
                        Edit
                    </span>
                </div>

                <h1
                    class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white"
                >
                    Edit Order
                </h1>

                <p
                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                >
                    Update order details and status.
                </p>
            </div>

            <Link
                :href="`/admin/orders/${order.id}`"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-green-300 hover:text-green-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-green-700 dark:hover:text-green-400"
            >
                Back to Order
            </Link>
        </div>

        <form
            @submit.prevent="submit"
            class="space-y-6"
        >
            <!-- Status -->
            <section
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mb-6">
                    <h2
                        class="text-base font-bold text-slate-950 dark:text-white"
                    >
                        Order Status
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                    >
                        Control the current state of this order.
                    </p>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <!-- Order Status -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                        >
                            Order Status
                        </label>

                        <select
                            v-model="form.status"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:ring-green-900/30"
                        >
                            <option value="pending">
                                Pending
                            </option>

                            <option value="confirmed">
                                Confirmed
                            </option>

                            <option value="processing">
                                Processing
                            </option>

                            <option value="shipped">
                                Shipped
                            </option>

                            <option value="completed">
                                Completed
                            </option>

                            <option value="cancelled">
                                Cancelled
                            </option>
                        </select>

                        <p
                            v-if="form.errors.status"
                            class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.status }}
                        </p>
                    </div>

                    <!-- Payment Status -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                        >
                            Payment Status
                        </label>

                        <select
                            v-model="form.payment_status"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:ring-green-900/30"
                        >
                            <option value="awaiting_payment">
                                Awaiting Payment
                            </option>

                            <option value="payment_submitted">
                                Payment Submitted
                            </option>

                            <option value="paid">
                                Paid
                            </option>

                            <option value="rejected">
                                Rejected
                            </option>
                        </select>

                        <p
                            v-if="form.errors.payment_status"
                            class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.payment_status }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Customer -->
            <section
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mb-6">
                    <h2
                        class="text-base font-bold text-slate-950 dark:text-white"
                    >
                        Customer Information
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                    >
                        Update the customer's contact details.
                    </p>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <!-- Name -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                        >
                            Full Name
                        </label>

                        <input
                            v-model="form.customer_name"
                            type="text"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:ring-green-900/30"
                        />

                        <p
                            v-if="form.errors.customer_name"
                            class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.customer_name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                        >
                            Email Address
                        </label>

                        <input
                            v-model="form.customer_email"
                            type="email"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:ring-green-900/30"
                        />

                        <p
                            v-if="form.errors.customer_email"
                            class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.customer_email }}
                        </p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                        >
                            Phone Number
                        </label>

                        <input
                            v-model="form.customer_phone"
                            type="text"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:ring-green-900/30"
                        />

                        <p
                            v-if="form.errors.customer_phone"
                            class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.customer_phone }}
                        </p>
                    </div>

                    <!-- Order Number -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                        >
                            Order Number
                        </label>

                        <input
                            :value="order.order_number ?? order.id"
                            type="text"
                            disabled
                            class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400"
                        />
                    </div>
                </div>
            </section>

            <!-- Delivery -->
            <section
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mb-6">
                    <h2
                        class="text-base font-bold text-slate-950 dark:text-white"
                    >
                        Delivery Information
                    </h2>
                </div>

                <label
                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                >
                    Delivery Address
                </label>

                <textarea
                    v-model="form.delivery_address"
                    rows="4"
                    class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:ring-green-900/30"
                ></textarea>

                <p
                    v-if="form.errors.delivery_address"
                    class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                >
                    {{ form.errors.delivery_address }}
                </p>
            </section>

            <!-- Order Items -->
            <section
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="border-b border-slate-200 p-6 dark:border-slate-800"
                >
                    <h2
                        class="text-base font-bold text-slate-950 dark:text-white"
                    >
                        Order Items
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                    >
                        Products included in this order.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left">
                        <thead
                            class="bg-slate-50 dark:bg-slate-800/60"
                        >
                            <tr>
                                <th
                                    class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                >
                                    Product
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                >
                                    Quantity
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                >
                                    Unit Price
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                >
                                    Total
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-for="item in items"
                                :key="item.id"
                            >
                                <!-- Product -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800"
                                        >
                                            <img
                                                v-if="item.product?.image"
                                                :src="`/storage/${item.product.image}`"
                                                :alt="item.product?.name ?? item.product_name"
                                                class="h-full w-full object-cover"
                                            />

                                            <span
                                                v-else
                                                class="text-lg"
                                            >
                                                💊
                                            </span>
                                        </div>

                                        <div>
                                            <div
                                                class="font-semibold text-slate-900 dark:text-white"
                                            >
                                                {{
                                                    item.product?.name ??
                                                    item.product_name ??
                                                    'Product'
                                                }}
                                            </div>

                                            <div
                                                v-if="
                                                    item.product?.sku ??
                                                    item.product_sku
                                                "
                                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                SKU:
                                                {{
                                                    item.product?.sku ??
                                                    item.product_sku
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Quantity -->
                                <td
                                    class="px-6 py-4 text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    {{ item.quantity }}
                                </td>

                                <!-- Unit Price -->
                                <td
                                    class="px-6 py-4 text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    {{ formatMoney(itemPrice(item)) }}
                                </td>

                                <!-- Total -->
                                <td
                                    class="px-6 py-4 text-right text-sm font-bold text-slate-900 dark:text-white"
                                >
                                    {{ formatMoney(itemTotal(item)) }}
                                </td>
                            </tr>

                            <tr v-if="items.length === 0">
                                <td
                                    colspan="4"
                                    class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400"
                                >
                                    No order items found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div
                    class="border-t border-slate-200 p-6 dark:border-slate-800"
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="text-sm font-semibold text-slate-500 dark:text-slate-400"
                        >
                            Order Subtotal
                        </span>

                        <span
                            class="text-lg font-bold text-slate-950 dark:text-white"
                        >
                            {{ formatMoney(order.subtotal ?? subtotal) }}
                        </span>
                    </div>

                    <div
                        v-if="deliveryFee > 0"
                        class="mt-3 flex items-center justify-between"
                    >
                        <span
                            class="text-sm font-semibold text-slate-500 dark:text-slate-400"
                        >
                            Delivery Fee
                        </span>

                        <span
                            class="text-sm font-semibold text-slate-900 dark:text-slate-200"
                        >
                            {{ formatMoney(deliveryFee) }}
                        </span>
                    </div>

                    <div
                        class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-800"
                    >
                        <span
                            class="text-base font-bold text-slate-950 dark:text-white"
                        >
                            Total
                        </span>

                        <span
                            class="text-xl font-extrabold text-green-600 dark:text-green-400"
                        >
                            {{ formatMoney(calculatedTotal) }}
                        </span>
                    </div>
                </div>
            </section>

            <!-- Notes -->
            <section
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mb-6">
                    <h2
                        class="text-base font-bold text-slate-950 dark:text-white"
                    >
                        Admin Notes
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                    >
                        Add internal notes about this order.
                    </p>
                </div>

                <textarea
                    v-model="form.notes"
                    rows="5"
                    placeholder="Enter internal order notes..."
                    class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:ring-green-900/30"
                ></textarea>

                <p
                    v-if="form.errors.notes"
                    class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                >
                    {{ form.errors.notes }}
                </p>
            </section>

            <!-- Actions -->
            <div
                class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
            >
                <Link
                    :href="`/admin/orders/${order.id}`"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    Cancel
                </Link>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center rounded-xl bg-green-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span v-if="form.processing">
                        Saving...
                    </span>

                    <span v-else>
                        Save Changes
                    </span>
                </button>
            </div>
        </form>
    </div>
</template>
