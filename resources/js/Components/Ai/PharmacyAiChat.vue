<script setup>
import {
    nextTick,
    onMounted,
    onUnmounted,
    ref,
} from 'vue';

const isOpen = ref(false);
const message = ref('');
const isLoading = ref(false);
const conversationStatus = ref('active');
const messagesContainer = ref(null);

let conversationPolling = null;

const createWelcomeMessage = () => ({
    role: 'assistant',
    content:
        'Hi! I’m the Go Pharmacy Assistant. How can I help you today?',
    created_at: new Date().toISOString(),
});

const messages = ref([
    createWelcomeMessage(),
]);

const scrollToBottom = async () => {
    await nextTick();

    if (messagesContainer.value) {
        messagesContainer.value.scrollTop =
            messagesContainer.value.scrollHeight;
    }
};

const formatMessageTime = (createdAt) => {
    if (!createdAt) {
        return '';
    }

    return new Date(createdAt).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatMessageDate = (createdAt) => {
    if (!createdAt) {
        return '';
    }

    const date = new Date(createdAt);
    const today = new Date();

    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);

    const isSameDay = (first, second) => {
        return (
            first.getFullYear() === second.getFullYear() &&
            first.getMonth() === second.getMonth() &&
            first.getDate() === second.getDate()
        );
    };

    if (isSameDay(date, today)) {
        return 'Today';
    }

    if (isSameDay(date, yesterday)) {
        return 'Yesterday';
    }

    return date.toLocaleDateString([], {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const shouldShowDateSeparator = (index) => {
    if (index === 0) {
        return true;
    }

    const currentMessage = messages.value[index];
    const previousMessage = messages.value[index - 1];

    if (
        !currentMessage?.created_at ||
        !previousMessage?.created_at
    ) {
        return false;
    }

    const currentDate = new Date(
        currentMessage.created_at
    );

    const previousDate = new Date(
        previousMessage.created_at
    );

    return (
        currentDate.getFullYear() !== previousDate.getFullYear() ||
        currentDate.getMonth() !== previousDate.getMonth() ||
        currentDate.getDate() !== previousDate.getDate()
    );
};

const loadConversation = async () => {
    try {
        const response = await fetch(
            '/api/v1/ai/conversation',
            {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                },
            }
        );

        if (!response.ok) {
            return;
        }

        const data = await response.json();

        conversationStatus.value =
            data.status || 'active';

        if (!data.messages?.length) {
            messages.value = [
                createWelcomeMessage(),
            ];

            await scrollToBottom();

            return;
        }

        const hasNewMessages =
            data.messages.length !== messages.value.length;

        messages.value = data.messages;

        if (hasNewMessages) {
            await scrollToBottom();
        }
    } catch (error) {
        // Keep the current conversation if loading or polling fails.
    }
};

const startConversationPolling = () => {
    conversationPolling = setInterval(async () => {
        if (!isOpen.value) {
            return;
        }

        await loadConversation();
    }, 5000);
};

const sendMessage = async () => {
    const text = message.value.trim();

    if (!text || isLoading.value) {
        return;
    }

    messages.value.push({
        role: 'user',
        content: text,
        created_at: new Date().toISOString(),
    });

    message.value = '';
    isLoading.value = true;

    await scrollToBottom();

    try {
        const response = await fetch(
            '/api/v1/ai/chat',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document
                        .querySelector(
                            'meta[name="csrf-token"]'
                        )
                        ?.getAttribute('content'),
                },
                body: JSON.stringify({
                    message: text,
                }),
            }
        );

        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.message ||
                    'Unable to contact the AI assistant.'
            );
        }

        messages.value.push({
            role: data.role || 'assistant',
            content: data.message,
            created_at: new Date().toISOString(),
        });

        if (data.role === 'system') {
            conversationStatus.value =
                'waiting_for_pharmacist';
        }
    } catch (error) {
        messages.value.push({
            role: 'assistant',
            content:
                'Sorry, I’m unable to respond right now. Please try again later.',
            created_at: new Date().toISOString(),
        });
    } finally {
        isLoading.value = false;

        await scrollToBottom();
    }
};

const handleKeydown = (event) => {
    if (
        event.key === 'Enter' &&
        !event.shiftKey
    ) {
        event.preventDefault();

        sendMessage();
    }
};

onMounted(async () => {
    await loadConversation();

    startConversationPolling();
});

onUnmounted(() => {
    if (conversationPolling) {
        clearInterval(conversationPolling);
    }
});
</script>

<template>
    <div class="fixed bottom-5 right-5 z-50">
        <!-- Chat Panel -->
        <div
            v-if="isOpen"
            class="mb-4 flex h-[520px] w-[360px] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl"
        >
            <!-- Header -->
            <div
                class="flex items-center justify-between bg-green-700 px-4 py-4 text-white"
            >
                <div>
                    <h2 class="font-semibold">
                        Go Pharmacy Assistant
                    </h2>

                    <p class="text-xs text-green-100">
                        <span
                            v-if="
                                conversationStatus ===
                                'waiting_for_pharmacist'
                            "
                        >
                            Waiting for a pharmacist
                        </span>

                        <span
                            v-else-if="
                                conversationStatus ===
                                'with_pharmacist'
                            "
                        >
                            Connected to a pharmacist
                        </span>

                        <span v-else>
                            Here to help you find products
                        </span>
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-lg p-2 hover:bg-green-600"
                    aria-label="Close chat"
                    @click="isOpen = false"
                >
                    ✕
                </button>
            </div>

            <!-- Messages -->
            <div
                ref="messagesContainer"
                class="flex-1 space-y-4 overflow-y-auto bg-gray-50 p-4"
            >
                <!-- Waiting status -->
                <div
                    v-if="
                        conversationStatus ===
                        'waiting_for_pharmacist'
                    "
                    class="rounded-xl bg-amber-50 px-3 py-2 text-center text-xs text-amber-700"
                >
                    Your request has been sent to a Go Pharmacy
                    pharmacist. Please keep this chat open while you
                    wait for a response.
                </div>

                <!-- Pharmacist connected status -->
                <div
                    v-else-if="
                        conversationStatus ===
                        'with_pharmacist'
                    "
                    class="rounded-xl bg-green-50 px-3 py-2 text-center text-xs text-green-700"
                >
                    You are now chatting with a Go Pharmacy
                    pharmacist.
                </div>

                <!-- Conversation messages -->
                <template
                    v-for="(item, index) in messages"
                    :key="index"
                >
                    <!-- Date separator -->
                    <div
                        v-if="
                            shouldShowDateSeparator(index)
                        "
                        class="my-3 flex justify-center"
                    >
                        <span
                            class="rounded-full bg-gray-200 px-3 py-1 text-[11px] font-medium text-gray-600"
                        >
                            {{
                                formatMessageDate(
                                    item.created_at
                                )
                            }}
                        </span>
                    </div>

                    <!-- Message -->
                    <div
                        class="flex"
                        :class="
                            item.role === 'user'
                                ? 'justify-end'
                                : 'justify-start'
                        "
                    >
                        <div
                            class="max-w-[85%] rounded-2xl px-4 py-3 text-sm"
                            :class="
                                item.role === 'user'
                                    ? 'rounded-br-md bg-green-700 text-white'
                                    : item.role === 'system'
                                        ? 'rounded-xl bg-amber-50 text-amber-800 shadow-sm dark:bg-amber-950/30 dark:text-amber-200'
                                        : item.role === 'pharmacist'
                                            ? 'rounded-bl-md bg-green-100 text-green-900 shadow-sm dark:bg-green-950/40 dark:text-green-100'
                                            : 'rounded-bl-md bg-white text-gray-800 shadow-sm'
                            "
                        >
                            <!-- Pharmacist label -->
                            <p
                                v-if="
                                    item.role === 'pharmacist'
                                "
                                class="mb-1 text-xs font-semibold text-green-700 dark:text-green-300"
                            >
                                Go Pharmacy Pharmacist
                            </p>

                            <!-- System label -->
                            <p
                                v-if="
                                    item.role === 'system'
                                "
                                class="mb-1 text-xs font-semibold text-amber-700 dark:text-amber-300"
                            >
                                Go Pharmacy
                            </p>

                            <p>
                                {{ item.content }}
                            </p>

                            <!-- Message timestamp -->
                            <p
                                class="mt-1 text-[10px] opacity-60"
                            >
                                {{
                                    formatMessageTime(
                                        item.created_at
                                    )
                                }}
                            </p>
                        </div>
                    </div>
                </template>

                <!-- Loading -->
                <div
                    v-if="isLoading"
                    class="flex justify-start"
                >
                    <div
                        class="rounded-2xl rounded-bl-md bg-white px-4 py-3 text-sm text-gray-500 shadow-sm"
                    >
                        <span
                            v-if="
                                conversationStatus ===
                                'with_pharmacist'
                            "
                        >
                            Sending...
                        </span>

                        <span v-else>
                            Thinking...
                        </span>
                    </div>
                </div>
            </div>

            <!-- Input -->
            <form
                class="border-t border-gray-200 bg-white p-3"
                @submit.prevent="sendMessage"
            >
                <div class="flex items-end gap-2">
                    <textarea
                        v-model="message"
                        rows="1"
                        maxlength="2000"
                        :placeholder="
                            conversationStatus ===
                            'with_pharmacist'
                                ? 'Message the pharmacist...'
                                : 'Ask about our products...'
                        "
                        class="max-h-24 min-h-10 flex-1 resize-none rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none placeholder:text-gray-400 focus:border-green-600 focus:ring-1 focus:ring-green-600"
                        :disabled="isLoading"
                        @keydown="handleKeydown"
                    ></textarea>

                    <button
                        type="submit"
                        :disabled="
                            isLoading ||
                            !message.trim()
                        "
                        class="rounded-xl bg-green-700 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-green-800 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Send
                    </button>
                </div>
            </form>
        </div>

        <!-- Floating Button -->
        <button
            v-else
            type="button"
            class="flex h-14 w-14 items-center justify-center rounded-full bg-green-700 text-xl text-white shadow-lg transition hover:bg-green-800 hover:shadow-xl"
            aria-label="Open Go Pharmacy Assistant"
            @click="isOpen = true"
        >
            ✦
        </button>
    </div>
</template>