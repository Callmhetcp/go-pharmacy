<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },

    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});

const updateProfile = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <form
        class="space-y-6"
        @submit.prevent="updateProfile"
    >
        <!-- Full Name -->
        <div>
            <InputLabel
                for="name"
                value="Full Name"
                class="text-sm font-semibold text-slate-700 dark:text-slate-300"
            />

            <TextInput
                id="name"
                v-model="form.name"
                type="text"
                class="mt-2 block w-full rounded-xl border-slate-200 bg-white text-slate-900 placeholder:text-slate-400 focus:border-green-500 focus:ring-green-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                required
                autofocus
                autocomplete="name"
            />

            <InputError
                class="mt-2"
                :message="form.errors.name"
            />
        </div>

        <!-- Email -->
        <div>
            <InputLabel
                for="email"
                value="Email Address"
                class="text-sm font-semibold text-slate-700 dark:text-slate-300"
            />

            <TextInput
                id="email"
                v-model="form.email"
                type="email"
                class="mt-2 block w-full rounded-xl border-slate-200 bg-white text-slate-900 placeholder:text-slate-400 focus:border-green-500 focus:ring-green-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                required
                autocomplete="username"
            />

            <InputError
                class="mt-2"
                :message="form.errors.email"
            />
        </div>

        <!-- Email Verification -->
        <div
            v-if="
                mustVerifyEmail &&
                user.email_verified_at === null
            "
            class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/30"
        >
            <div class="flex gap-3">
                <div
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400"
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
                            d="M12 9v3.75m0 3.75h.007M10.29 3.86 2.82 17a2 2 0 0 0 1.74 3h14.88a2 2 0 0 0 1.74-3L13.71 3.86a2 2 0 0 0-3.42 0Z"
                        />
                    </svg>
                </div>

                <div>
                    <p
                        class="text-sm font-semibold text-amber-800 dark:text-amber-300"
                    >
                        Your email address is not verified.
                    </p>

                    <p
                        class="mt-1 text-sm text-amber-700 dark:text-amber-400"
                    >
                        Verify your email address to keep your
                        account secure.
                    </p>

                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="mt-3 text-sm font-semibold text-green-700 underline underline-offset-2 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300"
                    >
                        Resend verification email
                    </Link>

                    <p
                        v-show="status === 'verification-link-sent'"
                        class="mt-2 text-sm font-medium text-green-700 dark:text-green-400"
                    >
                        A new verification link has been sent.
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div
            class="flex flex-wrap items-center gap-4 border-t border-slate-100 pt-6 dark:border-slate-800"
        >
            <PrimaryButton
                :disabled="form.processing"
                class="rounded-xl bg-green-600 px-5 py-3 font-semibold text-white hover:bg-green-700 focus:bg-green-700 active:bg-green-800 disabled:cursor-not-allowed disabled:opacity-60"
            >
                {{ form.processing ? 'Saving...' : 'Save Changes' }}
            </PrimaryButton>

            <Transition
                enter-active-class="transition ease-in-out duration-200"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out duration-200"
                leave-to-class="opacity-0"
            >
                <p
                    v-if="form.recentlySuccessful"
                    class="text-sm font-medium text-green-600 dark:text-green-400"
                >
                    Changes saved successfully.
                </p>
            </Transition>
        </div>
    </form>
</template>
