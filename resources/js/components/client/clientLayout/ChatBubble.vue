<script setup lang="ts">
import { useClickOutside } from '@/composables/useClickOutside';
import { formatTime } from '@/lib/utils';
import chat from '@/routes/chat';
import message from '@/routes/message';
import { ChatType, MessageType } from '@/types/chat';
import axios from 'axios';
import {
    MoreHorizontal,
    Send,
    Check,
    SquarePen,
    MessageCircleOff,
    CircleX,
    Loader,
    MessageCircleMore,
} from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const isOpen = ref<boolean>(false);
const messageInput = ref<string>('');
const messagesContainer = ref<HTMLElement | null>(null);
const unreadCount = ref<number>(0);
const currentChat = ref<ChatType | null>(null);
const messages = ref<MessageType[]>([]);
const chatBubbleRef = ref<HTMLDivElement | null>(null);
const messageMenuDropdownRef = ref<HTMLElement | null>(null);
const messageMenuId = ref<number | null>(null);
const editingMessageId = ref<number | null>(null);
const isSending = ref<boolean>(false);
const isEditing = ref<boolean>(false);

const isUnsentOrRemoved = computed(() => {
    const message = messages.value.find((m) => m.id === messageMenuId.value);

    return !!(message?.unsent_at || message?.deleted_at);
});

const startEditing = (messageId: number) => {
    const message = messages.value.find((m) => m.id === messageId);
    if (!message) return;

    editingMessageId.value = messageId;
    messageInput.value = message.content;
    messageMenuId.value = null;

    nextTick(() => {
        const input = document.querySelector('textarea') as HTMLTextAreaElement;
        if (input) {
            input.focus();
            input.setSelectionRange(
                messageInput.value.length,
                messageInput.value.length,
            );
        }
    });
};

const cancelEditing = () => {
    editingMessageId.value = null;
    messageInput.value = '';
};

const updateMessage = async () => {
    if (!editingMessageId.value || !messageInput.value.trim()) {
        cancelEditing();
        return;
    }

    isEditing.value = true;

    try {
        const { data } = await axios.patch<MessageType>(
            message.update(editingMessageId.value).url,
            {
                content: messageInput.value,
            },
        );

        const index = messages.value.findIndex(
            (m) => m.id === editingMessageId.value,
        );

        if (index !== -1) {
            messages.value[index] = data;
        }

        if (
            currentChat.value &&
            currentChat.value.latest_message.id === editingMessageId.value
        ) {
            currentChat.value.latest_message = data;
        }

        cancelEditing();
    } catch (error) {
        console.error(error);
    } finally {
        isEditing.value = false;
    }
};

const unsendMessage = async (messageId: number) => {
    if (!messageId) return;

    try {
        messageMenuId.value = null;

        const { data } = await axios.patch<MessageType>(
            message.unsend(messageId).url,
        );

        if (data) {
            const index = messages.value.findIndex((m) => m.id === messageId);

            if (index !== -1) {
                messages.value[index] = data;
            }
        }
    } catch (error) {
        console.error(error);
    }
};

const removeMessage = async (messageId: number) => {
    if (!messageId) return;

    try {
        messageMenuId.value = null;

        const { data } = await axios.delete(message.destroy(messageId).url);

        if (data) {
            const index = messages.value.findIndex((m) => m.id === messageId);

            if (index !== -1) {
                messages.value[index] = data;
            }
        }
    } catch (error) {
        console.error(error);
    }
};

const dropdownStyle = ref<{ top: string; left: string }>({
    top: '0px',
    left: '0px',
});

const openMenu = (event: MouseEvent, messageId: number) => {
    if (messageMenuId.value === messageId) {
        messageMenuId.value = null;
        return;
    }

    messageMenuId.value = messageId;

    const rect = (event.currentTarget as HTMLElement).getBoundingClientRect();

    dropdownStyle.value = {
        top: `${rect.bottom + 8}px`,
        left: `${rect.left - 80}px`,
    };
};

const loadChat = async () => {
    try {
        const response = await axios.get(chat.getChat().url);

        if (response.data) {
            currentChat.value = response.data.chat as ChatType;
            messages.value = response.data.messages as MessageType[];
        }
    } catch (error) {
        console.log(error);
    }
};

const toggleChat = async () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        if (currentChat.value && messages.value && messages.value.length > 0) {
            await scrollToBottom();
            await markAsRead(currentChat.value.id);
            await loadChat();
        }
    }
};

const closeChat = () => {
    cancelEditing();
    isOpen.value = false;
};

const markAsRead = async (chatId: number) => {
    if (!currentChat.value?.latest_message) {
        return;
    }

    if (
        currentChat.value.latest_message.read_at !== null ||
        !currentChat.value.latest_message.is_from_restaurant
    ) {
        return;
    }

    try {
        await axios.patch(chat.markAsRead(chatId).url);
    } catch (error) {
        console.log(error);
    }
};

const sendMessage = async () => {
    if (!messageInput.value.trim() || !currentChat.value) return;

    isSending.value = true;

    try {
        const response = await axios.post<MessageType>(chat.send().url, {
            content: messageInput.value,
        });

        const newMessage = response.data;
        if (newMessage) {
            messages.value.push(newMessage);

            if (currentChat.value) {
                currentChat.value.latest_message = {
                    ...newMessage,
                    read_at: '',
                };
            }
        }

        messageInput.value = '';
        await scrollToBottom();
    } catch (error) {
        console.log(error);
    } finally {
        isSending.value = false;
    }
};

const scrollToBottom = async () => {
    await nextTick();

    if (messagesContainer.value) {
        messagesContainer.value.scrollTop =
            messagesContainer.value.scrollHeight;
    }
};

const formatDate = (date: string) => {
    const now = new Date();
    const messageDate = new Date(date);
    const yesterday = new Date(now);
    yesterday.setDate(yesterday.getDate() - 1);

    if (messageDate.toDateString() === now.toDateString()) {
        return 'Today';
    } else if (messageDate.toDateString() === yesterday.toDateString()) {
        return 'Yesterday';
    } else {
        return messageDate.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
        });
    }
};

useClickOutside(chatBubbleRef, () => {
    if (isOpen.value) {
        isOpen.value = false;
        cancelEditing();
    }
});

useClickOutside(messageMenuDropdownRef, () => {
    messageMenuId.value = null;
});

const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (editingMessageId.value) {
            updateMessage();
        } else {
            sendMessage();
        }
    }
};

const handleEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        if (editingMessageId.value) {
            cancelEditing();
        } else {
            isOpen.value = false;
        }
    }
};

type EchoChannel = {
    listen: (event: string, callback: (e: MessageType) => void) => EchoChannel;
};

const channel = ref<EchoChannel | null>(null);

onMounted(async () => {
    document.addEventListener('keydown', handleEscape);

    await loadChat();

    if (currentChat.value) {
        channel.value = window.Echo.private(
            `chat.${currentChat.value.id}`,
        ).listen('.message-sent', async (e: MessageType) => {
            const exists = messages.value.some((m) => m.id === e.id);

            if (!exists) {
                await loadChat();
                await scrollToBottom();
            } else {
                const index = messages.value.findIndex((m) => m.id === e.id);

                if (index !== -1) {
                    messages.value[index] = e;
                }
            }

            if (
                currentChat.value &&
                currentChat.value.latest_message.id === e.id
            ) {
                await loadChat();
                await scrollToBottom();
            }
        });
    }
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleEscape);

    if (channel.value && currentChat.value) {
        window.Echo.leave(`private-chat.${currentChat.value.id}`);
    }
});

watch(
    messages,
    (newMessages) => {
        if (!newMessages) {
            unreadCount.value = 0;
            return;
        }

        unreadCount.value = newMessages.filter(
            (msg) => !msg.read_at && msg.is_from_restaurant,
        ).length;
    },
    { deep: true, immediate: true },
);

watch(messageMenuId, (id) => {
    if (id === null) {
        dropdownStyle.value = { top: '0px', left: '0px' };
    }
});
</script>

<template>
    <div ref="chatBubbleRef" class="fixed right-6 bottom-6 z-50">
        <button
            @click="toggleChat"
            class="relative flex h-14 w-14 items-center justify-center rounded-full bg-red-600 text-white shadow-lg transition-all hover:scale-105 hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none"
            :class="{ hidden: isOpen }"
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
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                />
            </svg>
            <span
                v-if="unreadCount > 0 && !isOpen"
                class="absolute -top-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <div
            v-if="isOpen && currentChat"
            @click="markAsRead(currentChat.id)"
            class="absolute right-0 bottom-0 mb-2 flex h-[500px] w-[380px] flex-col rounded-lg bg-white shadow-2xl transition-all duration-300"
        >
            <div
                class="flex items-center justify-between border-b border-gray-200 bg-gradient-to-r from-red-600 to-red-700 px-4 py-3"
            >
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20"
                        >
                            <svg
                                class="h-4 w-4 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white">Support Chat</h3>
                        <p class="text-xs text-red-100">We're here to help</p>
                    </div>
                </div>
                <button
                    @click="closeChat()"
                    class="rounded-full p-1 text-white/80 transition-colors hover:bg-white/20 hover:text-white"
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
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <div
                ref="messagesContainer"
                class="flex-1 overflow-y-auto bg-gray-50 p-4"
            >
                <div
                    v-if="!currentChat || messages?.length === 0"
                    class="flex h-full items-center justify-center"
                >
                    <div class="text-center text-gray-500">
                        <MessageCircleMore
                            class="mx-auto h-12 w-12 text-gray-400"
                        />
                        <p class="mt-2">No messages yet</p>
                        <p class="text-sm">Start a conversation with us!</p>
                    </div>
                </div>

                <div v-else>
                    <div v-for="(msg, index) in messages" :key="msg.id">
                        <div
                            v-if="
                                index === 0 ||
                                formatDate(msg.created_at) !==
                                    formatDate(messages[index - 1]?.created_at)
                            "
                            class="relative my-4 flex justify-center"
                        >
                            <span
                                class="rounded-full bg-gray-200 px-3 py-1 text-xs text-gray-600"
                            >
                                {{ formatDate(msg.created_at) }}
                            </span>
                        </div>

                        <div
                            class="relative mt-3 flex"
                            :class="
                                msg.is_from_restaurant
                                    ? 'justify-start'
                                    : 'justify-end'
                            "
                        >
                            <div
                                class="relative flex max-w-[70%] flex-col"
                                :class="
                                    msg.is_from_restaurant
                                        ? 'items-start'
                                        : 'items-end'
                                "
                            >
                                <div
                                    v-if="
                                        msg.edited_at &&
                                        !(msg.unsent_at || msg.deleted_at)
                                    "
                                    class="mb-1 text-xs text-gray-400"
                                >
                                    Modified at {{ formatTime(msg.edited_at) }}
                                </div>

                                <div
                                    :class="[
                                        'group relative rounded-lg px-4 py-2 text-sm',

                                        {
                                            'rounded-bl-none bg-white text-gray-800 shadow-sm':
                                                msg.is_from_restaurant &&
                                                !msg.unsent_at &&
                                                !msg.deleted_at,

                                            'rounded-br-none bg-red-600 text-white shadow-sm':
                                                !msg.is_from_restaurant &&
                                                !msg.unsent_at &&
                                                !msg.deleted_at,

                                            'bg-gray-100 text-gray-500 italic':
                                                msg.unsent_at || msg.deleted_at,
                                        },
                                    ]"
                                >
                                    <div
                                        v-if="
                                            !msg.is_from_restaurant &&
                                            !(msg.unsent_at || msg.deleted_at)
                                        "
                                        class="absolute top-1/2 -translate-y-1/2 transition-opacity"
                                        :class="[
                                            '-left-10',
                                            messageMenuId === msg.id
                                                ? 'opacity-100'
                                                : 'opacity-0 group-hover:opacity-100',
                                        ]"
                                    >
                                        <button
                                            @click.stop="
                                                openMenu($event, msg.id)
                                            "
                                            class="rounded-full p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600"
                                        >
                                            <MoreHorizontal class="h-5 w-5" />
                                        </button>
                                    </div>

                                    <p class="break-words whitespace-pre-wrap">
                                        {{ msg.content }}
                                    </p>

                                    <p
                                        v-if="
                                            !(msg.unsent_at || msg.deleted_at)
                                        "
                                        :class="[
                                            'mt-1 text-right text-xs',
                                            msg.is_from_restaurant
                                                ? 'text-gray-400'
                                                : 'text-red-200',
                                        ]"
                                    >
                                        {{ formatTime(msg.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="
                            currentChat.latest_message &&
                            currentChat.latest_message.read_at &&
                            !currentChat.latest_message.is_from_restaurant
                        "
                        class="mt-1 flex justify-end text-xs text-gray-400"
                    >
                        read at
                        {{ formatTime(currentChat.latest_message.read_at) }}
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 bg-white p-3">
                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <div
                            v-if="editingMessageId"
                            class="mb-1 flex items-center justify-between"
                        >
                            <div class="flex items-center gap-2">
                                <button
                                    @click.stop="cancelEditing()"
                                    class="text-xs text-gray-400 hover:text-gray-600"
                                >
                                    Cancel
                                </button>
                            </div>
                            <span class="text-xs text-gray-400"
                                >Press Enter to save, Escape to cancel</span
                            >
                        </div>
                        <textarea
                            v-model="messageInput"
                            @keydown="handleKeyDown"
                            placeholder="Type your message..."
                            rows="1"
                            class="block w-full resize-none rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                            style="min-height: 42px; max-height: 120px"
                            :disabled="isSending || isEditing"
                        ></textarea>
                    </div>
                    <button
                        v-if="editingMessageId"
                        @click="updateMessage()"
                        :disabled="!messageInput.trim()"
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-600 text-white transition-colors hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <Check v-if="!isEditing" class="h-5 w-5" />
                        <Loader v-else class="h-5 w-5 animate-spin" />
                    </button>
                    <button
                        v-else
                        @click="sendMessage"
                        :disabled="!messageInput.trim()"
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-600 text-white transition-colors hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <Send v-if="!isSending" class="h-5 w-5" />
                        <Loader v-else class="h-5 w-5 animate-spin" />
                    </button>
                </div>
            </div>
        </div>
    </div>
    <Teleport to="body">
        <div
            v-if="messageMenuId !== null"
            ref="messageMenuDropdownRef"
            @click.stop
            class="fixed z-50 w-48 rounded-md border bg-white py-1 shadow-lg"
            :style="dropdownStyle"
        >
            <button
                v-if="!isUnsentOrRemoved"
                @click="startEditing(messageMenuId)"
                class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
            >
                <SquarePen class="mr-2 h-4 w-4" />
                Edit
            </button>
            <button
                v-if="!isUnsentOrRemoved"
                @click="unsendMessage(messageMenuId)"
                class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
            >
                <MessageCircleOff class="mr-2 h-4 w-4" />
                Unsend
            </button>
            <button
                v-if="!isUnsentOrRemoved"
                @click="removeMessage(messageMenuId)"
                class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
            >
                <CircleX class="mr-2 h-4 w-4" />
                Remove
            </button>
        </div>
    </Teleport>
</template>

<style scoped></style>
