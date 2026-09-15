<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const showCurrentPassword = ref(false);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            if (form.errors.password) {
                form.reset(
                    'password',
                    'password_confirmation'
                );
                passwordInput.value?.focus();
            }

            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <form
        class="space-y-6"
        @submit.prevent="updatePassword"
    >
        <!-- Current Password -->
        <div>
            <InputLabel
                for="current_password"
                value="Current Password"
                class="text-sm font-semibold text-slate-700 dark:text-slate-300"
            />

            <div class="relative mt-2">
                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    :type="showCurrentPassword ? 'text' : 'password'"
                    class="block w-full rounded-xl border-slate-200 bg-white pr-12 text-slate-900 placeholder:text-slate-400 focus:border-green-500 focus:ring-green-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                    autocomplete="current-password"
                />

                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-400 transition hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300"
                    :aria-label="
                        showCurrentPassword
                            ? 'Hide current password'
                            : 'Show current password'
                    "
                    @click="showCurrentPassword = !showCurrentPassword"
                >
                    <svg
                        v-if="!showCurrentPassword"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.64 0 8.577 3.01 9.964 7.178.07.21.07.434 0 .644C20.577 16.49 16.64 19.5 12 19.5c-4.64 0-8.577-3.01-9.964-7.178z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                    </svg>

                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.104 7.085 19.5 12 19.5c1.69 0 3.27-.42 4.66-1.16M6.228 6.228A10.45 10.45 0 0112 4.5c4.915 0 8.774 3.396 10.066 7.5a10.523 10.523 0 01-4.066 5.272M6.228 6.228L3 3m3.228 3.228l12.544 12.544"
                        />
                    </svg>
                </button>
            </div>

            <InputError
                :message="form.errors.current_password"
                class="mt-2"
            />
        </div>

        <!-- New Password -->
        <div>
            <InputLabel
                for="password"
                value="New Password"
                class="text-sm font-semibold text-slate-700 dark:text-slate-300"
            />

            <div class="relative mt-2">
                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    class="block w-full rounded-xl border-slate-200 bg-white pr-12 text-slate-900 placeholder:text-slate-400 focus:border-green-500 focus:ring-green-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                    autocomplete="new-password"
                />

                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-400 transition hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300"
                    :aria-label="
                        showPassword
                            ? 'Hide new password'
                            : 'Show new password'
                    "
                    @click="showPassword = !showPassword"
                >
                    <svg
                        v-if="!showPassword"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.64 0 8.577 3.01 9.964 7.178.07.21.07.434 0 .644C20.577 16.49 16.64 19.5 12 19.5c-4.64 0-8.577-3.01-9.964-7.178z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                    </svg>

                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.104 7.085 19.5 12 19.5c1.69 0 3.27-.42 4.66-1.16M6.228 6.228A10.45 10.45 0 0112 4.5c4.915 0 8.774 3.396 10.066 7.5a10.523 10.523 0 01-4.066 5.272M6.228 6.228L3 3m3.228 3.228l12.544 12.544"
                        />
                    </svg>
                </button>
            </div>

            <InputError
                :message="form.errors.password"
                class="mt-2"
            />
        </div>

        <!-- Confirm Password -->
        <div>
            <InputLabel
                for="password_confirmation"
                value="Confirm New Password"
                class="text-sm font-semibold text-slate-700 dark:text-slate-300"
            />

            <div class="relative mt-2">
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    :type="
                        showPasswordConfirmation
                            ? 'text'
                            : 'password'
                    "
                    class="block w-full rounded-xl border-slate-200 bg-white pr-12 text-slate-900 placeholder:text-slate-400 focus:border-green-500 focus:ring-green-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                    autocomplete="new-password"
                />

                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-400 transition hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300"
                    :aria-label="
                        showPasswordConfirmation
                            ? 'Hide password confirmation'
                            : 'Show password confirmation'
                    "
                    @click="
                        showPasswordConfirmation =
                            !showPasswordConfirmation
                    "
                >
                    <svg
                        v-if="!showPasswordConfirmation"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.64 0 8.577 3.01 9.964 7.178.07.21.07.434 0 .644C20.577 16.49 16.64 19.5 12 19.5c-4.64 0-8.577-3.01-9.964-7.178z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                    </svg>

                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.104 7.085 19.5 12 19.5c1.69 0 3.27-.42 4.66-1.16M6.228 6.228A10.45 10.45 0 0112 4.5c4.915 0 8.774 3.396 10.066 7.5a10.523 10.523 0 01-4.066 5.272M6.228 6.228L3 3m3.228 3.228l12.544 12.544"
                        />
                    </svg>
                </button>
            </div>

            <InputError
                :message="form.errors.password_confirmation"
                class="mt-2"
            />
        </div>

        <!-- Actions -->
        <div
            class="flex flex-wrap items-center gap-4 border-t border-slate-100 pt-6 dark:border-slate-800"
        >
            <PrimaryButton
                :disabled="form.processing"
                class="rounded-xl bg-green-600 px-5 py-3 font-semibold text-white hover:bg-green-700 focus:bg-green-700 active:bg-green-800 disabled:cursor-not-allowed disabled:opacity-60"
            >
                {{
                    form.processing
                        ? 'Updating...'
                        : 'Update Password'
                }}
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
                    Password updated successfully.
                </p>
            </Transition>
        </div>
    </form>
</template>
