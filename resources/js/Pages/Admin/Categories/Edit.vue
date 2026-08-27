<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.category.name ?? '',
    slug: props.category.slug ?? '',
    description: props.category.description ?? '',
    image: null,
    is_active: Boolean(props.category.is_active),
    sort_order: props.category.sort_order ?? 0,
    _method: 'PUT',
});

watch(
    () => form.name,
    (name) => {
        if (!name) {
            return;
        }

        if (name === props.category.name) {
            return;
        }

        form.slug = name
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    }
);

const submit = () => {
    form.post(`/admin/categories/${props.category.id}`, {
        forceFormData: true,
    });
};

const handleImage = (event) => {
    form.image = event.target.files[0] ?? null;
};
</script>

<template>
    <AdminLayout>
        <div class="min-h-screen bg-slate-50">
            <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">

                <!-- Header -->
                <div class="mb-8">
                    <Link
                        href="/admin/categories"
                        class="text-sm font-semibold text-green-600 hover:text-green-700"
                    >
                        ← Back to Categories
                    </Link>

                    <h1
                        class="mt-3 text-3xl font-bold tracking-tight text-slate-900"
                    >
                        Edit Category
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Update {{ category.name }}.
                    </p>
                </div>

                <form
                    @submit.prevent="submit"
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
                >
                    <div class="grid gap-6">

                        <!-- Name -->
                        <div>
                            <label
                                for="name"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Category Name
                            </label>

                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                            />

                            <p
                                v-if="form.errors.name"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Slug -->
                        <div>
                            <label
                                for="slug"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Slug
                            </label>

                            <input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                            />

                            <p
                                v-if="form.errors.slug"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ form.errors.slug }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label
                                for="description"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Description
                            </label>

                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="5"
                                class="w-full resize-none rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                            ></textarea>

                            <p
                                v-if="form.errors.description"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Existing Image -->
                        <div v-if="category.image">
                            <p
                                class="mb-2 text-sm font-semibold text-slate-700"
                            >
                                Current Image
                            </p>

                            <img
                                :src="`/storage/${category.image}`"
                                :alt="category.name"
                                class="h-32 w-32 rounded-xl object-cover"
                            />
                        </div>

                        <!-- New Image -->
                        <div>
                            <label
                                for="image"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Replace Image
                            </label>

                            <input
                                id="image"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp"
                                @change="handleImage"
                                class="block w-full rounded-xl border border-slate-200 bg-white text-sm text-slate-600 file:mr-4 file:border-0 file:bg-green-50 file:px-4 file:py-3 file:font-semibold file:text-green-700"
                            />

                            <p class="mt-2 text-xs text-slate-400">
                                Leave empty to keep the existing image.
                            </p>

                            <p
                                v-if="form.errors.image"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ form.errors.image }}
                            </p>
                        </div>

                        <!-- Sort -->
                        <div>
                            <label
                                for="sort_order"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Sort Order
                            </label>

                            <input
                                id="sort_order"
                                v-model="form.sort_order"
                                type="number"
                                min="0"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                            />

                            <p
                                v-if="form.errors.sort_order"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ form.errors.sort_order }}
                            </p>
                        </div>

                        <!-- Active -->
                        <label
                            class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4"
                        >
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-5 w-5 rounded border-slate-300 text-green-600 focus:ring-green-500"
                            />

                            <div>
                                <p class="text-sm font-semibold text-slate-900">
                                    Active Category
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Make this category visible on the store.
                                </p>
                            </div>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div
                        class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end"
                    >
                        <Link
                            href="/admin/categories"
                            class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Cancel
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{
                                form.processing
                                    ? 'Saving...'
                                    : 'Save Changes'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>