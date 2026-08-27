<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();

const mobileMenuOpen = ref(false);
const darkMode = ref(false);

const user = computed(() => page.props.auth?.user ?? null);
const isAuthenticated = computed(() => !!user.value);

const cartCount = computed(() => {
    return Number(page.props.cart?.count ?? 0);
});

const logout = () => {
    router.post('/logout');
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

const toggleDarkMode = () => {
    darkMode.value = !darkMode.value;
};
</script>

<template>
    <div
        :class="[
            'min-h-screen transition-colors duration-300',
            darkMode
                ? 'bg-slate-950 text-white'
                : 'bg-white text-slate-900',
        ]"
    >
        <!-- HEADER -->
        <header
            :class="[
                'sticky top-0 z-50 border-b backdrop-blur',
                darkMode
                    ? 'border-slate-800 bg-slate-950/95'
                    : 'border-slate-200 bg-white/95',
            ]"
        >
            <div
                class="mx-auto flex h-20 max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8"
            >
                <!-- LOGO -->
                <Link
                    href="/"
                    class="shrink-0 text-2xl font-extrabold tracking-tight"
                >
                    <span class="text-green-600">GO</span>
                    <span
                        :class="
                            darkMode
                                ? 'text-white'
                                : 'text-slate-900'
                        "
                    >
                        PHARMACY
                    </span>
                </Link>

                <!-- SEARCH -->
                <div class="hidden flex-1 md:block">
                    <div class="relative mx-auto max-w-xl">
                        <input
                            type="search"
                            placeholder="Search medicines, health products and more..."
                            :class="[
                                'w-full rounded-xl border py-3 pl-11 pr-4 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20',
                                darkMode
                                    ? 'border-slate-700 bg-slate-900 text-white placeholder:text-slate-500'
                                    : 'border-slate-200 bg-slate-50 text-slate-900 placeholder:text-slate-400',
                            ]"
                        />

                        <svg
                            class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
                            />
                        </svg>
                    </div>
                </div>

                <!-- DESKTOP ACTIONS -->
                <div class="hidden items-center gap-1 md:flex">

                    <!-- THEME -->
                    <button
                        type="button"
                        @click="toggleDarkMode"
                        class="rounded-xl p-3 transition"
                        :class="
                            darkMode
                                ? 'text-slate-300 hover:bg-slate-800 hover:text-green-400'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-green-600'
                        "
                        aria-label="Toggle dark mode"
                    >
                        <svg
                            v-if="!darkMode"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
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

                        <svg
                            v-else
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"
                            />
                        </svg>
                    </button>

                    <!-- CART -->
                    <Link
                        href="/cart"
                        class="relative rounded-xl p-3 transition"
                        :class="
                            darkMode
                                ? 'text-slate-300 hover:bg-slate-800 hover:text-green-400'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-green-600'
                        "
                        aria-label="Shopping cart"
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
                                d="M3 3h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 7H6"
                            />
                            <circle cx="10" cy="20" r="1.5" />
                            <circle cx="18" cy="20" r="1.5" />
                        </svg>

                        <span
                            v-if="cartCount > 0"
                            class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-green-600 px-1 text-xs font-bold text-white"
                        >
                            {{ cartCount }}
                        </span>
                    </Link>

                    <!-- AUTH -->
                    <template v-if="!isAuthenticated">
                        <Link
                            href="/login"
                            :class="[
                                'rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                                darkMode
                                    ? 'text-slate-200 hover:bg-slate-800 hover:text-green-400'
                                    : 'text-slate-700 hover:bg-slate-100 hover:text-green-600',
                            ]"
                        >
                            Login
                        </Link>

                        <Link
                            href="/register"
                            class="rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700"
                        >
                            Create Account
                        </Link>
                    </template>

                    <template v-else>
                        <Link
                            href="/profile"
                            class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                            :class="
                                darkMode
                                    ? 'text-slate-200 hover:bg-slate-800 hover:text-green-400'
                                    : 'text-slate-700 hover:bg-slate-100 hover:text-green-600'
                            "
                        >
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-xs font-bold text-green-700"
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
                            class="rounded-xl px-3 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                        >
                            Logout
                        </button>
                    </template>
                </div>

                <!-- MOBILE MENU BUTTON -->
                <button
                    type="button"
                    class="ml-auto rounded-xl p-3 md:hidden"
                    :class="
                        darkMode
                            ? 'text-white hover:bg-slate-800'
                            : 'text-slate-700 hover:bg-slate-100'
                    "
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    :aria-expanded="mobileMenuOpen"
                >
                    <svg
                        v-if="!mobileMenuOpen"
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

                    <svg
                        v-else
                        class="h-6 w-6"
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

            <!-- DESKTOP NAV -->
            <nav
                class="hidden border-t md:block"
                :class="
                    darkMode
                        ? 'border-slate-800'
                        : 'border-slate-100'
                "
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
                        class="text-sm font-medium transition hover:text-green-600"
                        :class="
                            darkMode
                                ? 'text-slate-300'
                                : 'text-slate-600'
                        "
                    >
                        Shop
                    </Link>

                    <Link
                        href="/shop"
                        class="text-sm font-medium transition hover:text-green-600"
                        :class="
                            darkMode
                                ? 'text-slate-300'
                                : 'text-slate-600'
                        "
                    >
                        Categories
                    </Link>

                    <span
                        class="text-sm font-medium"
                        :class="
                            darkMode
                                ? 'text-slate-400'
                                : 'text-slate-500'
                        "
                    >
                        Health & Wellness
                    </span>

                    <span
                        class="text-sm font-medium"
                        :class="
                            darkMode
                                ? 'text-slate-400'
                                : 'text-slate-500'
                        "
                    >
                        Prescription
                    </span>

                    <span
                        class="text-sm font-medium"
                        :class="
                            darkMode
                                ? 'text-slate-400'
                                : 'text-slate-500'
                        "
                    >
                        About
                    </span>

                    <span
                        class="text-sm font-medium"
                        :class="
                            darkMode
                                ? 'text-slate-400'
                                : 'text-slate-500'
                        "
                    >
                        Contact
                    </span>
                </div>
            </nav>

            <!-- MOBILE NAV -->
            <div
                v-if="mobileMenuOpen"
                class="border-t md:hidden"
                :class="
                    darkMode
                        ? 'border-slate-800 bg-slate-950'
                        : 'border-slate-200 bg-white'
                "
            >
                <div class="space-y-2 px-4 py-4">

                    <!-- MOBILE SEARCH -->
                    <div class="relative mb-4">
                        <input
                            type="search"
                            placeholder="Search medicines and products..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm outline-none focus:border-green-500"
                        />

                        <svg
                            class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
                            />
                        </svg>
                    </div>

                    <Link
                        href="/"
                        @click="closeMobileMenu"
                        class="block rounded-xl bg-green-50 px-4 py-3 text-sm font-semibold text-green-700"
                    >
                        Home
                    </Link>

                    <Link
                        href="/shop"
                        @click="closeMobileMenu"
                        class="block rounded-xl px-4 py-3 text-sm font-medium"
                        :class="
                            darkMode
                                ? 'text-slate-200 hover:bg-slate-900'
                                : 'text-slate-700 hover:bg-slate-50'
                        "
                    >
                        Shop
                    </Link>

                    <Link
                        href="/shop"
                        @click="closeMobileMenu"
                        class="block rounded-xl px-4 py-3 text-sm font-medium"
                        :class="
                            darkMode
                                ? 'text-slate-200 hover:bg-slate-900'
                                : 'text-slate-700 hover:bg-slate-50'
                        "
                    >
                        Categories
                    </Link>

                    <Link
                        href="/cart"
                        @click="closeMobileMenu"
                        class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-medium"
                        :class="
                            darkMode
                                ? 'text-slate-200 hover:bg-slate-900'
                                : 'text-slate-700 hover:bg-slate-50'
                        "
                    >
                        <span>Shopping Cart</span>

                        <span
                            v-if="cartCount > 0"
                            class="rounded-full bg-green-600 px-2 py-0.5 text-xs font-bold text-white"
                        >
                            {{ cartCount }}
                        </span>
                    </Link>

                    <button
                        type="button"
                        @click="toggleDarkMode"
                        class="flex w-full items-center justify-between rounded-xl border px-4 py-3 text-sm font-semibold"
                        :class="
                            darkMode
                                ? 'border-slate-700 text-white'
                                : 'border-slate-200 text-slate-700'
                        "
                    >
                        <span>Appearance</span>

                        <span>
                            {{ darkMode ? 'Dark' : 'Light' }}
                        </span>
                    </button>

                    <div class="border-t border-slate-200 pt-4">
                        <template v-if="!isAuthenticated">
                            <div class="grid grid-cols-2 gap-3">
                                <Link
                                    href="/login"
                                    @click="closeMobileMenu"
                                    class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold"
                                >
                                    Login
                                </Link>

                                <Link
                                    href="/register"
                                    @click="closeMobileMenu"
                                    class="rounded-xl bg-green-600 px-4 py-3 text-center text-sm font-semibold text-white"
                                >
                                    Create Account
                                </Link>
                            </div>
                        </template>

                        <template v-else>
                            <Link
                                href="/profile"
                                @click="closeMobileMenu"
                                class="block rounded-xl bg-slate-50 p-4"
                            >
                                <p class="text-sm font-bold">
                                    {{ user.name }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    My Account
                                </p>
                            </Link>

                            <button
                                type="button"
                                @click="logout"
                                class="mt-2 w-full rounded-xl px-4 py-3 text-left text-sm font-semibold text-red-600 hover:bg-red-50"
                            >
                                Sign Out
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <main>
            <slot />
        </main>

        <!-- FOOTER -->
        <footer
            class="border-t"
            :class="
                darkMode
                    ? 'border-slate-800 bg-slate-900'
                    : 'border-slate-200 bg-slate-50'
            "
        >
            <div
                class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8"
            >
                <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">

                    <div>
                        <div class="text-2xl font-extrabold">
                            <span class="text-green-600">GO</span>
                            <span
                                :class="
                                    darkMode
                                        ? 'text-white'
                                        : 'text-slate-900'
                                "
                            >
                                PHARMACY
                            </span>
                        </div>

                        <p
                            class="mt-4 max-w-xs text-sm leading-6"
                            :class="
                                darkMode
                                    ? 'text-slate-400'
                                    : 'text-slate-600'
                            "
                        >
                            Good health. Made simple. Your trusted
                            destination for medicines, healthcare
                            products and everyday wellness.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold">
                            Shop
                        </h3>

                        <div class="mt-4 space-y-3 text-sm">
                            <Link
                                href="/shop"
                                class="block hover:text-green-600"
                            >
                                Medicines
                            </Link>

                            <Link
                                href="/shop"
                                class="block hover:text-green-600"
                            >
                                Health & Wellness
                            </Link>

                            <Link
                                href="/shop"
                                class="block hover:text-green-600"
                            >
                                Personal Care
                            </Link>

                            <Link
                                href="/shop"
                                class="block hover:text-green-600"
                            >
                                Baby Care
                            </Link>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold">
                            Help
                        </h3>

                        <div class="mt-4 space-y-3 text-sm">
                            <span class="block">Contact Us</span>
                            <span class="block">Delivery Information</span>
                            <span class="block">Prescription Guide</span>
                            <span class="block">FAQs</span>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold">
                            Legal
                        </h3>

                        <div class="mt-4 space-y-3 text-sm">
                            <span class="block">Privacy Policy</span>
                            <span class="block">Terms & Conditions</span>
                            <span class="block">Refund Policy</span>
                            <span class="block">Prescription Policy</span>
                        </div>
                    </div>
                </div>

                <div
                    class="mt-10 border-t pt-6 text-sm"
                    :class="
                        darkMode
                            ? 'border-slate-800 text-slate-400'
                            : 'border-slate-200 text-slate-500'
                    "
                >
                    © {{ new Date().getFullYear() }} Go Pharmacy.
                    All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>