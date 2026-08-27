<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

// Public logo path
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

                <!-- Form -->
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
