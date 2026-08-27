<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },

    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="My Profile" />

    <AuthenticatedLayout>
        <div
            class="min-h-screen bg-slate-50 text-slate-900 transition-colors duration-200 dark:bg-slate-950 dark:text-slate-100"
        >
            <!-- PAGE HEADER -->
            <section
                class="border-b border-slate-200 bg-white transition-colors duration-200 dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-green-600 text-xl font-extrabold text-white shadow-sm"
                        >
                            GP
                        </div>

                        <div>
                            <p
                                class="text-sm font-semibold uppercase tracking-wider text-green-600 dark:text-green-400"
                            >
                                Go Pharmacy
                            </p>

                            <h1
                                class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white"
                            >
                                My Profile
                            </h1>

                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Manage your personal information and account
                                security.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- PROFILE CONTENT -->
            <main
                class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
            >
                <div class="grid gap-8 lg:grid-cols-12">

                    <!-- LEFT SIDEBAR -->
                    <aside class="lg:col-span-4">

                        <!-- Account Card -->
                        <div
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-colors duration-200 dark:border-slate-800 dark:bg-slate-900"
                        >
                            <!-- Account Header -->
                            <div
                                class="bg-green-600 px-6 py-8 text-white dark:bg-green-700"
                            >
                                <div
                                    class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white text-2xl font-extrabold text-green-600 shadow dark:bg-slate-100"
                                >
                                    GP
                                </div>

                                <h2 class="mt-5 text-xl font-bold">
                                    Your Account
                                </h2>

                                <p
                                    class="mt-2 text-sm leading-6 text-green-50"
                                >
                                    Manage your Go Pharmacy profile, password
                                    and account settings.
                                </p>
                            </div>

                            <!-- Sidebar Links -->
                            <div class="p-4">

                                <!-- Active Profile -->
                                <div
                                    class="flex items-center gap-3 rounded-xl bg-green-50 px-4 py-3 dark:bg-green-950/40"
                                >
                                    <div
                                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-green-100 text-green-600 dark:bg-green-900/60 dark:text-green-400"
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
                                                d="M20 21a8 8 0 0 0-16 0m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"
                                            />
                                        </svg>
                                    </div>

                                    <div>
                                        <p
                                            class="text-sm font-semibold text-green-700 dark:text-green-400"
                                        >
                                            Profile
                                        </p>

                                        <p
                                            class="text-xs text-green-600 dark:text-green-500"
                                        >
                                            Personal information
                                        </p>
                                    </div>
                                </div>

                                <!-- Security -->
                                <div
                                    class="mt-2 flex items-center gap-3 rounded-xl px-4 py-3 text-slate-500 dark:text-slate-400"
                                >
                                    <div
                                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800"
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
                                                d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2Zm10-11V7a4 4 0 0 0-8 0v2h8Z"
                                            />
                                        </svg>
                                    </div>

                                    <div>
                                        <p
                                            class="text-sm font-semibold text-slate-700 dark:text-slate-300"
                                        >
                                            Security
                                        </p>

                                        <p class="text-xs text-slate-500 dark:text-slate-500">
                                            Password protection
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security Card -->
                        <div
                            class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-colors duration-200 dark:border-slate-800 dark:bg-slate-900"
                        >
                            <h3
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                Account Security
                            </h3>

                            <p
                                class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                Keep your Go Pharmacy account secure by using a
                                strong password.
                            </p>

                            <div class="mt-5 space-y-4">

                                <!-- Secure -->
                                <div class="flex gap-3">
                                    <span
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-100 text-sm font-bold text-green-600 dark:bg-green-900/40 dark:text-green-400"
                                    >
                                        ✓
                                    </span>

                                    <div>
                                        <p
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            Secure account
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            Your account information is
                                            protected.
                                        </p>
                                    </div>
                                </div>

                                <!-- Password -->
                                <div class="flex gap-3">
                                    <span
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-100 text-sm font-bold text-green-600 dark:bg-green-900/40 dark:text-green-400"
                                    >
                                        ✓
                                    </span>

                                    <div>
                                        <p
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            Strong password
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            Change your password regularly.
                                        </p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </aside>

                    <!-- RIGHT CONTENT -->
                    <div class="space-y-8 lg:col-span-8">

                        <!-- PROFILE INFORMATION -->
                        <section
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-colors duration-200 dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div
                                class="border-b border-slate-200 px-6 py-6 sm:px-8 dark:border-slate-800"
                            >
                                <h2
                                    class="text-xl font-bold text-slate-900 dark:text-white"
                                >
                                    Personal Information
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Update your name and email address.
                                </p>
                            </div>

                            <div class="px-6 py-7 sm:px-8">
                                <UpdateProfileInformationForm
                                    :must-verify-email="mustVerifyEmail"
                                    :status="status"
                                />
                            </div>
                        </section>

                        <!-- PASSWORD -->
                        <section
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-colors duration-200 dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div
                                class="border-b border-slate-200 px-6 py-6 sm:px-8 dark:border-slate-800"
                            >
                                <h2
                                    class="text-xl font-bold text-slate-900 dark:text-white"
                                >
                                    Change Password
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Update your password to keep your account
                                    secure.
                                </p>
                            </div>

                            <div class="px-6 py-7 sm:px-8">
                                <UpdatePasswordForm />
                            </div>
                        </section>

                        <!-- DELETE -->
                        <section
                            class="overflow-hidden rounded-2xl border border-red-200 bg-white shadow-sm transition-colors duration-200 dark:border-red-900/50 dark:bg-slate-900"
                        >
                            <div
                                class="border-b border-red-100 bg-red-50 px-6 py-6 sm:px-8 dark:border-red-900/40 dark:bg-red-950/30"
                            >
                                <h2
                                    class="text-xl font-bold text-red-700 dark:text-red-400"
                                >
                                    Delete Account
                                </h2>

                                <p
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    Permanently remove your Go Pharmacy
                                    account.
                                </p>
                            </div>

                            <div class="px-6 py-7 sm:px-8">
                                <DeleteUserForm />
                            </div>
                        </section>

                    </div>
                </div>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
