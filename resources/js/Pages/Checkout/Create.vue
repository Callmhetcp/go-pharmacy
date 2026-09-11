<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const props = defineProps({
    cart: {
        type: Array,
        default: () => [],
    },

    deliveryFee: {
        type: [Number, String],
        default: 0,
    },

    user: {
        type: Object,
        default: null,
    },

    savedAddresses: {
        type: Array,
        default: () => [],
    },
});

const defaultAddress = props.savedAddresses.find(
    (address) => address.is_default
) ?? props.savedAddresses[0] ?? null;

const form = useForm({
    customer_name: props.user?.name ?? '',
    customer_email: props.user?.email ?? '',
    customer_phone: defaultAddress?.phone ?? props.user?.phone ?? '',

    delivery_address: defaultAddress?.address ?? '',
    delivery_city: defaultAddress?.city ?? '',
    delivery_state: defaultAddress?.state ?? '',
    delivery_notes: defaultAddress?.delivery_notes ?? '',

    notes: '',

    saved_address_id: defaultAddress?.id ?? null,
    address_label: defaultAddress?.label ?? 'Delivery address',
    save_address: false,
    update_saved_address: false,
});

const selectSavedAddress = (address) => {
    form.saved_address_id = address.id;
    form.address_label = address.label ?? 'Delivery address';
    form.customer_name = address.recipient_name;
    form.customer_phone = address.phone;
    form.delivery_address = address.address;
    form.delivery_city = address.city;
    form.delivery_state = address.state;
    form.delivery_notes = address.delivery_notes ?? '';
    form.save_address = false;
    form.update_saved_address = false;
};

const useNewAddress = () => {
    form.saved_address_id = null;
    form.address_label = 'Delivery address';
    form.delivery_address = '';
    form.delivery_city = '';
    form.delivery_state = '';
    form.delivery_notes = '';
    form.save_address = false;
    form.update_saved_address = false;
};

const updateSelectedAddress = () => {
    if (form.update_saved_address) {
        form.save_address = true;
    }
};

/*
|--------------------------------------------------------------------------
| Cart helpers
|--------------------------------------------------------------------------
*/

const getProduct = (item) => {
    return item?.product ?? item ?? {};
};

const getQuantity = (item) => {
    return Number(item?.quantity ?? 1);
};

const getPrice = (item) => {
    const product = getProduct(item);

    return Number(
        item?.unit_price ??
        item?.price ??
        product?.price ??
        0
    );
};

const getName = (item) => {
    const product = getProduct(item);

    return (
        item?.product_name ??
        item?.name ??
        product?.name ??
        'Product'
    );
};

const getImage = (item) => {
    const product = getProduct(item);

    return (
        item?.image ??
        product?.image ??
        null
    );
};

const imageUrl = (image) => {
    if (!image) {
        return null;
    }

    if (
        image.startsWith('http://') ||
        image.startsWith('https://') ||
        image.startsWith('/storage/')
    ) {
        return image;
    }

    return `/storage/${image}`;
};

/*
|--------------------------------------------------------------------------
| Totals
|--------------------------------------------------------------------------
*/

const subtotal = computed(() => {
    return props.cart.reduce((total, item) => {
        return total + (
            getPrice(item) *
            getQuantity(item)
        );
    }, 0);
});

const delivery = computed(() => {
    return Number(props.deliveryFee ?? 0);
});

const total = computed(() => {
    return Math.max(
        0,
        subtotal.value + delivery.value
    );
});

/*
|--------------------------------------------------------------------------
| Formatting
|--------------------------------------------------------------------------
*/

const formatMoney = (value) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
    }).format(Number(value ?? 0));
};

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = () => {
    form.post('/checkout', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Checkout" />

    <CustomerLayout>
        <div
            class="min-h-screen bg-slate-50 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
        >
            <!-- =====================================================
                 PAGE HEADER
            ====================================================== -->

            <section
                class="border-b border-slate-200 bg-white transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.2em] text-green-600 dark:text-green-400"
                            >
                                Go Pharmacy
                            </p>

                            <h1
                                class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white"
                            >
                                Checkout
                            </h1>

                            <p
                                class="mt-2 max-w-xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                Enter your delivery information to complete
                                your order.
                            </p>
                        </div>

                        <Link
                            :href="route('cart.index')"
                            class="inline-flex w-fit items-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-green-300 hover:text-green-600 dark:border-slate-700 dark:text-slate-200 dark:hover:border-green-700 dark:hover:text-green-400"
                        >
                            ← Back to Cart
                        </Link>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 MAIN
            ====================================================== -->

            <main
                class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12"
            >
                <form
                    @submit.prevent="submit"
                    class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_400px]"
                >
                    <!-- =================================================
                         LEFT COLUMN
                    ================================================== -->

                    <div class="space-y-6">

                        <!-- Contact Information -->

                        <section
                            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900 sm:p-8"
                        >
                            <div class="mb-6">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100 text-green-600 dark:bg-green-950/50 dark:text-green-400"
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
                                            d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM4 21a8 8 0 0 1 16 0"
                                        />
                                    </svg>
                                </div>

                                <h2
                                    class="mt-4 text-lg font-bold text-slate-950 dark:text-white"
                                >
                                    Contact Information
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    How we can contact you about your order.
                                </p>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">

                                <!-- Full Name -->

                                <div class="sm:col-span-2">
                                    <label
                                        for="customer_name"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Full Name
                                    </label>

                                    <input
                                        id="customer_name"
                                        v-model="form.customer_name"
                                        type="text"
                                        autocomplete="name"
                                        placeholder="Enter your full name"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                    />

                                    <p
                                        v-if="form.errors.customer_name"
                                        class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400"
                                    >
                                        {{ form.errors.customer_name }}
                                    </p>
                                </div>

                                <!-- Email -->

                                <div>
                                    <label
                                        for="customer_email"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Email Address
                                    </label>

                                    <input
                                        id="customer_email"
                                        v-model="form.customer_email"
                                        type="email"
                                        autocomplete="email"
                                        placeholder="you@example.com"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                    />

                                    <p
                                        v-if="form.errors.customer_email"
                                        class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400"
                                    >
                                        {{ form.errors.customer_email }}
                                    </p>
                                </div>

                                <!-- Phone -->

                                <div>
                                    <label
                                        for="customer_phone"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Phone Number
                                    </label>

                                    <input
                                        id="customer_phone"
                                        v-model="form.customer_phone"
                                        type="tel"
                                        autocomplete="tel"
                                        placeholder="08012345678"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                    />

                                    <p
                                        v-if="form.errors.customer_phone"
                                        class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400"
                                    >
                                        {{ form.errors.customer_phone }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- Delivery Information -->

                        <section
                            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900 sm:p-8"
                        >
                            <div class="mb-6">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100 text-green-600 dark:bg-green-950/50 dark:text-green-400"
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
                                            d="M3 7h11v10H3V7Zm11 3h4l3 3v4h-7v-7Zm-7 9a2 2 0 1 1-4 0m11 0a2 2 0 1 1-4 0"
                                        />
                                    </svg>
                                </div>

                                <h2
                                    class="mt-4 text-lg font-bold text-slate-950 dark:text-white"
                                >
                                    Delivery Information
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Where should we deliver your order?
                                </p>
                            </div>

                            <div
                                v-if="savedAddresses.length"
                                class="mb-6 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                            Saved delivery details
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            Select one to prefill checkout, then edit the form if needed.
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        @click="useNewAddress"
                                        class="shrink-0 text-xs font-semibold text-green-600 hover:text-green-700 dark:text-green-400"
                                    >
                                        Use new address
                                    </button>
                                </div>

                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <button
                                        v-for="address in savedAddresses"
                                        :key="address.id"
                                        type="button"
                                        @click="selectSavedAddress(address)"
                                        class="rounded-lg border p-3 text-left transition"
                                        :class="
                                            form.saved_address_id === address.id
                                                ? 'border-green-500 bg-green-50 ring-2 ring-green-500/20 dark:bg-green-950/30'
                                                : 'border-slate-200 bg-white hover:border-green-300 dark:border-slate-700 dark:bg-slate-900'
                                        "
                                    >
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-sm font-semibold text-slate-900 dark:text-white">
                                                {{ address.label || 'Delivery address' }}
                                            </span>

                                            <span
                                                v-if="address.is_default"
                                                class="rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-green-700 dark:bg-green-950 dark:text-green-300"
                                            >
                                                Default
                                            </span>
                                        </div>

                                        <p class="mt-2 text-xs leading-5 text-slate-600 dark:text-slate-300">
                                            {{ address.recipient_name }} · {{ address.phone }}<br>
                                            {{ address.address }}, {{ address.city }}, {{ address.state }}
                                        </p>
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-5">

                                <!-- Address -->

                                <div>
                                    <label
                                        for="delivery_address"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Delivery Address
                                    </label>

                                    <textarea
                                        id="delivery_address"
                                        v-model="form.delivery_address"
                                        rows="3"
                                        placeholder="Enter your full delivery address"
                                        class="w-full resize-none rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                    ></textarea>

                                    <p
                                        v-if="form.errors.delivery_address"
                                        class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400"
                                    >
                                        {{ form.errors.delivery_address }}
                                    </p>
                                </div>

                                <div class="grid gap-5 sm:grid-cols-2">

                                    <!-- City -->

                                    <div>
                                        <label
                                            for="delivery_city"
                                            class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            City
                                        </label>

                                        <input
                                            id="delivery_city"
                                            v-model="form.delivery_city"
                                            type="text"
                                            placeholder="City"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                        />

                                        <p
                                            v-if="form.errors.delivery_city"
                                            class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400"
                                        >
                                            {{ form.errors.delivery_city }}
                                        </p>
                                    </div>

                                    <!-- State -->

                                    <div>
                                        <label
                                            for="delivery_state"
                                            class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            State
                                        </label>

                                        <input
                                            id="delivery_state"
                                            v-model="form.delivery_state"
                                            type="text"
                                            placeholder="State"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                        />

                                        <p
                                            v-if="form.errors.delivery_state"
                                            class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400"
                                        >
                                            {{ form.errors.delivery_state }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Delivery Notes -->

                                <div>
                                    <label
                                        for="delivery_notes"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Delivery Notes
                                        <span
                                            class="font-normal text-slate-400"
                                        >
                                            (Optional)
                                        </span>
                                    </label>

                                    <textarea
                                        id="delivery_notes"
                                        v-model="form.delivery_notes"
                                        rows="3"
                                        placeholder="Landmark, preferred delivery time, or other instructions"
                                        class="w-full resize-none rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                    ></textarea>

                                    <p
                                        v-if="form.errors.delivery_notes"
                                        class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400"
                                    >
                                        {{ form.errors.delivery_notes }}
                                    </p>
                                </div>

                                <div
                                    v-if="user"
                                    class="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-900/70 dark:bg-green-950/30"
                                >
                                    <label
                                        v-if="form.saved_address_id"
                                        class="flex cursor-pointer items-start gap-3"
                                    >
                                        <input
                                            v-model="form.update_saved_address"
                                            type="checkbox"
                                            class="mt-0.5 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                            @change="updateSelectedAddress"
                                        >

                                        <span class="text-sm text-slate-700 dark:text-slate-200">
                                            <span class="font-semibold">Update this saved address</span><br>
                                            Save the edits above for your next order. Your existing orders will not change.
                                        </span>
                                    </label>

                                    <label
                                        v-else
                                        class="flex cursor-pointer items-start gap-3"
                                    >
                                        <input
                                            v-model="form.save_address"
                                            type="checkbox"
                                            class="mt-0.5 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                        >

                                        <span class="text-sm text-slate-700 dark:text-slate-200">
                                            <span class="font-semibold">Save these delivery details</span><br>
                                            Reuse them at checkout next time.
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </section>

                        <!-- Order Notes -->

                        <section
                            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900 sm:p-8"
                        >
                            <label
                                for="notes"
                                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                            >
                                Order Notes
                                <span
                                    class="font-normal text-slate-400"
                                >
                                    (Optional)
                                </span>
                            </label>

                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="3"
                                placeholder="Anything else we should know?"
                                class="w-full resize-none rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                            ></textarea>

                            <p
                                v-if="form.errors.notes"
                                class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.notes }}
                            </p>
                        </section>
                    </div>

                    <!-- =================================================
                         RIGHT COLUMN — ORDER SUMMARY
                    ================================================== -->

                    <aside
                        class="lg:sticky lg:top-24 lg:h-fit"
                    >
                        <div
                            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900 sm:p-8"
                        >
                            <div class="flex items-center justify-between">
                                <h2
                                    class="text-lg font-bold text-slate-950 dark:text-white"
                                >
                                    Your Order
                                </h2>

                                <span
                                    class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-bold text-green-700 dark:bg-green-950/50 dark:text-green-400"
                                >
                                    {{ props.cart.length }}
                                    {{ props.cart.length === 1 ? 'item' : 'items' }}
                                </span>
                            </div>

                            <!-- Products -->

                            <div
                                v-if="cart.length"
                                class="mt-6 max-h-80 space-y-4 overflow-y-auto pr-1"
                            >
                                <div
                                    v-for="item in cart"
                                    :key="item.id ?? item.product_id"
                                    class="flex gap-3"
                                >
                                    <!-- Image -->

                                    <div
                                        class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-950"
                                    >
                                        <img
                                            v-if="imageUrl(getImage(item))"
                                            :src="imageUrl(getImage(item))"
                                            :alt="getName(item)"
                                            class="h-full w-full object-contain p-1"
                                        />

                                        <svg
                                            v-else
                                            class="h-6 w-6 text-slate-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.8"
                                                d="M12 3 4 7v10l8 4 8-4V7l-8-4Z"
                                            />
                                        </svg>
                                    </div>

                                    <!-- Details -->

                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="line-clamp-2 text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            {{ getName(item) }}
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{ formatMoney(getPrice(item)) }}
                                            ×
                                            {{ getQuantity(item) }}
                                        </p>
                                    </div>

                                    <p
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            formatMoney(
                                                getPrice(item) *
                                                getQuantity(item)
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>

                            <!-- Empty -->

                            <div
                                v-else
                                class="mt-6 rounded-xl bg-slate-50 p-5 text-center dark:bg-slate-950"
                            >
                                <p
                                    class="text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Your cart is empty.
                                </p>
                            </div>

                            <!-- Totals -->

                            <div
                                class="mt-6 space-y-3 border-t border-slate-200 pt-5 dark:border-slate-800"
                            >
                                <div
                                    class="flex justify-between text-sm"
                                >
                                    <span
                                        class="text-slate-500 dark:text-slate-400"
                                    >
                                        Subtotal
                                    </span>

                                    <span
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ formatMoney(subtotal) }}
                                    </span>
                                </div>

                                <div
                                    class="flex justify-between text-sm"
                                >
                                    <span
                                        class="text-slate-500 dark:text-slate-400"
                                    >
                                        Delivery
                                    </span>

                                    <span
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ formatMoney(delivery) }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center justify-between border-t border-slate-200 pt-4 dark:border-slate-800"
                                >
                                    <span
                                        class="text-base font-bold text-slate-950 dark:text-white"
                                    >
                                        Total
                                    </span>

                                    <span
                                        class="text-xl font-extrabold text-green-600 dark:text-green-400"
                                    >
                                        {{ formatMoney(total) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Submit -->

                            <button
                                type="submit"
                                :disabled="form.processing || cart.length === 0"
                                class="mt-6 w-full rounded-xl bg-green-600 px-5 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500/30 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span v-if="form.processing">
                                    Creating Order...
                                </span>

                                <span v-else>
                                    Continue to Payment
                                </span>
                            </button>

                            <p
                                class="mt-4 text-center text-xs leading-5 text-slate-400 dark:text-slate-500"
                            >
                                Your order will be created first. You will
                                then proceed to the payment step.
                            </p>
                        </div>
                    </aside>
                </form>
            </main>
        </div>
    </CustomerLayout>
</template>
