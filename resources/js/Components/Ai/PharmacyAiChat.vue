<script setup>
import { ref, nextTick } from 'vue';

const isOpen = ref(false);
const message = ref('');
const messages = ref([
    {
        role: 'assistant',
        content: 'Hi! I’m the Go Pharmacy Assistant. How can I help you today?',
    },
]);
const isLoading = ref(false);

const messagesContainer = ref(null);

const scrollToBottom = async () => {
    await nextTick();

    if (messagesContainer.value) {
        messagesContainer.value.scrollTop =
            messagesContainer.value.scrollHeight;
    }
};

const sendMessage = async () => {
    const text = message.value.trim();

    if (!text || isLoading.value) {
        return;
    }

    messages.value.push({
        role: 'user',
        content: text,
    });

    message.value = '';
    isLoading.value = true;

    await scrollToBottom();

    try {
        const response = await fetch('/api/v1/ai/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content'),
            },
            body: JSON.stringify({
                message: text,
            }),
        });
        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.message || 'Unable to contact the AI assistant.'
            );
        }

        messages.value.push({
            role: 'assistant',
            content: data.message,
        });
    } catch (error) {
        messages.value.push({
            role: 'assistant',
            content:
                'Sorry, I’m unable to respond right now. Please try again later.',
        });
    } finally {
        isLoading.value = false;
        await scrollToBottom();
    }
};

const handleKeydown = (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
};
</script>

<template>
    <div class="fixed bottom-5 right-5 z-50">
        <!-- Chat Panel -->
        <div
            v-if="isOpen"
            class="mb-4 flex h-[520px] w-[360px] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl"
        >
            <!-- Header -->
            <div class="flex items-center justify-between bg-green-700 px-4 py-4 text-white">
                <div>
                    <h2 class="font-semibold">Go Pharmacy Assistant</h2>
                    <p class="text-xs text-green-100">
                        Here to help you find products
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
                <div
                    v-for="(item, index) in messages"
                    :key="index"
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
                                : 'rounded-bl-md bg-white text-gray-800 shadow-sm'
                        "
                    >
                        {{ item.content }}
                    </div>
                </div>

                <!-- Loading -->
                <div
                    v-if="isLoading"
                    class="flex justify-start"
                >
                    <div class="rounded-2xl rounded-bl-md bg-white px-4 py-3 text-sm text-gray-500 shadow-sm">
                        Thinking...
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
                        placeholder="Ask about our products..."
                        class="max-h-24 min-h-10 flex-1 resize-none rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none placeholder:text-gray-400 focus:border-green-600 focus:ring-1 focus:ring-green-600"
                        :disabled="isLoading"
                        @keydown="handleKeydown"
                    />

                    <button
                        type="submit"
                        :disabled="isLoading || !message.trim()"
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