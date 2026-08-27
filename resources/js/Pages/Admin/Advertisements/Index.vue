<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAdminTheme } from '@/Composables/useAdminTheme';

defineOptions({
    layout: AdminLayout,
});

const props = defineProps({
    advertisements: {
        type: Object,
        required: true,
    },
});

const { theme } = useAdminTheme();

const isDark = computed(() => theme.value === 'dark');

const deleteAdvertisement = (advertisement) => {
    if (
        !confirm(
            `Are you sure you want to delete "${advertisement.title}"?`
        )
    ) {
        return;
    }

    router.delete(
        `/admin/advertisements/${advertisement.id}`,
        {
            preserveScroll: true,
        }
    );
};

const imageUrl = (advertisement) => {
    if (advertisement.image_url) {
        return advertisement.image_url;
    }

    if (!advertisement.image) {
        return null;
    }

    if (
        advertisement.image.startsWith('http://') ||
        advertisement.image.startsWith('https://')
    ) {
        return advertisement.image;
    }

    return `/storage/${advertisement.image}`;
};

const formatDate = (value) => {
    if (!value) {
        return 'Not set';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleString('en-NG', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const scheduleStatus = (advertisement) => {
    const now = new Date();

    if (!advertisement.is_active) {
        return 'Inactive';
    }

    const startsAt = advertisement.starts_at
        ? new Date(advertisement.starts_at)
        : null;

    const endsAt = advertisement.ends_at
        ? new Date(advertisement.ends_at)
        : null;

    if (startsAt && now < startsAt) {
        return 'Scheduled';
    }

    if (endsAt && now > endsAt) {
        return 'Expired';
    }

    return 'Live';
};

const scheduleStatusClasses = (advertisement) => {
    const status = scheduleStatus(advertisement);

    if (status === 'Live') {
        return isDark.value
            ? 'bg-green-950 text-green-400'
            : 'bg-green-100 text-green-700';
    }

    if (status === 'Scheduled') {
        return isDark.value
            ? 'bg-blue-950 text-blue-400'
            : 'bg-blue-100 text-blue-700';
    }

    if (status === 'Expired') {
        return isDark.value
            ? 'bg-red-950 text-red-400'
            : 'bg-red-100 text-red-700';
    }

    return isDark.value
        ? 'bg-slate-800 text-slate-400'
        : 'bg-slate-100 text-slate-600';
};
</script>

<template>
    <div
        class="min-h-screen p-4 transition-colors duration-300 sm:p-6 lg:p-8"
        :class="
            isDark
                ? 'bg-slate-950 text-slate-100'
                : 'bg-slate-50 text-slate-900'
        "
    >
        <!-- =========================================================
             HEADER
        ========================================================== -->
        <div
            class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <p class="text-sm font-semibold text-green-600">
                    Marketing
                </p>

                <h1
                    class="mt-1 text-2xl font-extrabold tracking-tight"
                    :class="
                        isDark
                            ? 'text-white'
                            : 'text-slate-950'
                    "
                >
                    Advertisements
                </h1>

                <p
                    class="mt-1 text-sm"
                    :class="
                        isDark
                            ? 'text-slate-400'
                            : 'text-slate-500'
                    "
                >
                    Manage promotional banners displayed on the
                    Go Pharmacy website.
                </p>
            </div>

            <Link
                href="/admin/advertisements/create"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-green-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-green-700"
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
                        d="M12 5v14M5 12h14"
                    />
                </svg>

                New Advertisement
            </Link>
        </div>

        <!-- =========================================================
             EMPTY STATE
        ========================================================== -->
        <div
            v-if="!advertisements.data?.length"
            class="rounded-2xl border border-dashed p-12 text-center transition-colors duration-300"
            :class="
                isDark
                    ? 'border-slate-700 bg-slate-900'
                    : 'border-slate-300 bg-white'
            "
        >
            <div
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl"
                :class="
                    isDark
                        ? 'bg-green-950 text-green-400'
                        : 'bg-green-50 text-green-600'
                "
            >
                <svg
                    class="h-8 w-8"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 2.5-3 2 2 2.5-3 3 4H7l1-0Z"
                    />
                </svg>
            </div>

            <h2
                class="mt-5 text-lg font-bold"
                :class="
                    isDark
                        ? 'text-white'
                        : 'text-slate-900'
                "
            >
                No advertisements yet
            </h2>

            <p
                class="mx-auto mt-2 max-w-md text-sm"
                :class="
                    isDark
                        ? 'text-slate-400'
                        : 'text-slate-500'
                "
            >
                Create your first homepage advertisement and
                link it directly to a product.
            </p>

            <Link
                href="/admin/advertisements/create"
                class="mt-6 inline-flex rounded-xl bg-green-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-green-700"
            >
                Create Advertisement
            </Link>
        </div>

        <!-- =========================================================
             ADVERTISEMENT GRID
        ========================================================== -->
        <div
            v-else
            class="grid gap-6 xl:grid-cols-2"
        >
            <div
                v-for="advertisement in advertisements.data"
                :key="advertisement.id"
                class="overflow-hidden rounded-2xl border shadow-sm transition-colors duration-300"
                :class="
                    isDark
                        ? 'border-slate-800 bg-slate-900'
                        : 'border-slate-200 bg-white'
                "
            >
                <!-- Image -->
                <div
                    class="relative aspect-[16/7] overflow-hidden"
                    :class="
                        isDark
                            ? 'bg-slate-950'
                            : 'bg-slate-100'
                    "
                >
                    <img
                        v-if="imageUrl(advertisement)"
                        :src="imageUrl(advertisement)"
                        :alt="advertisement.title"
                        class="h-full w-full object-cover"
                    />

                    <div
                        v-else
                        class="flex h-full items-center justify-center text-sm"
                        :class="
                            isDark
                                ? 'text-slate-500'
                                : 'text-slate-400'
                        "
                    >
                        No image
                    </div>

                    <!-- Active Status -->
                    <div class="absolute right-3 top-3">
                        <span
                            class="rounded-full px-3 py-1 text-xs font-bold shadow-sm"
                            :class="
                                advertisement.is_active
                                    ? isDark
                                        ? 'bg-green-950 text-green-400'
                                        : 'bg-green-100 text-green-700'
                                    : isDark
                                        ? 'bg-slate-800 text-slate-400'
                                        : 'bg-slate-100 text-slate-600'
                            "
                        >
                            {{
                                advertisement.is_active
                                    ? 'Active'
                                    : 'Inactive'
                            }}
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-5">

                    <!-- Title + Order -->
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div class="min-w-0">
                            <h2
                                class="truncate text-lg font-bold"
                                :class="
                                    isDark
                                        ? 'text-white'
                                        : 'text-slate-950'
                                "
                            >
                                {{ advertisement.title }}
                            </h2>

                            <p
                                v-if="advertisement.description"
                                class="mt-1 line-clamp-2 text-sm"
                                :class="
                                    isDark
                                        ? 'text-slate-400'
                                        : 'text-slate-500'
                                "
                            >
                                {{ advertisement.description }}
                            </p>
                        </div>

                        <div
                            class="shrink-0 rounded-lg px-3 py-2 text-center"
                            :class="
                                isDark
                                    ? 'bg-slate-800'
                                    : 'bg-slate-50'
                            "
                        >
                            <p
                                class="text-[10px] font-bold uppercase tracking-wider"
                                :class="
                                    isDark
                                        ? 'text-slate-500'
                                        : 'text-slate-400'
                                "
                            >
                                Order
                            </p>

                            <p
                                class="text-sm font-bold"
                                :class="
                                    isDark
                                        ? 'text-slate-200'
                                        : 'text-slate-700'
                                "
                            >
                                {{ advertisement.sort_order ?? 0 }}
                            </p>
                        </div>
                    </div>

                    <!-- Schedule Status -->
                    <div class="mt-5">
                        <span
                            class="inline-flex rounded-full px-3 py-1 text-xs font-bold"
                            :class="
                                scheduleStatusClasses(
                                    advertisement
                                )
                            "
                        >
                            {{ scheduleStatus(advertisement) }}
                        </span>
                    </div>

                    <!-- Schedule -->
                    <div
                        class="mt-4 grid gap-3 sm:grid-cols-2"
                    >
                        <div
                            class="rounded-xl border p-3"
                            :class="
                                isDark
                                    ? 'border-slate-800 bg-slate-950'
                                    : 'border-slate-100 bg-slate-50'
                            "
                        >
                            <p
                                class="text-[10px] font-bold uppercase tracking-wider"
                                :class="
                                    isDark
                                        ? 'text-slate-500'
                                        : 'text-slate-400'
                                "
                            >
                                Starts
                            </p>

                            <p
                                class="mt-1 text-sm font-semibold"
                                :class="
                                    isDark
                                        ? 'text-slate-200'
                                        : 'text-slate-700'
                                "
                            >
                                {{
                                    formatDate(
                                        advertisement.starts_at
                                    )
                                }}
                            </p>
                        </div>

                        <div
                            class="rounded-xl border p-3"
                            :class="
                                isDark
                                    ? 'border-slate-800 bg-slate-950'
                                    : 'border-slate-100 bg-slate-50'
                            "
                        >
                            <p
                                class="text-[10px] font-bold uppercase tracking-wider"
                                :class="
                                    isDark
                                        ? 'text-slate-500'
                                        : 'text-slate-400'
                                "
                            >
                                Expires
                            </p>

                            <p
                                class="mt-1 text-sm font-semibold"
                                :class="
                                    isDark
                                        ? 'text-slate-200'
                                        : 'text-slate-700'
                                "
                            >
                                {{
                                    formatDate(
                                        advertisement.ends_at
                                    )
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Target Product -->
                    <div
                        v-if="advertisement.product"
                        class="mt-5 flex items-center gap-3 rounded-xl border p-3"
                        :class="
                            isDark
                                ? 'border-slate-800 bg-slate-950'
                                : 'border-slate-100 bg-slate-50'
                        "
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                            :class="
                                isDark
                                    ? 'bg-green-950 text-green-400'
                                    : 'bg-green-100 text-green-700'
                            "
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
                                    stroke-width="1.8"
                                    d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 0v9m8-4.5-8 4.5m0 0-8-4.5"
                                />
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <p
                                class="text-[10px] font-bold uppercase tracking-wider"
                                :class="
                                    isDark
                                        ? 'text-slate-500'
                                        : 'text-slate-400'
                                "
                            >
                                Links to
                            </p>

                            <p
                                class="truncate text-sm font-semibold"
                                :class="
                                    isDark
                                        ? 'text-slate-200'
                                        : 'text-slate-800'
                                "
                            >
                                {{ advertisement.product.name }}
                            </p>
                        </div>
                    </div>

                    <!-- Button Text -->
                    <div
                        class="mt-4 flex items-center justify-between rounded-xl border px-3 py-2.5"
                        :class="
                            isDark
                                ? 'border-slate-800'
                                : 'border-slate-100'
                        "
                    >
                        <span
                            class="text-xs font-medium"
                            :class="
                                isDark
                                    ? 'text-slate-500'
                                    : 'text-slate-400'
                            "
                        >
                            CTA Button
                        </span>

                        <span
                            class="text-sm font-semibold text-green-600"
                        >
                            {{ advertisement.button_text }}
                        </span>
                    </div>

                    <!-- Actions -->
                    <div
                        class="mt-5 flex items-center justify-end gap-2"
                    >
                        <Link
                            :href="`/admin/advertisements/${advertisement.id}/edit`"
                            class="rounded-xl border px-4 py-2.5 text-sm font-semibold transition"
                            :class="
                                isDark
                                    ? 'border-slate-700 text-slate-200 hover:border-green-700 hover:bg-green-950/30 hover:text-green-400'
                                    : 'border-slate-200 text-slate-600 hover:border-green-300 hover:bg-green-50 hover:text-green-700'
                            "
                        >
                            Edit
                        </Link>

                        <button
                            type="button"
                            @click="
                                deleteAdvertisement(
                                    advertisement
                                )
                            "
                            class="rounded-xl border px-4 py-2.5 text-sm font-semibold transition"
                            :class="
                                isDark
                                    ? 'border-red-900 text-red-400 hover:bg-red-950/40'
                                    : 'border-red-200 text-red-600 hover:bg-red-50'
                            "
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================
             PAGINATION
        ========================================================== -->
        <div
            v-if="advertisements.links?.length > 3"
            class="mt-8 flex flex-wrap justify-center gap-2"
        >
            <Link
                v-for="(link, index) in advertisements.links"
                :key="index"
                :href="link.url ?? '#'"
                class="rounded-lg border px-3 py-2 text-sm font-medium transition"
                :class="
                    link.active
                        ? 'border-green-600 bg-green-600 text-white'
                        : isDark
                            ? 'border-slate-700 bg-slate-900 text-slate-300 hover:bg-slate-800'
                            : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                "
                v-html="link.label"
            />
        </div>
    </div>
</template>