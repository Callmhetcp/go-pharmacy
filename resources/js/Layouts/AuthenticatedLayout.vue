<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div
        class="min-h-screen bg-slate-50 text-slate-900 transition-colors duration-200 dark:bg-slate-950 dark:text-slate-100"
    >
        <!-- =========================================================
             HEADER
        ========================================================== -->
        <header
            class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur transition-colors duration-200 dark:border-slate-800 dark:bg-slate-900/95"
        >
            <div
                class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <!-- Logo -->
                <Link
                    :href="route('dashboard')"
                    class="flex items-center"
                >
                    <span
                        class="text-2xl font-extrabold tracking-tight text-green-600"
                    >
                        GO<span class="text-slate-900 dark:text-white">
                            PHARMACY
                        </span>
                    </span>
                </Link>

                <!-- Desktop Navigation -->
                <nav class="hidden items-center gap-8 md:flex">
                    <Link
                        :href="route('dashboard')"
                        class="text-sm font-semibold transition"
                        :class="
                            route().current('dashboard')
                                ? 'text-green-600 dark:text-green-400'
                                : 'text-slate-600 hover:text-green-600 dark:text-slate-300 dark:hover:text-green-400'
                        "
                    >
                        Home
                    </Link>

                    <Link
                        :href="route('shop.index')"
                        class="text-sm font-medium text-slate-600 transition hover:text-green-600 dark:text-slate-300 dark:hover:text-green-400"
                    >
                        Shop
                    </Link>

                    <Link
                        href="/"
                        class="text-sm font-medium text-slate-600 transition hover:text-green-600 dark:text-slate-300 dark:hover:text-green-400"
                    >
                        Categories
                    </Link>

                    <Link
                        href="/"
                        class="text-sm font-medium text-slate-600 transition hover:text-green-600 dark:text-slate-300 dark:hover:text-green-400"
                    >
                        Health & Wellness
                    </Link>
                </nav>

                <!-- Desktop Account -->
                <div class="hidden items-center gap-3 md:flex">

                    <!-- Cart -->
                    <Link
                        :href="route('cart.index')"
                        class="relative rounded-xl p-3 text-slate-600 transition hover:bg-green-50 hover:text-green-600 dark:text-slate-300 dark:hover:bg-green-950/40 dark:hover:text-green-400"
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
                            class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-green-600 px-1 text-xs font-bold text-white"
                        >
                            0
                        </span>
                    </Link>

                    <!-- User -->
                    <div
                        class="flex items-center gap-3 border-l border-slate-200 pl-4 dark:border-slate-700"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 font-bold text-green-700 dark:bg-green-900/50 dark:text-green-400"
                        >
                            {{
                                $page.props.auth.user.name
                                    ?.charAt(0)
                                    ?.toUpperCase()
                            }}
                        </div>

                        <div class="hidden lg:block">
                            <p
                                class="text-sm font-semibold text-slate-900 dark:text-white"
                            >
                                {{ $page.props.auth.user.name }}
                            </p>

                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $page.props.auth.user.email }}
                            </p>
                        </div>

                        <div class="relative">
                            <details class="group">
                                <summary
                                    class="flex cursor-pointer list-none items-center rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
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
                                            d="m6 9 6 6 6-6"
                                        />
                                    </svg>
                                </summary>

                                <div
                                    class="absolute right-0 mt-2 w-52 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <Link
                                        :href="route('profile.edit')"
                                        class="block px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-green-50 hover:text-green-700 dark:text-slate-200 dark:hover:bg-green-950/40 dark:hover:text-green-400"
                                    >
                                        My Profile
                                    </Link>

                                    <Link
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        class="block w-full px-4 py-3 text-left text-sm font-medium text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/30"
                                    >
                                        Log Out
                                    </Link>
                                </div>
                            </details>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <button
                    type="button"
                    class="rounded-xl p-3 text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800 md:hidden"
                    @click="
                        showingNavigationDropdown =
                            !showingNavigationDropdown
                    "
                    :aria-expanded="showingNavigationDropdown"
                >
                    <svg
                        v-if="!showingNavigationDropdown"
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
                            d="M6 6l12 12M18 6L6 18"
                        />
                    </svg>
                </button>
            </div>

            <!-- =====================================================
                 MOBILE NAVIGATION
            ====================================================== -->
            <div
                v-if="showingNavigationDropdown"
                class="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 md:hidden"
            >
                <div class="space-y-1 px-4 py-4">
                    <Link
                        :href="route('dashboard')"
                        class="block rounded-xl px-4 py-3 text-sm font-semibold transition"
                        :class="
                            route().current('dashboard')
                                ? 'bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-400'
                                : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800'
                        "
                    >
                        Home
                    </Link>

                    <Link
                        :href="route('shop.index')"
                        class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Shop
                    </Link>

                    <Link
                        href="/"
                        class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Categories
                    </Link>

                    <Link
                        href="/"
                        class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Health & Wellness
                    </Link>

                    <!-- Mobile Cart -->
                    <Link
                        :href="route('cart.index')"
                        class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-green-50 hover:text-green-700 dark:text-slate-200 dark:hover:bg-green-950/40 dark:hover:text-green-400"
                    >
                        Cart
                    </Link>
                </div>

                <!-- Mobile Account -->
                <div
                    class="border-t border-slate-200 px-4 py-4 dark:border-slate-800"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-full bg-green-100 font-bold text-green-700 dark:bg-green-900/50 dark:text-green-400"
                        >
                            {{
                                $page.props.auth.user.name
                                    ?.charAt(0)
                                    ?.toUpperCase()
                            }}
                        </div>

                        <div>
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{ $page.props.auth.user.name }}
                            </p>

                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                {{ $page.props.auth.user.email }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-1">
                        <Link
                            :href="route('profile.edit')"
                            class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
                        >
                            My Profile
                        </Link>

                        <Link
                            :href="route('orders.index')"
                            class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
                        >
                            My Orders
                        </Link>

                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="block w-full rounded-xl px-4 py-3 text-left text-sm font-medium text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/30"
                        >
                            Log Out
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- =========================================================
             PAGE HEADER
        ========================================================== -->
        <div
            v-if="$slots.header"
            class="border-b border-slate-200 bg-white transition-colors duration-200 dark:border-slate-800 dark:bg-slate-900"
        >
            <div
                class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
            >
                <slot name="header" />
            </div>
        </div>

        <!-- =========================================================
             PAGE CONTENT
        ========================================================== -->
        <main>
            <slot />
        </main>

        <!-- =========================================================
             FOOTER
        ========================================================== -->
        <footer
            class="mt-16 border-t border-slate-200 bg-white transition-colors duration-200 dark:border-slate-800 dark:bg-slate-900"
        >
            <div
                class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <div
                            class="text-xl font-extrabold tracking-tight text-green-600"
                        >
                            GO<span class="text-slate-900 dark:text-white">
                                PHARMACY
                            </span>
                        </div>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Good health. Made simple.
                        </p>
                    </div>

                    <p class="text-sm text-slate-400 dark:text-slate-500">
                        © {{ new Date().getFullYear() }} Go Pharmacy.
                        All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
