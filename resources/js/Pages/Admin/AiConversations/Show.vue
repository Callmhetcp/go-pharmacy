<script setup>
import {
    Link,
    router,
    useForm,
} from '@inertiajs/vue3';

import {
    nextTick,
    onMounted,
    onUnmounted,
    ref,
} from 'vue';

import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    conversation: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    message: '',
});

const endForm = useForm({});

const messagesContainer = ref(null);

const sendReply = () => {
    form.post(
        route(
            'admin.ai.conversations.reply',
            props.conversation.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                form.reset();

                scrollToLatestMessage();
            },
        }
    );
};

const endConversation = () => {
    const confirmed = window.confirm(
        'Are you sure you want to end this pharmacist conversation? The customer will be returned to the AI assistant.'
    );

    if (!confirmed) {
        return;
    }

    endForm.post(
        route(
            'admin.ai.conversations.end',
            props.conversation.id
        )
    );
};

const scrollToLatestMessage = async () => {
    await nextTick();

    if (!messagesContainer.value) {
        return;
    }

    messagesContainer.value.scrollTop =
        messagesContainer.value.scrollHeight;
};

let conversationPolling = null;

const startConversationPolling = () => {
    conversationPolling = setInterval(() => {
        if (
            form.processing ||
            endForm.processing
        ) {
            return;
        }

        router.reload({
            only: ['conversation'],
            preserveScroll: true,
            preserveState: true,

            onSuccess: () => {
                scrollToLatestMessage();
            },
        });
    }, 3000);
};

onMounted(() => {
    startConversationPolling();

    scrollToLatestMessage();
});

onUnmounted(() => {
    if (conversationPolling) {
        clearInterval(conversationPolling);
    }
});
</script>

<template>
    <AdminLayout>
        <div
            class="min-h-screen bg-slate-50 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
        >
            <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('admin.ai.conversations.index')"
                        class="inline-flex items-center text-sm font-medium text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300"
                    >
                        ← Back to conversations
                    </Link>

                    <div
                        class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p
                                class="mb-2 text-sm font-semibold uppercase tracking-wide text-green-600 dark:text-green-400"
                            >
                                Pharmacist Conversation
                            </p>

                            <h1 class="text-3xl font-bold tracking-tight">
                                {{ conversation.user?.name ?? 'Customer' }}
                            </h1>

                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{ conversation.user?.email }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <span
                                class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold"
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
                                        ? 'Waiting for pharmacist'
                                        : 'With pharmacist'
                                }}
                            </span>

                            <button
                                v-if="
                                    conversation.status ===
                                    'with_pharmacist'
                                "
                                type="button"
                                :disabled="endForm.processing"
                                class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-red-900/50 dark:bg-slate-900 dark:text-red-400 dark:hover:bg-red-950/30"
                                @click="endConversation"
                            >
                                {{
                                    endForm.processing
                                        ? 'Ending...'
                                        : 'End conversation'
                                }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Conversation -->
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="border-b border-slate-200 px-5 py-4 dark:border-slate-800"
                    >
                        <h2 class="font-semibold">
                            Conversation history
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Review the conversation before responding.
                        </p>
                    </div>

                    <!-- Messages -->
                    <div
                        ref="messagesContainer"
                        class="max-h-[600px] space-y-4 overflow-y-auto p-5"
                    >
                        <div
                            v-for="message in conversation.messages"
                            :key="message.id"
                            class="flex"
                            :class="
                                message.sender_type === 'customer'
                                    ? 'justify-start'
                                    : 'justify-end'
                            "
                        >
                            <div
                                class="max-w-[85%] rounded-2xl px-4 py-3 sm:max-w-[70%]"
                                :class="
                                    message.sender_type === 'customer'
                                        ? 'rounded-bl-md bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-white'
                                        : message.sender_type === 'pharmacist'
                                            ? 'rounded-br-md bg-green-600 text-white'
                                            : 'rounded-br-md bg-green-50 text-green-900 dark:bg-green-950/40 dark:text-green-100'
                                "
                            >
                                <p
                                    class="mb-1 text-xs font-semibold opacity-70"
                                >
                                    {{
                                        message.sender_type === 'customer'
                                            ? 'Customer'
                                            : message.sender_type ===
                                                'pharmacist'
                                              ? 'Pharmacist'
                                              : 'Go Pharmacy Assistant'
                                    }}
                                </p>

                                <p
                                    class="whitespace-pre-wrap text-sm leading-6"
                                >
                                    {{ message.message }}
                                </p>

                                <p
                                    class="mt-2 text-[11px] opacity-60"
                                >
                                    {{
                                        new Date(
                                            message.created_at
                                        ).toLocaleString()
                                    }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="!conversation.messages?.length"
                            class="py-12 text-center"
                        >
                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                No messages in this conversation.
                            </p>
                        </div>
                    </div>

                    <!-- Reply form -->
                    <div
                        class="border-t border-slate-200 p-5 dark:border-slate-800"
                    >
                        <form
                            @submit.prevent="sendReply"
                            class="space-y-3"
                        >
                            <label
                                for="message"
                                class="block text-sm font-semibold"
                            >
                                Reply as pharmacist
                            </label>

                            <textarea
                                id="message"
                                v-model="form.message"
                                rows="4"
                                maxlength="2000"
                                placeholder="Write a reply to the customer..."
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                                :disabled="form.processing"
                            ></textarea>

                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Maximum 2000 characters.
                                </p>

                                <button
                                    type="submit"
                                    :disabled="
                                        form.processing ||
                                        !form.message.trim()
                                    "
                                    class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {{
                                        form.processing
                                            ? 'Sending...'
                                            : 'Send reply'
                                    }}
                                </button>
                            </div>

                            <p
                                v-if="form.errors.message"
                                class="text-sm text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.message }}
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>