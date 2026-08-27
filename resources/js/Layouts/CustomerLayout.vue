<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useCustomerTheme } from '@/Composables/useCustomerTheme';

const page = usePage();

const mobileMenuOpen = ref(false);
const { theme, setTheme } = useCustomerTheme();

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

const user = computed(() => {
    return page.props.auth?.user ?? null;
});

const isAuthenticated = computed(() => {
    return !!user.value;
});

/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
*/

const cartCount = computed(() => {
    return Number(page.props.cart?.count ?? 0);
});

/*
|--------------------------------------------------------------------------
| Branding
|--------------------------------------------------------------------------
*/

const logoUrl = computed(() => {
    return (
        page.props.general?.logo ||
        '/images/branding/go-pharmacy-logo-transparent.png'
    );
});

/*
|--------------------------------------------------------------------------
| Dynamic Website Colors
|--------------------------------------------------------------------------
*/

const primaryColor = computed(() => {
    return page.props.website?.primary_color || '#16A34A';
});

const accentColor = computed(() => {
    return page.props.website?.accent_color || '#22C55E';
});

/*
|--------------------------------------------------------------------------
| Apply Website Colors Globally
|--------------------------------------------------------------------------
*/

const applyWebsiteColors = () => {
    document.documentElement.style.setProperty(
        '--gp-primary',
        primaryColor.value
    );

    document.documentElement.style.setProperty(
        '--gp-accent',
        accentColor.value
    );
};

watch(
    [primaryColor, accentColor],
    () => {
        applyWebsiteColors();
    },
    {
        immediate: true,
    }
);

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

const searchQuery = ref('');

const sanitizeSearch = (value) => {
    return value
        .replace(/[^a-zA-Z0-9\s]/g, '')
        .replace(/\s+/g, ' ')
        .trimStart();
};

const handleSearchInput = (event) => {
    searchQuery.value = sanitizeSearch(event.target.value);
};

const search = () => {
    const query = sanitizeSearch(searchQuery.value).trim();

    if (!query) {
        return;
    }

    mobileMenuOpen.value = false;

    router.get(
        '/shop',
        {
            search: query,
        },
        {
            preserveState: true,
            preserveScroll: false,
        }
    );
};

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

const logout = () => {
    router.post('/logout');
};

/*
|--------------------------------------------------------------------------
| Mobile Menu
|--------------------------------------------------------------------------
*/

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

/*
|--------------------------------------------------------------------------
| Theme
|--------------------------------------------------------------------------
*/

const themeLabel = computed(() => {
    if (theme.value === 'dark') {
        return 'Dark mode';
    }

    if (theme.value === 'light') {
        return 'Light mode';
    }

    return 'System theme';
});

const toggleTheme = () => {
    if (theme.value === 'system') {
        setTheme('light');
    } else if (theme.value === 'light') {
        setTheme('dark');
    } else {
        setTheme('system');
    }
};
</script>

<template>
    <div
        class="min-h-screen bg-white text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
    >
        <!-- =========================================================
             HEADER
        ========================================================== -->

        <header
            class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-950/95"
        >
            <!-- Main Header -->

            <div
                class="mx-auto flex h-20 max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8"
            >
                <!-- Logo -->

                <Link
                    href="/"
                    class="flex shrink-0 items-center"
                    aria-label="Go Pharmacy home"
                >
                    <img
                        :src="logoUrl"
                        alt="Go Pharmacy"
                        class="h-12 w-auto object-contain sm:h-14"
                    />
                </Link>

                <!-- Desktop Search -->

                <div class="hidden flex-1 md:block">
                    <form
                        class="relative mx-auto max-w-xl"
                        @submit.prevent="search"
                    >
                        <input
                            v-model="searchQuery"
                            type="search"
                            inputmode="text"
                            autocomplete="off"
                            maxlength="100"
                            placeholder="Search medicines, health products and more..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-24 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                            @input="handleSearchInput"
                        />

                        <svg
                            class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
                            />
                        </svg>

                        <button
                            type="submit"
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-lg bg-green-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-green-700"
                        >
                            Search
                        </button>
                    </form>
                </div>

                <!-- Desktop Actions -->

                <div class="hidden items-center gap-1 md:flex">
                    <!-- Theme -->

                    <button
                        type="button"
                        @click="toggleTheme"
                        :title="`${themeLabel} — click to change`"
                        :aria-label="`${themeLabel} — click to change`"
                        class="rounded-xl p-3 text-slate-600 transition hover:bg-slate-100 hover:text-green-600 focus:outline-none focus:ring-2 focus:ring-green-500/30 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-green-400"
                    >
                        <!-- System -->

                        <svg
                            v-if="theme === 'system'"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <rect
                                x="3"
                                y="4"
                                width="18"
                                height="14"
                                rx="2"
                                stroke-width="2"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-width="2"
                                d="M8 20h8M12 18v2"
                            />
                        </svg>

                        <!-- Light -->

                        <svg
                            v-else-if="theme === 'light'"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="4"
                                stroke-width="2"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-width="2"
                                d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"
                            />
                        </svg>

                        <!-- Dark -->

                        <svg
                            v-else
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-width="2"
                                d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"
                            />
                        </svg>
                    </button>

                    <!-- Wishlist -->

                    <button
                        type="button"
                        class="rounded-xl p-3 text-slate-600 transition hover:bg-slate-100 hover:text-green-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-green-400"
                        aria-label="Wishlist"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"
                            />
                        </svg>
                    </button>

                    <!-- My Orders -->

                    <Link
                        v-if="isAuthenticated"
                        :href="route('orders.index')"
                        class="rounded-xl p-3 text-slate-600 transition hover:bg-slate-100 hover:text-green-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-green-400"
                        aria-label="My Orders"
                        title="My Orders"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5h6M9 3h6a2 2 0 0 1 2 2v1h1a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h1V5a2 2 0 0 1 2-2Zm-3 11h12M8 15h3m-3 4h6"
                            />
                        </svg>
                    </Link>

                    <!-- Cart -->

                    <Link
                        :href="route('cart.index')"
                        class="relative rounded-xl p-3 text-slate-600 transition hover:bg-slate-100 hover:text-green-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-green-400"
                        aria-label="Shopping cart"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 3h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 7H6"
                            />

                            <circle
                                cx="10"
                                cy="20"
                                r="1.5"
                            />

                            <circle
                                cx="18"
                                cy="20"
                                r="1.5"
                            />
                        </svg>

                        <span
                            v-if="cartCount > 0"
                            class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-green-600 px-1 text-xs font-bold text-white"
                        >
                            {{ cartCount }}
                        </span>
                    </Link>

                    <!-- Authentication -->

                    <template v-if="!isAuthenticated">
                        <Link
                            href="/login"
                            class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 hover:text-green-600 dark:text-slate-200 dark:hover:bg-slate-800 dark:hover:text-green-400"
                        >
                            Login
                        </Link>

                        <Link
                            href="/register"
                            class="rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700"
                        >
                            Create Account
                        </Link>
                    </template>

                    <template v-else>
                        <Link
                            href="/profile"
                            class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 hover:text-green-600 dark:text-slate-200 dark:hover:bg-slate-800 dark:hover:text-green-400"
                        >
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-xs font-bold text-green-700 dark:bg-green-900/40 dark:text-green-400"
                            >
                                {{ user.name?.charAt(0)?.toUpperCase() }}
                            </span>

                            <span class="max-w-[120px] truncate">
                                {{ user.name }}
                            </span>
                        </Link>

                        <button
                            type="button"
                            @click="logout"
                            class="rounded-xl px-3 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:hover:bg-red-950/30"
                        >
                            Logout
                        </button>
                    </template>
                </div>

                <!-- Mobile Menu Button -->

                <button
                    type="button"
                    class="ml-auto rounded-xl p-3 text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800 md:hidden"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    :aria-expanded="mobileMenuOpen"
                    aria-label="Toggle navigation menu"
                >
                    <svg
                        v-if="!mobileMenuOpen"
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>

                    <svg
                        v-else
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
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

            <!-- Desktop Navigation -->

            <nav
                class="hidden border-t border-slate-100 dark:border-slate-800 md:block"
            >
                <div
                    class="mx-auto flex h-12 max-w-7xl items-center gap-8 px-4 sm:px-6 lg:px-8"
                >
                    <Link
                        href="/"
                        class="text-sm font-semibold text-green-600"
                    >
                        Home
                    </Link>

                    <Link
                        href="/shop"
                        class="text-sm font-medium text-slate-600 transition hover:text-green-600 dark:text-slate-300 dark:hover:text-green-400"
                    >
                        Shop
                    </Link>

                    <Link
                        href="/categories"
                        class="text-sm font-medium text-slate-600 transition hover:text-green-600 dark:text-slate-300 dark:hover:text-green-400"
                    >
                        Categories
                    </Link>

                    <Link
                        href="/shop"
                        class="text-sm font-medium text-slate-600 transition hover:text-green-600 dark:text-slate-300 dark:hover:text-green-400"
                    >
                        Health & Wellness
                    </Link>

                    <Link
                        href="/prescriptions"
                        class="text-sm font-medium text-slate-600 transition hover:text-green-600 dark:text-slate-300 dark:hover:text-green-400"
                    >
                        Prescription
                    </Link>

                    <a
                        href="#"
                        class="text-sm font-medium text-slate-600 transition hover:text-green-600 dark:text-slate-300 dark:hover:text-green-400"
                    >
                        About
                    </a>

                    <a
                        href="#"
                        class="text-sm font-medium text-slate-600 transition hover:text-green-600 dark:text-slate-300 dark:hover:text-green-400"
                    >
                        Contact
                    </a>
                </div>
            </nav>

            <!-- Mobile Navigation -->

            <div
                v-if="mobileMenuOpen"
                class="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950 md:hidden"
            >
                <div class="space-y-1 px-4 py-4">
                    <!-- Mobile Search -->

                    <form
                        class="relative mb-4"
                        @submit.prevent="search"
                    >
                        <input
                            v-model="searchQuery"
                            type="search"
                            inputmode="text"
                            autocomplete="off"
                            maxlength="100"
                            placeholder="Search medicines and products..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-20 text-sm text-slate-900 outline-none focus:border-green-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            @input="handleSearchInput"
                        />

                        <svg
                            class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
                            />
                        </svg>

                        <button
                            type="submit"
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-lg bg-green-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-green-700"
                        >
                            Search
                        </button>
                    </form>

                    <Link
                        href="/"
                        @click="closeMobileMenu"
                        class="block rounded-lg bg-green-50 px-4 py-3 text-sm font-semibold text-green-700 dark:bg-green-950/30 dark:text-green-400"
                    >
                        Home
                    </Link>

                    <Link
                        href="/shop"
                        @click="closeMobileMenu"
                        class="block rounded-lg px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900"
                    >
                        Shop
                    </Link>

                    <Link
                        href="/categories"
                        @click="closeMobileMenu"
                        class="block rounded-lg px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900"
                    >
                        Categories
                    </Link>

                    <Link
                        href="/shop"
                        @click="closeMobileMenu"
                        class="block rounded-lg px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900"
                    >
                        Health & Wellness
                    </Link>

                    <Link
                        href="/prescriptions"
                        @click="closeMobileMenu"
                        class="block rounded-lg px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900"
                    >
                        Prescription
                    </Link>

                    <Link
                        :href="route('cart.index')"
                        @click="closeMobileMenu"
                        class="flex items-center justify-between rounded-lg px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900"
                    >
                        <span>Shopping Cart</span>

                        <span
                            v-if="cartCount > 0"
                            class="rounded-full bg-green-600 px-2 py-0.5 text-xs font-bold text-white"
                        >
                            {{ cartCount }}
                        </span>
                    </Link>

                    <Link
                        v-if="isAuthenticated"
                        :href="route('orders.index')"
                        @click="closeMobileMenu"
                        class="flex items-center justify-between rounded-lg px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900"
                    >
                        <span>My Orders</span>
                    </Link>

                    <a
                        href="#"
                        class="block rounded-lg px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900"
                    >
                        About
                    </a>

                    <a
                        href="#"
                        class="block rounded-lg px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900"
                    >
                        Contact
                    </a>

                    <!-- Mobile Theme -->

                    <div
                        class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-800"
                    >
                        <button
                            type="button"
                            @click="toggleTheme"
                            class="flex w-full items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-green-300 hover:text-green-600 dark:border-slate-700 dark:text-slate-200 dark:hover:border-green-700 dark:hover:text-green-400"
                        >
                            <span>Appearance</span>

                            <span>
                                {{
                                    theme === 'system'
                                        ? 'System'
                                        : theme === 'light'
                                          ? 'Light'
                                          : 'Dark'
                                }}
                            </span>
                        </button>
                    </div>

                    <!-- Mobile Authentication -->

                    <div
                        class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-800"
                    >
                        <template v-if="!isAuthenticated">
                            <div class="grid grid-cols-2 gap-3">
                                <Link
                                    href="/login"
                                    @click="closeMobileMenu"
                                    class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-700 transition hover:border-green-300 hover:text-green-600 dark:border-slate-700 dark:text-slate-200 dark:hover:border-green-700 dark:hover:text-green-400"
                                >
                                    Login
                                </Link>

                                <Link
                                    href="/register"
                                    @click="closeMobileMenu"
                                    class="rounded-xl bg-green-600 px-4 py-3 text-center text-sm font-semibold text-white transition hover:bg-green-700"
                                >
                                    Create Account
                                </Link>
                            </div>
                        </template>

                        <template v-else>
                            <Link
                                href="/profile"
                                @click="closeMobileMenu"
                                class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 dark:bg-slate-900"
                            >
                                <span
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 text-sm font-bold text-green-700 dark:bg-green-900/40 dark:text-green-400"
                                >
                                    {{ user.name?.charAt(0)?.toUpperCase() }}
                                </span>

                                <div>
                                    <p
                                        class="text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ user.name }}
                                    </p>

                                    <p
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        My Account
                                    </p>
                                </div>
                            </Link>

                            <button
                                type="button"
                                @click="logout"
                                class="mt-2 w-full rounded-xl px-4 py-3 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:hover:bg-red-950/30"
                            >
                                Sign Out
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page -->

        <main>
            <slot />
        </main>

        <!-- Footer -->

        <footer
            class="border-t border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-900"
        >
            <div
                class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8"
            >
                <div
                    class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <!-- Brand -->

                    <div>
                        <Link
                            href="/"
                            class="inline-flex items-center"
                        >
                            <img
                                :src="logoUrl"
                                alt="Go Pharmacy"
                                class="h-14 w-auto object-contain"
                            />
                        </Link>

                        <p
                            class="mt-4 max-w-xs text-sm leading-6 text-slate-600 dark:text-slate-400"
                        >
                            Good health. Made simple. Your trusted destination
                            for medicines, healthcare products and everyday
                            wellness.
                        </p>
                    </div>

                    <!-- Shop -->

                    <div>
                        <h3
                            class="text-sm font-semibold text-slate-900 dark:text-white"
                        >
                            Shop
                        </h3>

                        <div
                            class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-400"
                        >
                            <Link
                                href="/shop"
                                class="block transition hover:text-green-600"
                            >
                                Medicines
                            </Link>

                            <Link
                                href="/shop"
                                class="block transition hover:text-green-600"
                            >
                                Health & Wellness
                            </Link>

                            <Link
                                href="/shop"
                                class="block transition hover:text-green-600"
                            >
                                Personal Care
                            </Link>

                            <Link
                                href="/shop"
                                class="block transition hover:text-green-600"
                            >
                                Baby Care
                            </Link>
                        </div>
                    </div>

                    <!-- Help -->

                    <div>
                        <h3
                            class="text-sm font-semibold text-slate-900 dark:text-white"
                        >
                            Help
                        </h3>

                        <div
                            class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-400"
                        >
                            <a
                                href="#"
                                class="block transition hover:text-green-600"
                            >
                                Contact Us
                            </a>

                            <a
                                href="#"
                                class="block transition hover:text-green-600"
                            >
                                Delivery Information
                            </a>

                            <a
                                href="#"
                                class="block transition hover:text-green-600"
                            >
                                Prescription Guide
                            </a>

                            <a
                                href="#"
                                class="block transition hover:text-green-600"
                            >
                                FAQs
                            </a>
                        </div>
                    </div>

                    <!-- Legal -->

                    <div>
                        <h3
                            class="text-sm font-semibold text-slate-900 dark:text-white"
                        >
                            Legal
                        </h3>

                        <div
                            class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-400"
                        >
                            <a
                                href="#"
                                class="block transition hover:text-green-600"
                            >
                                Privacy Policy
                            </a>

                            <a
                                href="#"
                                class="block transition hover:text-green-600"
                            >
                                Terms & Conditions
                            </a>

                            <a
                                href="#"
                                class="block transition hover:text-green-600"
                            >
                                Refund Policy
                            </a>

                            <a
                                href="#"
                                class="block transition hover:text-green-600"
                            >
                                Prescription Policy
                            </a>
                        </div>
                    </div>
                </div>

                <div
                    class="mt-10 border-t border-slate-200 pt-6 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400"
                >
                    © {{ new Date().getFullYear() }} Go Pharmacy.
                    All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>