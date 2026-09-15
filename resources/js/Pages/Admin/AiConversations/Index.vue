<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';


defineProps({
    conversations: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AdminLayout>
        <div
            class="min-h-screen bg-slate-50 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
        >
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <p
                        class="mb-2 text-sm font-semibold uppercase tracking-wide text-green-600 dark:text-green-400"
                    >
                        Admin Panel
                    </p>

                    <h1 class="text-3xl font-bold tracking-tight">
                        Pharmacist Conversations
                    </h1>

                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        Manage customer conversations that require pharmacist
                        assistance.
                    </p>
                </div>

                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        v-if="conversations.data?.length"
                        class="divide-y divide-slate-200 dark:divide-slate-800"
                    >
                        <Link
                            v-for="conversation in conversations.data"
                            :key="conversation.id"
                            :href="route('admin.ai.conversations.show', conversation.id)"
                            class="block p-5 transition hover:bg-slate-50 dark:hover:bg-slate-800/50"
                        >
                            <div
                                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div>
                                    <h2 class="font-semibold">
                                        {{ conversation.user?.name ?? 'Customer' }}
                                    </h2>

                                    <p
                                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        {{ conversation.user?.email }}
                                    </p>

                                    <p
                                        v-if="conversation.messages?.length"
                                        class="mt-3 text-sm text-slate-700 dark:text-slate-300"
                                    >
                                        {{ conversation.messages[0].message }}
                                    </p>
                                </div>

                                <div class="shrink-0">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="
                                            conversation.status ===
                                            'waiting_for_pharmacist'
                                                ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
                                                : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                        "
                                    >
                                        {{
                                            conversation.status ===
                                            'waiting_for_pharmacist'
                                                ? 'Waiting'
                                                : 'With pharmacist'
                                        }}
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <div
                        v-else
                        class="px-6 py-16 text-center"
                    >
                        <h2 class="text-lg font-semibold">
                            No pharmacist conversations
                        </h2>

                        <p
                            class="mx-auto mt-2 max-w-md text-sm text-slate-500 dark:text-slate-400"
                        >
                            Conversations that customers send to a pharmacist
                            will appear here.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>