<script setup>
import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
});

const activeTab = ref('general');
const saving = ref(false);

const form = reactive({
    // General
    business_name: props.settings.general?.['business.name'] ?? '',
    business_tagline: props.settings.general?.['business.tagline'] ?? '',
    business_email: props.settings.general?.['business.email'] ?? '',
    business_phone: props.settings.general?.['business.phone'] ?? '',
    business_whatsapp: props.settings.general?.['business.whatsapp'] ?? '',
    business_address: props.settings.general?.['business.address'] ?? '',
    business_city: props.settings.general?.['business.city'] ?? '',
    business_state: props.settings.general?.['business.state'] ?? '',

    // Website
    website_title: props.settings.website?.['website.title'] ?? '',
    hero_title: props.settings.website?.['website.hero_title'] ?? '',
    hero_subtitle: props.settings.website?.['website.hero_subtitle'] ?? '',
    primary_color:
        props.settings.website?.['website.primary_color'] ?? '#16A34A',
    accent_color:
        props.settings.website?.['website.accent_color'] ?? '#22C55E',
    registration_enabled:
        props.settings.website?.['website.registration_enabled'] ?? true,
    guest_checkout:
        props.settings.website?.['website.guest_checkout'] ?? true,
    maintenance_mode:
        props.settings.website?.['website.maintenance_mode'] ?? false,

    // Orders & Delivery
    order_prefix: props.settings.orders?.['orders.prefix'] ?? 'GP-',
    minimum_order_amount:
        props.settings.orders?.['orders.minimum_amount'] ?? 0,
    delivery_enabled:
        props.settings.orders?.['delivery.enabled'] ?? true,
    standard_delivery_fee:
        props.settings.orders?.['delivery.standard_fee'] ?? 0,
    free_delivery_threshold:
        props.settings.orders?.['delivery.free_threshold'] ?? 0,
    pickup_enabled:
        props.settings.orders?.['delivery.pickup_enabled'] ?? true,
    pickup_address:
        props.settings.orders?.['delivery.pickup_address'] ?? '',

    // Payments
    bank_transfer_enabled:
        props.settings.payments?.['payments.bank_transfer_enabled'] ?? true,
    cash_on_delivery_enabled:
        props.settings.payments?.[
            'payments.cash_on_delivery_enabled'
        ] ?? true,
    require_payment_proof:
        props.settings.payments?.['payments.require_proof'] ?? true,
    bank_name: props.settings.payments?.['payments.bank_name'] ?? '',
    account_name: props.settings.payments?.['payments.account_name'] ?? '',
    account_number:
        props.settings.payments?.['payments.account_number'] ?? '',
    payment_instructions:
        props.settings.payments?.['payments.instructions'] ?? '',

    // Receipt
    receipt_show_logo:
        props.settings.receipt?.['receipt.show_logo'] ?? true,
    receipt_show_customer:
        props.settings.receipt?.['receipt.show_customer'] ?? true,
    receipt_show_cashier:
        props.settings.receipt?.['receipt.show_cashier'] ?? true,
    receipt_show_payment_method:
        props.settings.receipt?.['receipt.show_payment_method'] ?? true,
    receipt_show_delivery_fee:
        props.settings.receipt?.['receipt.show_delivery_fee'] ?? true,
    receipt_prefix:
        props.settings.receipt?.['receipt.prefix'] ?? 'GP-RCPT-',
    receipt_footer:
        props.settings.receipt?.['receipt.footer'] ?? '',
});

const tabs = [
    {
        key: 'general',
        label: 'General',
    },
    {
        key: 'website',
        label: 'Website',
    },
    {
        key: 'orders',
        label: 'Orders & Delivery',
    },
    {
        key: 'payments',
        label: 'Payments',
    },
    {
        key: 'receipt',
        label: 'Receipt',
    },
];

const submit = () => {
    saving.value = true;

    router.put(route('admin.settings.update'), form, {
        preserveScroll: true,
        onFinish: () => {
            saving.value = false;
        },
    });
};
</script>

<template>
    <AdminLayout>
        <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
            <!-- Header -->
            <section
                class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
                >
                    <p
                        class="text-sm font-semibold text-green-600 dark:text-green-400"
                    >
                        Admin Panel
                    </p>

                    <h1
                        class="mt-1 text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white"
                    >
                        Settings
                    </h1>

                    <p
                        class="mt-2 max-w-2xl text-sm text-slate-500 dark:text-slate-400"
                    >
                        Manage Go Pharmacy's core business, website,
                        orders, payments and receipt settings.
                    </p>
                </div>
            </section>

            <!-- Content -->
            <main
                class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
            >
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- Tabs -->
                    <div
                        class="overflow-x-auto border-b border-slate-200 dark:border-slate-800"
                    >
                        <nav class="flex min-w-max gap-1 px-4">
                            <button
                                v-for="tab in tabs"
                                :key="tab.key"
                                type="button"
                                @click="activeTab = tab.key"
                                class="border-b-2 px-4 py-4 text-sm font-semibold transition"
                                :class="
                                    activeTab === tab.key
                                        ? 'border-green-600 text-green-600 dark:border-green-400 dark:text-green-400'
                                        : 'border-transparent text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                "
                            >
                                {{ tab.label }}
                            </button>
                        </nav>
                    </div>

                    <form @submit.prevent="submit">
                        <!-- GENERAL -->
                        <section
                            v-if="activeTab === 'general'"
                            class="space-y-6 p-6"
                        >
                            <div>
                                <h2
                                    class="text-lg font-bold text-slate-950 dark:text-white"
                                >
                                    General Information
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Basic information about your pharmacy.
                                </p>
                            </div>

                            <div
                                class="grid gap-6 md:grid-cols-2"
                            >
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Business Name
                                    </label>

                                    <input
                                        v-model="form.business_name"
                                        type="text"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Tagline
                                    </label>

                                    <input
                                        v-model="form.business_tagline"
                                        type="text"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Business Email
                                    </label>

                                    <input
                                        v-model="form.business_email"
                                        type="email"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Phone
                                    </label>

                                    <input
                                        v-model="form.business_phone"
                                        type="text"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        WhatsApp
                                    </label>

                                    <input
                                        v-model="form.business_whatsapp"
                                        type="text"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        City
                                    </label>

                                    <input
                                        v-model="form.business_city"
                                        type="text"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        State
                                    </label>

                                    <input
                                        v-model="form.business_state"
                                        type="text"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>

                                <div class="md:col-span-2">
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Address
                                    </label>

                                    <textarea
                                        v-model="form.business_address"
                                        rows="3"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    ></textarea>
                                </div>
                            </div>
                        </section>

                        <!-- WEBSITE -->
                        <section
                            v-if="activeTab === 'website'"
                            class="space-y-6 p-6"
                        >
                            <div>
                                <h2
                                    class="text-lg font-bold text-slate-950 dark:text-white"
                                >
                                    Website Settings
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Control the main public website settings.
                                </p>
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Website Title
                                    </label>

                                    <input
                                        v-model="form.website_title"
                                        type="text"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Hero Title
                                    </label>

                                    <input
                                        v-model="form.hero_title"
                                        type="text"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Hero Subtitle
                                    </label>

                                    <textarea
                                        v-model="form.hero_subtitle"
                                        rows="3"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    ></textarea>
                                </div>

                                <div
                                    class="grid gap-6 md:grid-cols-2"
                                >
                                    <div>
                                        <label
                                            class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            Primary Color
                                        </label>

                                        <div class="flex gap-3">
                                            <input
                                                v-model="form.primary_color"
                                                type="color"
                                                class="h-11 w-16 cursor-pointer rounded-lg border border-slate-300 bg-white"
                                            />

                                            <input
                                                v-model="form.primary_color"
                                                type="text"
                                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            Accent Color
                                        </label>

                                        <div class="flex gap-3">
                                            <input
                                                v-model="form.accent_color"
                                                type="color"
                                                class="h-11 w-16 cursor-pointer rounded-lg border border-slate-300 bg-white"
                                            />

                                            <input
                                                v-model="form.accent_color"
                                                type="text"
                                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="grid gap-4">
                                    <label
                                        class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                                    >
                                        <input
                                            v-model="form.registration_enabled"
                                            type="checkbox"
                                            class="mt-1 h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                        />

                                        <span class="flex flex-col">
                                            <strong
                                                class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                            >
                                                Customer Registration
                                            </strong>

                                            <small
                                                class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                            >
                                                Allow customers to create
                                                accounts.
                                            </small>
                                        </span>
                                    </label>

                                    <label
                                        class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                                    >
                                        <input
                                            v-model="form.guest_checkout"
                                            type="checkbox"
                                            class="mt-1 h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                        />

                                        <span class="flex flex-col">
                                            <strong
                                                class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                            >
                                                Guest Checkout
                                            </strong>

                                            <small
                                                class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                            >
                                                Allow customers to checkout
                                                without an account.
                                            </small>
                                        </span>
                                    </label>

                                    <label
                                        class="flex cursor-pointer items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 transition hover:bg-amber-100 dark:border-amber-900/50 dark:bg-amber-950/20 dark:hover:bg-amber-950/30"
                                    >
                                        <input
                                            v-model="form.maintenance_mode"
                                            type="checkbox"
                                            class="mt-1 h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500"
                                        />

                                        <span class="flex flex-col">
                                            <strong
                                                class="text-sm font-semibold text-amber-800 dark:text-amber-300"
                                            >
                                                Maintenance Mode
                                            </strong>

                                            <small
                                                class="mt-1 text-xs leading-5 text-amber-700 dark:text-amber-400"
                                            >
                                                Temporarily disable the public
                                                website.
                                            </small>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </section>

                        <!-- ORDERS & DELIVERY -->
                        <section
                            v-if="activeTab === 'orders'"
                            class="space-y-6 p-6"
                        >
                            <div>
                                <h2
                                    class="text-lg font-bold text-slate-950 dark:text-white"
                                >
                                    Orders & Delivery
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Control order numbering, delivery and
                                    pickup options.
                                </p>
                            </div>

                            <div
                                class="grid gap-6 md:grid-cols-2"
                            >
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Order Number Prefix
                                    </label>

                                    <input
                                        v-model="form.order_prefix"
                                        type="text"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Minimum Order Amount
                                    </label>

                                    <input
                                        v-model="form.minimum_order_amount"
                                        type="number"
                                        min="0"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Standard Delivery Fee
                                    </label>

                                    <input
                                        v-model="form.standard_delivery_fee"
                                        type="number"
                                        min="0"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Free Delivery Threshold
                                    </label>

                                    <input
                                        v-model="form.free_delivery_threshold"
                                        type="number"
                                        min="0"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>

                                <div class="md:col-span-2">
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Pickup Address
                                    </label>

                                    <textarea
                                        v-model="form.pickup_address"
                                        rows="3"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                                >
                                    <input
                                        v-model="form.delivery_enabled"
                                        type="checkbox"
                                        class="mt-1 h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                    />

                                    <span class="flex flex-col">
                                        <strong
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            Delivery Enabled
                                        </strong>

                                        <small
                                            class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            Allow customers to select
                                            delivery.
                                        </small>
                                    </span>
                                </label>

                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                                >
                                    <input
                                        v-model="form.pickup_enabled"
                                        type="checkbox"
                                        class="mt-1 h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                    />

                                    <span class="flex flex-col">
                                        <strong
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            Pickup Enabled
                                        </strong>

                                        <small
                                            class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            Allow customers to collect orders.
                                        </small>
                                    </span>
                                </label>
                            </div>
                        </section>

                        <!-- PAYMENTS -->
                        <section
                            v-if="activeTab === 'payments'"
                            class="space-y-6 p-6"
                        >
                            <div>
                                <h2
                                    class="text-lg font-bold text-slate-950 dark:text-white"
                                >
                                    Payment Settings
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Configure manual payment methods for the
                                    MVP.
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-300"
                            >
                                Live payment gateway processing is not enabled
                                at this stage. Flutterwave/Paystack can be
                                integrated after client approval.
                            </div>

                            <div class="grid gap-4">
                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                                >
                                    <input
                                        v-model="form.bank_transfer_enabled"
                                        type="checkbox"
                                        class="mt-1 h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                    />

                                    <span class="flex flex-col">
                                        <strong
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            Bank Transfer
                                        </strong>

                                        <small
                                            class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            Allow manual bank transfer
                                            payments.
                                        </small>
                                    </span>
                                </label>

                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                                >
                                    <input
                                        v-model="form.cash_on_delivery_enabled"
                                        type="checkbox"
                                        class="mt-1 h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                    />

                                    <span class="flex flex-col">
                                        <strong
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            Cash on Delivery
                                        </strong>

                                        <small
                                            class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            Allow customers to pay on
                                            delivery.
                                        </small>
                                    </span>
                                </label>

                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                                >
                                    <input
                                        v-model="form.require_payment_proof"
                                        type="checkbox"
                                        class="mt-1 h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                    />

                                    <span class="flex flex-col">
                                        <strong
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            Require Payment Proof
                                        </strong>

                                        <small
                                            class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            Require customers to submit proof
                                            for manual payments.
                                        </small>
                                    </span>
                                </label>
                            </div>

                            <div
                                class="grid gap-6 md:grid-cols-2"
                            >
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Bank Name
                                    </label>

                                    <input
                                        v-model="form.bank_name"
                                        type="text"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Account Name
                                    </label>

                                    <input
                                        v-model="form.account_name"
                                        type="text"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Account Number
                                    </label>

                                    <input
                                        v-model="form.account_number"
                                        type="text"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>

                                <div class="md:col-span-2">
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Payment Instructions
                                    </label>

                                    <textarea
                                        v-model="form.payment_instructions"
                                        rows="4"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    ></textarea>
                                </div>
                            </div>
                        </section>

                        <!-- RECEIPT -->
                        <section
                            v-if="activeTab === 'receipt'"
                            class="space-y-6 p-6"
                        >
                            <div>
                                <h2
                                    class="text-lg font-bold text-slate-950 dark:text-white"
                                >
                                    Receipt Settings
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Control the information displayed on Go
                                    Pharmacy receipts.
                                </p>
                            </div>

                            <div
                                class="grid gap-4 md:grid-cols-2"
                            >
                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                                >
                                    <input
                                        v-model="form.receipt_show_logo"
                                        type="checkbox"
                                        class="mt-1 h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                    />

                                    <span class="flex flex-col">
                                        <strong
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            Show Logo
                                        </strong>

                                        <small
                                            class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            Display the pharmacy logo.
                                        </small>
                                    </span>
                                </label>

                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                                >
                                    <input
                                        v-model="form.receipt_show_customer"
                                        type="checkbox"
                                        class="mt-1 h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                    />

                                    <span class="flex flex-col">
                                        <strong
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            Show Customer
                                        </strong>

                                        <small
                                            class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            Display customer information.
                                        </small>
                                    </span>
                                </label>

                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                                >
                                    <input
                                        v-model="form.receipt_show_cashier"
                                        type="checkbox"
                                        class="mt-1 h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                    />

                                    <span class="flex flex-col">
                                        <strong
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            Show Cashier
                                        </strong>

                                        <small
                                            class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            Display the staff member who
                                            processed the order.
                                        </small>
                                    </span>
                                </label>

                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                                >
                                    <input
                                        v-model="form.receipt_show_payment_method"
                                        type="checkbox"
                                        class="mt-1 h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                    />

                                    <span class="flex flex-col">
                                        <strong
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            Show Payment Method
                                        </strong>

                                        <small
                                            class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            Display how the order was paid.
                                        </small>
                                    </span>
                                </label>

                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                                >
                                    <input
                                        v-model="form.receipt_show_delivery_fee"
                                        type="checkbox"
                                        class="mt-1 h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                    />

                                    <span class="flex flex-col">
                                        <strong
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            Show Delivery Fee
                                        </strong>

                                        <small
                                            class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            Display delivery charges.
                                        </small>
                                    </span>
                                </label>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Receipt Number Prefix
                                </label>

                                <input
                                    v-model="form.receipt_prefix"
                                    type="text"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Receipt Footer
                                </label>

                                <textarea
                                    v-model="form.receipt_footer"
                                    rows="4"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                ></textarea>
                            </div>
                        </section>

                        <!-- SAVE -->
                        <div
                            class="flex items-center justify-end border-t border-slate-200 bg-slate-50 px-6 py-5 dark:border-slate-800 dark:bg-slate-950"
                        >
                            <button
                                type="submit"
                                :disabled="saving"
                                class="rounded-xl bg-green-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                {{ saving ? 'Saving...' : 'Save Settings' }}
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </AdminLayout>
</template>