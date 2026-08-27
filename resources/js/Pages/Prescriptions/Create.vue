<script setup>
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    doctor_name: '',
    hospital_name: '',
    prescription_date: '',
    notes: '',
    prescription_file: null,
});

const submit = () => {
    form.post('/prescriptions', {
        forceFormData: true,
    });
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 px-4 py-8 dark:bg-slate-950">
        <div class="mx-auto max-w-3xl">
            <div class="mb-8">
                <Link
                    href="/prescriptions"
                    class="text-sm font-semibold text-green-600 hover:text-green-700"
                >
                    ← Back to Prescriptions
                </Link>

                <h1
                    class="mt-4 text-3xl font-bold text-slate-950 dark:text-white"
                >
                    Upload Prescription
                </h1>

                <p
                    class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                >
                    Upload a valid prescription and our pharmacy team will
                    review it.
                </p>
            </div>

            <form
                @submit.prevent="submit"
                class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                        >
                            Doctor's Name
                        </label>

                        <input
                            v-model="form.doctor_name"
                            type="text"
                            placeholder="Dr. John Doe"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />

                        <p
                            v-if="form.errors.doctor_name"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.doctor_name }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                        >
                            Hospital / Clinic
                        </label>

                        <input
                            v-model="form.hospital_name"
                            type="text"
                            placeholder="Hospital or clinic name"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />

                        <p
                            v-if="form.errors.hospital_name"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.hospital_name }}
                        </p>
                    </div>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                    >
                        Prescription Date
                    </label>

                    <input
                        v-model="form.prescription_date"
                        type="date"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />

                    <p
                        v-if="form.errors.prescription_date"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.prescription_date }}
                    </p>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                    >
                        Prescription File
                    </label>

                    <input
                        type="file"
                        accept=".jpg,.jpeg,.png,.pdf"
                        @change="
                            form.prescription_file =
                                $event.target.files[0]
                        "
                        class="block w-full rounded-xl border border-slate-200 bg-white text-sm text-slate-600 file:mr-4 file:border-0 file:bg-green-50 file:px-4 file:py-3 file:font-semibold file:text-green-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                    />

                    <p class="mt-2 text-xs text-slate-400">
                        Accepted formats: JPG, JPEG, PNG and PDF. Maximum
                        file size: 10MB.
                    </p>

                    <p
                        v-if="form.errors.prescription_file"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.prescription_file }}
                    </p>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                    >
                        Additional Notes
                    </label>

                    <textarea
                        v-model="form.notes"
                        rows="5"
                        placeholder="Tell our pharmacy team anything important about this prescription..."
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    ></textarea>

                    <p
                        v-if="form.errors.notes"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.notes }}
                    </p>
                </div>

                <div
                    class="rounded-xl bg-green-50 p-4 text-sm text-green-800 dark:bg-green-950/30 dark:text-green-300"
                >
                    <strong>Important:</strong>
                    Please upload a clear and valid prescription. Our
                    pharmacy team will review it before any prescription
                    medicine is dispensed.
                </div>

                <div class="flex justify-end gap-3">
                    <Link
                        href="/prescriptions"
                        class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                    >
                        Cancel
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-green-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{
                            form.processing
                                ? 'Uploading...'
                                : 'Submit Prescription'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>