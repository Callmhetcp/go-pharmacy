<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => {
        passwordInput.value?.focus();
    });
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,

        onSuccess: () => {
            closeModal();
        },

        onError: () => {
            passwordInput.value?.focus();
        },

        onFinish: () => {
            form.reset();
        },
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <div>
        <p class="max-w-2xl text-sm leading-6 text-slate-500">
            Deleting your account will permanently remove your Go Pharmacy
            account and associated information. This action cannot be undone.
        </p>

        <button
            type="button"
            class="mt-5 rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-100"
            @click="confirmUserDeletion"
        >
            Delete My Account
        </button>

        <Modal
            :show="confirmingUserDeletion"
            @close="closeModal"
        >
            <div class="p-6">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-red-600"
                >
                    <svg
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 7 18 20H6L5 7m3 0V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m-7 4v6m4-6v6M4 7h16"
                        />
                    </svg>
                </div>

                <h2 class="mt-5 text-lg font-bold text-slate-900">
                    Delete your account?
                </h2>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    This will permanently delete your Go Pharmacy account.
                    Enter your password below to confirm.
                </p>

                <div class="mt-6">
                    <InputLabel
                        for="delete_password"
                        value="Password"
                        class="text-sm font-semibold text-slate-700"
                    />

                    <TextInput
                        id="delete_password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-2 block w-full rounded-xl border-slate-200 focus:border-red-500 focus:ring-red-500"
                        placeholder="Enter your password"
                        @keyup.enter="deleteUser"
                    />

                    <InputError
                        :message="form.errors.password"
                        class="mt-2"
                    />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton
                        class="rounded-xl"
                        @click="closeModal"
                    >
                        Cancel
                    </SecondaryButton>

                    <DangerButton
                        class="rounded-xl"
                        :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Delete Account
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </div>
</template>