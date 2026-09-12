<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

const logo = '/images/branding/go-pharmacy-logo-transparent.png';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head title="Create Account" />

<div class="min-h-screen bg-slate-50 px-4 py-12 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-md">

        <!-- Brand -->
        <div class="mb-8 text-center">
            <Link
                href="/"
                class="inline-flex items-center justify-center"
            >
                <img
                    :src="logo"
                    alt="Go Pharmacy"
                    class="h-20 w-auto object-contain"
                />
            </Link>
        </div>

        <!-- Card -->
        <div
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8"
        >
            <!-- Heading -->
            <div class="mb-8">
                <h1
                    class="text-2xl font-bold tracking-tight text-slate-900"
                >
                    Create your account
                </h1>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Join Go Pharmacy and manage your healthcare needs
                    from one secure account.
                </p>
            </div>

            <!-- =================================================
                EMAIL / PASSWORD REGISTRATION
            ================================================== -->
            <form
                class="space-y-5"
                @submit.prevent="submit"
            >
                <!-- Name -->
                <div>
                    <label
                        for="name"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Full name
                    </label>

                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        name="name"
                        autocomplete="name"
                        autofocus
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:bg-white focus:ring-4 focus:ring-green-500/10"
                        placeholder="Enter your full name"
                    />

                    <p
                        v-if="form.errors.name"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- Email -->
                <div>
                    <label
                        for="email"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Email address
                    </label>

                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        name="email"
                        autocomplete="username"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:bg-white focus:ring-4 focus:ring-green-500/10"
                        placeholder="you@example.com"
                    />

                    <p
                        v-if="form.errors.email"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- Password -->
                <div>
                    <label
                        for="password"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Password
                    </label>

                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        name="password"
                        autocomplete="new-password"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:bg-white focus:ring-4 focus:ring-green-500/10"
                        placeholder="Create a secure password"
                    />

                    <p
                        v-if="form.errors.password"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label
                        for="password_confirmation"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Confirm password
                    </label>

                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:bg-white focus:ring-4 focus:ring-green-500/10"
                        placeholder="Confirm your password"
                    />

                    <p
                        v-if="form.errors.password_confirmation"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="flex w-full items-center justify-center rounded-xl bg-green-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-green-600/20 transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span v-if="!form.processing">
                        Create Account
                    </span>

                    <span v-else>
                        Creating account...
                    </span>
                </button>
            </form>

            <!-- =================================================
                GOOGLE REGISTRATION
            ================================================== -->
            <div class="my-6 flex items-center gap-4">
                <div class="h-px flex-1 bg-slate-200"></div>

                <span
                    class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                >
                    Or continue with
                </span>

                <div class="h-px flex-1 bg-slate-200"></div>
            </div>

            <a
                href="/auth/google"
                class="flex w-full items-center justify-center gap-3 rounded-xl border border-slate-300 bg-white px-5 py-3.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50"
            >
                <!-- Google Icon -->
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        fill="#4285F4"
                        d="M21.35 12.23c0-.79-.07-1.55-.2-2.27H12v4.3h5.24a4.48 4.48 0 0 1-1.94 2.94v2.45h3.14c1.84-1.69 2.91-4.18 2.91-7.42Z"
                    />

                    <path
                        fill="#34A853"
                        d="M12 21.75c2.63 0 4.84-.87 6.45-2.36l-3.14-2.45c-.87.58-1.98.92-3.31.92-2.54 0-4.69-1.72-5.46-4.03H3.3v2.53A9.75 9.75 0 0 0 12 21.75Z"
                    />

                    <path
                        fill="#FBBC05"
                        d="M6.54 13.83A5.86 5.86 0 0 1 6.23 12c0-.64.11-1.26.31-1.83V7.64H3.3A9.75 9.75 0 0 0 2.25 12c0 1.57.38 3.05 1.05 4.36l3.24-2.53Z"
                    />

                    <path
                        fill="#EA4335"
                        d="M12 6.14c1.43 0 2.71.49 3.72 1.46l2.79-2.79C16.84 3.22 14.63 2.25 12 2.25a9.75 9.75 0 0 0-8.7 5.39l3.24 2.53C7.31 7.86 9.46 6.14 12 6.14Z"
                    />
                </svg>

                Continue with Google
            </a>

            <!-- Login -->
            <div class="mt-7 border-t border-slate-100 pt-6 text-center">
                <p class="text-sm text-slate-500">
                    Already have an account?

                    <Link
                        :href="route('login')"
                        class="font-semibold text-green-600 transition hover:text-green-700"
                    >
                        Sign in
                    </Link>
                </p>
            </div>
        </div>

        <!-- Security -->
        <p class="mt-6 text-center text-xs leading-5 text-slate-400">
            Your account information is securely protected by
            Go Pharmacy.
        </p>
    </div>
</div>

</template>
