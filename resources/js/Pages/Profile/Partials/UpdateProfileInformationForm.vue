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
        <div>
            <InputLabel
                for="name"
                value="Full Name"
                class="text-sm font-semibold text-slate-700"
            />

            <TextInput
                id="name"
                v-model="form.name"
                type="text"
                class="mt-2 block w-full rounded-xl border-slate-200 focus:border-green-500 focus:ring-green-500"
                required
                autofocus
                autocomplete="name"
            />

            <InputError
                class="mt-2"
                :message="form.errors.name"
            />
        </div>

        <div>
            <InputLabel
                for="email"
                value="Email Address"
                class="text-sm font-semibold text-slate-700"
            />

            <TextInput
                id="email"
                v-model="form.email"
                type="email"
                class="mt-2 block w-full rounded-xl border-slate-200 focus:border-green-500 focus:ring-green-500"
                required
                autocomplete="username"
            />

            <InputError
                class="mt-2"
                :message="form.errors.email"
            />
        </div>

        <div
            v-if="mustVerifyEmail && user.email_verified_at === null"
            class="rounded-xl border border-amber-200 bg-amber-50 p-4"
        >
            <p class="text-sm text-amber-800">
                Your email address is not verified.
            </p>

            <Link
                :href="route('verification.send')"
                method="post"
                as="button"
                class="mt-2 text-sm font-semibold text-green-700 underline hover:text-green-800"
            >
                Resend verification email
            </Link>

            <p
                v-show="status === 'verification-link-sent'"
                class="mt-2 text-sm font-medium text-green-700"
            >
                A new verification link has been sent.
            </p>
        </div>

        <div class="flex items-center gap-4">
            <PrimaryButton
                :disabled="form.processing"
                class="rounded-xl bg-green-600 px-5 py-3 font-semibold hover:bg-green-700 focus:bg-green-700 active:bg-green-800"
            >
                Save Changes
            </PrimaryButton>

            <Transition
                enter-active-class="transition ease-in-out"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out"
                leave-to-class="opacity-0"
            >
                <p
                    v-if="form.recentlySuccessful"
                    class="text-sm font-medium text-green-600"
                >
                    Changes saved successfully.
                </p>
            </Transition>
        </div>
    </form>
</template>