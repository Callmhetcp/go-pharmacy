<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useAdminTheme } from '@/Composables/useAdminTheme';

const page = usePage();

const mobileMenuOpen = ref(false);
const expiryReminderClosed = ref(false);

const user = computed(() => page.props.auth?.user ?? null);

const adminExpiryReminder = computed(
    () => page.props.adminExpiryReminder ?? {
        show: false,
        products: [],
    }
);

const showExpiryReminder = computed(() => {
    return (
        adminExpiryReminder.value.show === true &&
        !expiryReminderClosed.value
    );
});

/*
|--------------------------------------------------------------------------
| BRANDING
|--------------------------------------------------------------------------
*/

const logoUrl =
    '/images/branding/go-pharmacy-logo-transparent.png';

/*
|--------------------------------------------------------------------------
| ADMIN THEME
|--------------------------------------------------------------------------
*/

const { theme, setTheme } = useAdminTheme();

const toggleTheme = () => {
    setTheme(
        theme.value === 'dark'
            ? 'light'
            : 'dark'
    );
};

/*
|--------------------------------------------------------------------------
| NAVIGATION
|--------------------------------------------------------------------------
*/

const navigation = [
    {
        label: 'Dashboard',
        href: '/admin',
        icon: 'dashboard',
    },
    {
        label: 'Products',
        href: '/admin/products',
        icon: 'products',
    },
    {
        label: 'Advertisements',
        href: '/admin/advertisements',
        icon: 'advertisements',
    },
    {
        label: 'Categories',
        href: '/admin/categories',
        icon: 'categories',
    },
    {
        label: 'Inventory',
        href: '/admin/inventory',
        icon: 'inventory',
    },
    {
        label: 'Suppliers',
        href: '/admin/suppliers',
        icon: 'suppliers',
    },
    {
        label: 'Purchases',
        href: '/admin/purchases',
        icon: 'purchases',
    },
    {
        label: 'Orders',
        href: '/admin/orders',
        icon: 'orders',
    },
    {
        label: 'Prescriptions',
        href: '/admin/prescriptions',
        icon: 'prescription',
    },
    {
        label: 'Customers',
        href: '/admin/customers',
        icon: 'customers',
    },
    {
        label: 'Reports',
        href: '/admin/reports',
        icon: 'reports',
    },
    {
        label: 'POS',
        href: '/admin/pos',
        icon: 'pos',
    },
];

const systemNavigation = [
    {
        label: 'Settings',
        href: '/admin/settings',
        icon: 'settings',
    },
];

/*
|--------------------------------------------------------------------------
| ACTIVE NAVIGATION
|--------------------------------------------------------------------------
*/

const isActive = (href) => {
    const currentUrl = page.url;

    if (href === '/admin') {
        return (
            currentUrl === '/admin' ||
            currentUrl === '/admin/'
        );
    }

    return currentUrl.startsWith(href);
};

/*
|--------------------------------------------------------------------------
| MOBILE MENU
|--------------------------------------------------------------------------
*/

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

/*
|--------------------------------------------------------------------------
| EXPIRY REMINDER
|--------------------------------------------------------------------------
*/

const closeExpiryReminder = () => {
    expiryReminderClosed.value = true;
};

const goToExpiredProducts = () => {
    expiryReminderClosed.value = true;

    router.visit('/admin/expiry-reminder');
};

const formatExpiryDate = (date) => {
    if (!date) {
        return 'Unknown';
    }

    const parsed = new Date(`${date}T00:00:00`);

    if (Number.isNaN(parsed.getTime())) {
        return date;
    }

    return parsed.toLocaleDateString('en-NG', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div
        class="admin-layout min-h-screen bg-slate-50 text-slate-900 transition-colors duration-300"
    >
        <!-- =========================================================
             MOBILE OVERLAY
        ========================================================== -->

        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileMenuOpen"
                class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm lg:hidden"
                @click="closeMobileMenu"
            ></div>
        </Transition>

        <!-- =========================================================
             SIDEBAR
        ========================================================== -->

        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-slate-200 bg-white transition-transform duration-300 lg:translate-x-0"
            :class="
                mobileMenuOpen
                    ? 'translate-x-0'
                    : '-translate-x-full'
            "
        >
            <!-- Brand -->

            <div
                class="flex h-20 shrink-0 items-center justify-between border-b border-slate-200 px-6"
            >
                <Link
                    href="/admin"
                    class="flex items-center"
                    @click="closeMobileMenu"
                >
                    <img
                        :src="logoUrl"
                        alt="Go Pharmacy"
                        class="h-12 w-auto max-w-[190px] object-contain"
                    />
                </Link>

                <!-- Mobile Close -->

                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 lg:hidden"
                    @click="closeMobileMenu"
                    aria-label="Close menu"
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
                            d="M6 6l12 12M18 6 6 18"
                        />
                    </svg>
                </button>
            </div>

            <!-- =====================================================
                 NAVIGATION
            ====================================================== -->

            <div class="flex-1 overflow-y-auto px-4 py-6">
                <!-- Main Menu -->

                <div>
                    <p
                        class="mb-3 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"
                    >
                        Main Menu
                    </p>

                    <nav class="space-y-1">
                        <Link
                            v-for="item in navigation"
                            :key="item.label"
                            :href="item.href"
                            @click="closeMobileMenu"
                            class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition"
                            :class="
                                isActive(item.href)
                                    ? 'bg-green-50 text-green-700'
                                    : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950'
                            "
                        >
                            <!-- Dashboard -->

                            <svg
                                v-if="item.icon === 'dashboard'"
                                class="h-5 w-5 shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M4 13h6V4H4v9Zm0 7h6v-4H4v4Zm10 0h6v-9h-6v9Zm0-16v4h6V4h-6Z"
                                />
                            </svg>

                            <!-- Products -->

                            <svg
                                v-else-if="item.icon === 'products'"
                                class="h-5 w-5 shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 0v9m8-4.5-8 4.5m0 0-8-4.5"
                                />
                            </svg>

                            <!-- Advertisements -->

                            <svg
                                v-else-if="item.icon === 'advertisements'"
                                class="h-5 w-5 shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M3 11.5v1a2 2 0 0 0 2 2h1l2.5 5h2l-2-5H10l8 3V6l-8 3H5a2 2 0 0 0-2 2v.5Zm7-2.5v6m8-4.5h2a2 2 0 0 1 0 4h-2"
                                />
                            </svg>

                            <!-- Categories -->

                            <svg
                                v-else-if="item.icon === 'categories'"
                                class="h-5 w-5 shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M4 5a1 1 0 0 1 1-1h5l2 3h7a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5Z"
                                />
                            </svg>

                            <!-- Inventory -->

                            <svg
                                v-else-if="item.icon === 'inventory'"
                                class="h-5 w-5 shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M4 7h16M6 7v12h12V7M9 4h6v3H9V4Zm0 7h6m-6 4h4"
                                />
                            </svg>

                            <!-- Suppliers -->

                            <svg
                                v-else-if="item.icon === 'suppliers'"
                                class="h-5 w-5 shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M3 21h18M5 21V9l7-4 7 4v12M8 21v-5h3v5m2 0v-5h3v5M8 10h.01M12 10h.01M16 10h.01"
                                />
                            </svg>

                            <!-- Purchases -->

                            <svg
                                v-else-if="item.icon === 'purchases'"
                                class="h-5 w-5 shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm0 5h12M8 12h8m-8 4h5"
                                />
                            </svg>

                            <!-- Orders -->

                            <svg
                                v-else-if="item.icon === 'orders'"
                                class="h-5 w-5 shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M7 4h10a2 2 0 0 1 2 2v14H5V6a2 2 0 0 1 2-2Zm2 0V2h6v2m-5 5h6m-6 4h6m-6 4h4"
                                />
                            </svg>

                            <!-- Prescriptions -->

                            <svg
                                v-else-if="item.icon === 'prescription'"
                                class="h-5 w-5 shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M7 3h10a2 2 0 0 1 2 2v14H5V5a2 2 0 0 1 2-2Zm2 4h6M9 11h6m-6 4h4"
                                />
                            </svg>

                            <!-- Customers -->

                            <svg
                                v-else-if="item.icon === 'customers'"
                                class="h-5 w-5 shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m9-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6 2a3 3 0 0 1 3 3v1m-3-8a3 3 0 1 0 0-6"
                                />
                            </svg>

                            <!-- Reports -->

                            <svg
                                v-else-if="item.icon === 'reports'"
                                class="h-5 w-5 shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M4 19V5m0 14h16M8 16v-5m4 5V7m4 9v-8"
                                />
                            </svg>

                            <!-- POS -->

                            <svg
                                v-else-if="item.icon === 'pos'"
                                class="h-5 w-5 shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm3 4h6M8 11h8M8 15h3m2 0h3"
                                />
                            </svg>

                            <span>{{ item.label }}</span>
                        </Link>
                    </nav>
                </div>

                <!-- System -->

                <div class="mt-8">
                    <p
                        class="mb-3 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"
                    >
                        System
                    </p>

                    <nav class="space-y-1">
                        <Link
                            v-for="item in systemNavigation"
                            :key="item.label"
                            :href="item.href"
                            @click="closeMobileMenu"
                            class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition"
                            :class="
                                isActive(item.href)
                                    ? 'bg-green-50 text-green-700'
                                    : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950'
                            "
                        >
                            <svg
                                class="h-5 w-5 shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm8.5 3.5a7.8 7.8 0 0 0-.1-1.2l2-1.5-2-3.4-2.4 1a8 8 0 0 0-2.1-1.2L15.6 3h-4l-.3 2.7a8 8 0 0 0-2.1 1.2l-2.4-1-2 3.4 2 1.5a7.8 7.8 0 0 0 0 2.4l-2 1.5 2 3.4 2.4-1a8 8 0 0 0 2.1 1.2l.3 2.7h4l.3-2.7a8 8 0 0 0 2.1-1.2l2.4 1 2-3.4-2-1.5c.1-.4.2-.8.2-1.2Z"
                                />
                            </svg>

                            <span>{{ item.label }}</span>
                        </Link>
                    </nav>
                </div>
            </div>

            <!-- =====================================================
                 USER AREA
            ====================================================== -->

            <div class="shrink-0 border-t border-slate-200 p-4">
                <div
                    class="mb-3 flex items-center gap-3 rounded-xl bg-slate-50 p-3"
                >
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-100 font-bold text-green-700"
                    >
                        {{
                            user?.name
                                ?.charAt(0)
                                ?.toUpperCase() ?? 'A'
                        }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <p
                            class="truncate text-sm font-semibold text-slate-900"
                        >
                            {{ user?.name ?? 'Administrator' }}
                        </p>

                        <p
                            class="truncate text-xs text-slate-500"
                        >
                            {{ user?.email ?? 'Admin account' }}
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="logout"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-slate-600 transition hover:bg-red-50 hover:text-red-600"
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
                            stroke-width="1.8"
                            d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4m-5-4 5-5m0 0-5-5m5 5H3"
                        />
                    </svg>

                    <span>Sign out</span>
                </button>
            </div>
        </aside>

        <!-- =========================================================
             MAIN AREA
        ========================================================== -->

        <div class="lg:pl-72">
            <!-- Header -->

            <header
                class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8"
            >
                <!-- Mobile Menu -->

                <button
                    type="button"
                    class="rounded-xl p-2.5 text-slate-600 hover:bg-slate-100 lg:hidden"
                    @click="mobileMenuOpen = true"
                    aria-label="Open menu"
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
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>

                <!-- Page Context -->

                <div class="hidden lg:block">
                    <p
                        class="text-sm font-medium text-slate-500"
                    >
                        Go Pharmacy
                    </p>

                    <p class="text-xs text-slate-400">
                        Administration Portal
                    </p>
                </div>

                <!-- Header Actions -->

                <div class="ml-auto flex items-center gap-3">
                    <!-- Admin Theme -->

                    <button
                        type="button"
                        @click="toggleTheme"
                        class="admin-theme-button flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-green-300 hover:bg-green-50 hover:text-green-700"
                        :aria-label="
                            theme === 'dark'
                                ? 'Switch to light mode'
                                : 'Switch to dark mode'
                        "
                    >
                        <!-- Moon -->

                        <svg
                            v-if="theme !== 'dark'"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M21 12.8A8.5 8.5 0 1 1 11.2 3 6.7 6.7 0 0 0 21 12.8Z"
                            />
                        </svg>

                        <!-- Sun -->

                        <svg
                            v-else
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="4"
                                stroke-width="1.8"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-width="1.8"
                                d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"
                            />
                        </svg>
                    </button>

                    <!-- View Website -->

                    <a
                        href="/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="hidden rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-green-300 hover:bg-green-50 hover:text-green-700 sm:inline-flex"
                    >
                        View Website
                    </a>

                    <!-- Avatar -->

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 text-sm font-bold text-green-700"
                    >
                        {{
                            user?.name
                                ?.charAt(0)
                                ?.toUpperCase() ?? 'A'
                        }}
                    </div>
                </div>
            </header>

            <!-- Page -->

            <main class="min-h-[calc(100vh-5rem)]">
                <slot />
            </main>
        </div>

       <!-- =========================================================
     ADMIN EXPIRY REMINDER
========================================================== -->
<Transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="opacity-0 scale-95"
    enter-to-class="opacity-100 scale-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100 scale-100"
    leave-to-class="opacity-0 scale-95"
>
    <div
        v-if="showExpiryReminder"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/50 px-4 py-6 backdrop-blur-sm"
    >
        <div
            class="w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl"
            role="dialog"
            aria-modal="true"
            aria-labelledby="expiry-reminder-title"
        >

            <!-- Header -->
            <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4 sm:px-6">
                <div class="flex min-w-0 items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600">
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 9v4m0 4h.01M10.3 3.8 2.7 17a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 3.8a2 2 0 0 0-3.4 0Z"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <h2
                            id="expiry-reminder-title"
                            class="text-base font-bold text-slate-900 sm:text-lg"
                        >
                            Expired Stock Reminder
                        </h2>

                        <p class="mt-1 text-xs leading-5 text-slate-500 sm:text-sm">
                            The following inventory batches have passed their expiry date.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="closeExpiryReminder"
                    class="ml-3 shrink-0 rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                    aria-label="Close reminder"
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
                            d="M6 6l12 12M18 6 6 18"
                        />
                    </svg>
                </button>
            </div>

            <!-- Exact Expired Products -->
            <div class="max-h-[52vh] overflow-y-auto px-5 py-4 sm:px-6">

                <div
                    v-if="adminExpiryReminder.products.length"
                    class="space-y-2.5"
                >
                    <div
                        v-for="product in adminExpiryReminder.products"
                        :key="product.id"
                        class="group rounded-xl border border-red-100 bg-red-50/60 p-3.5 transition hover:border-red-200 hover:bg-red-50"
                    >
                        <div class="flex items-center justify-between gap-3">

                            <!-- Product Information -->
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900">
                                    {{ product.product_name }}
                                </p>

                                <div class="mt-1.5 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500">
                                    <span v-if="product.sku">
                                        SKU: {{ product.sku }}
                                    </span>

                                    <span v-if="product.batch_number">
                                        Batch: {{ product.batch_number }}
                                    </span>

                                    <span v-if="product.supplier">
                                        Supplier: {{ product.supplier }}
                                    </span>
                                </div>

                                <div class="mt-2 flex flex-wrap items-center gap-3">
                                    <span class="text-xs font-medium text-red-600">
                                        Expired:
                                        {{ formatExpiryDate(product.expiry_date) }}
                                    </span>

                                    <span class="text-xs text-slate-500">
                                        Qty: {{ product.quantity }}
                                    </span>
                                </div>
                            </div>

                            <!-- Exact Product Button -->
                            <Link
                                :href="route(
                                    'admin.products.edit',
                                    product.product_id
                                )"
                                @click="closeExpiryReminder"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-green-50 hover:text-green-700 hover:ring-green-200"
                            >
                                View
                                
                                <svg
                                    class="h-3.5 w-3.5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- No expired products -->
                <div
                    v-else
                    class="py-8 text-center"
                >
                    <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-green-100 text-green-600">
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
                                d="m5 12 4 4L19 6"
                            />
                        </svg>
                    </div>

                    <p class="mt-3 text-sm font-semibold text-slate-900">
                        No expired products
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Your inventory is currently up to date.
                    </p>
                </div>

            </div>

            <!-- Footer -->
            <div class="flex flex-col-reverse gap-2.5 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-between sm:px-6">

                <button
                    type="button"
                    @click="closeExpiryReminder"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100"
                >
                    Later
                </button>

                <button
                    type="button"
                    @click="goToExpiredProducts"
                    class="rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700"
                >
                    Review Expired Products
                </button>

            </div>
        </div>
    </div>
</Transition>
    </div>
</template>