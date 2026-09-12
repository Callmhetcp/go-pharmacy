<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
        default: false,
    },

    status: {
        type: String,
        default: null,
    },
});

const page = usePage();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => {
            form.reset('password');
        },
    });
};

const logo = '/images/branding/go-pharmacy-logo-transparent.png';
</script>

<template>
    <Head title="Login" />

<div class="min-h-screen bg-slate-50">
    <div class="grid min-h-screen lg:grid-cols-2">

        <!-- =====================================================
            LEFT — DESKTOP BRANDING
        ====================================================== -->
        <div
            class="hidden bg-green-700 p-12 text-white lg:flex lg:flex-col lg:justify-between"
        >
            <div>
                <img
                    :src="logo"
                    alt="Go Pharmacy"
                    class="h-16 w-auto object-contain"
                />

                <p class="mt-3 text-sm font-medium text-green-100">
                    GOOD HEALTH. MADE SIMPLE.
                </p>
            </div>

            <div class="max-w-lg">
                <p
                    class="mb-4 text-sm font-bold uppercase tracking-[0.25em] text-green-200"
                >
                    Welcome back
                </p>

                <h1 class="text-5xl font-bold leading-tight">
                    Your healthcare,
                    <span class="text-green-200">
                        made simpler.
                    </span>
                </h1>

                <p class="mt-6 text-lg leading-8 text-green-50">
                    Sign in to manage your orders, prescriptions,
                    healthcare products and account.
                </p>
            </div>

            <p class="text-sm text-green-200">
                © {{ new Date().getFullYear() }} Go Pharmacy
            </p>
        </div>

        <!-- =====================================================
            RIGHT — LOGIN
        ====================================================== -->
        <div class="flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-md">

                <!-- Mobile Logo -->
                <div class="mb-10 text-center lg:hidden">
                    <img
                        :src="logo"
                        alt="Go Pharmacy"
                        class="mx-auto h-16 w-auto object-contain"
                    />

                    <p
                        class="mt-3 text-xs font-semibold tracking-widest text-slate-500"
                    >
                        GOOD HEALTH. MADE SIMPLE.
                    </p>
                </div>

                <!-- Heading -->
                <div class="mb-8">
                    <h2
                        class="text-3xl font-bold tracking-tight text-slate-900"
                    >
                        Welcome back
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Sign in to your Go Pharmacy account.
                    </p>
                </div>

                <!-- Success / Status -->
                <div
                    v-if="status"
                    class="mb-6 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
                >
                    {{ status }}
                </div>

                <!-- Authentication Error -->
                <div
                    v-if="page.props.flash?.error"
                    class="mb-6 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700"
                >
                    {{ page.props.flash.error }}
                </div>

                <!-- =================================================
                    EMAIL / PASSWORD LOGIN
                ================================================== -->
                <form
                    class="space-y-5"
                    @submit.prevent="submit"
                >

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
                            autocomplete="username"
                            required
                            autofocus
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10"
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
                        <div class="mb-2 flex items-center justify-between">
                            <label
                                for="password"
                                class="block text-sm font-semibold text-slate-700"
                            >
                                Password
                            </label>

                            <Link
                                v-if="canResetPassword"
                                href="/forgot-password"
                                class="text-sm font-semibold text-green-600 hover:text-green-700"
                            >
                                Forgot password?
                            </Link>
                        </div>

                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-4 focus:ring-green-500/10"
                            placeholder="Enter your password"
                        />

                        <p
                            v-if="form.errors.password"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember Me -->
                    <label class="flex items-center gap-3">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                        />

                        <span class="text-sm text-slate-600">
                            Remember me
                        </span>
                    </label>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-xl bg-green-600 px-5 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span v-if="form.processing">
                            Signing in...
                        </span>

                        <span v-else>
                            Sign in
                        </span>
                    </button>
                </form>

                <!-- =================================================
                    GOOGLE LOGIN
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

                <!-- Register -->
                <p class="mt-8 text-center text-sm text-slate-500">
                    Don't have an account?

                    <Link
                        href="/register"
                        class="font-bold text-green-600 hover:text-green-700"
                    >
                        Create an account
                    </Link>
                </p>
            </div>
        </div>
    </div>
</div>

</template>
