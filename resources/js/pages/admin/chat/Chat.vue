<script setup lang="ts">
import { useClickOutside } from '@/composables/useClickOutside';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { formatDateForHumans, formatTime } from '@/lib/utils';
import chat from '@/routes/admin/chat';
import message from '@/routes/admin/message';
import { useChatStore } from '@/stores/chat';
import { type BreadcrumbItem } from '@/types';
import { ChatType, MessageType } from '@/types/chat';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import {
    MoreHorizontal,
    Check,
    Loader,
    Send,
    SquarePen,
    MessageCircleOff,
    CircleX,
    MessageCircleMore,
} from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chats',
        href: chat.chats().url,
    },
];

const chatStore = useChatStore();
const { chats } = storeToRefs(chatStore);

const selectedChatId = ref<number | null>(null);
const messages = ref<MessageType[]>([]);
const newMessage = ref('');
const isSending = ref(false);
const isEditing = ref(false);
const searchQuery = ref('');
const messagesContainer = ref<HTMLElement | null>(null);
const messageMenuDropdownRef = ref<HTMLElement | null>(null);
const messageMenuId = ref<number | null>(null);
const editingMessageId = ref<number | null>(null);

const isUnsentOrRemoved = computed(() => {
    const message = messages.value.find((m) => m.id === messageMenuId.value);

    return !!(message?.unsent_at || message?.deleted_at);
});

const startEditing = (messageId: number) => {
    const message = messages.value.find((m) => m.id === messageId);
    if (!message) return;

    editingMessageId.value = messageId;
    newMessage.value = message.content;
    messageMenuId.value = null;

    nextTick(() => {
        const input = document.querySelector('textarea') as HTMLTextAreaElement;
        if (input) {
            input.focus();
            input.setSelectionRange(
                newMessage.value.length,
                newMessage.value.length,
            );
        }
    });
};

const cancelEditing = () => {
    editingMessageId.value = null;
    newMessage.value = '';
};

const updateMessage = async () => {
    if (!editingMessageId.value || !newMessage.value.trim()) {
        cancelEditing();
        return;
    }

    isEditing.value = true;

    try {
        const { data } = await axios.patch(
            message.update(editingMessageId.value).url,
            {
                content: newMessage.value,
            },
        );

        const index = messages.value.findIndex(
            (m) => m.id === editingMessageId.value,
        );

        if (index !== -1) {
            messages.value[index] = data;
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

    const dropdownWidth = 192;

    dropdownStyle.value = {
        top: `${rect.bottom + 8}px`,
        left: `${rect.left + rect.width / 2 - dropdownWidth / 2}px`,
    };
};

useClickOutside(messageMenuDropdownRef, () => {
    messageMenuId.value = null;
});

const selectedChat = computed(() => {
    scrollToBottom();
    return chats.value.find((chat) => chat.id === selectedChatId.value);
});

const filteredChats = computed(() => {
    if (!searchQuery.value) return chats.value;

    return chats.value.filter((chat) => {
        const userName = chat.user?.name?.toLowerCase() || '';
        const userEmail = chat.user?.email?.toLowerCase() || '';
        const query = searchQuery.value.toLowerCase();
        return userName.includes(query) || userEmail.includes(query);
    });
});

const isMessageUnread = (currentChat: ChatType) => {
    const latestMessage = currentChat.latest_message;
    if (!latestMessage) return false;
    return !latestMessage.is_from_restaurant && !latestMessage.read_at;
};

const selectChat = async (chatId: number) => {
    selectedChatId.value = chatId;
    await chatStore.markAsRead(chatId);
    await fetchMessages(chatId);
    await scrollToBottom();
};

const fetchMessages = async (chatId: number) => {
    try {
        const response = await axios.get<MessageType[]>(
            chat.getChatMessages(chatId).url,
        );

        messages.value = response.data;
    } catch (error) {
        console.log(error);
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim() || !selectedChatId.value || isSending.value)
        return;

    isSending.value = true;

    try {
        const response = await axios.post<MessageType>(
            chat.send(selectedChatId.value).url,
            {
                content: newMessage.value,
            },
        );

        if (response.data) {
            messages.value.push(response.data);

            const chat = chats.value.find((c) => c.id === selectedChatId.value);
            if (chat) {
                chat.latest_message = {
                    ...response.data,
                    read_at: '',
                };
            }
        }

        newMessage.value = '';
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

const getDateSeparator = (currentDate: string) => {
    const date = new Date(currentDate);
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (date >= today) {
        return 'Today';
    } else if (date >= yesterday) {
        return 'Yesterday';
    } else {
        return date.toLocaleDateString('en-Us', {
            month: 'short',
            day: 'numeric',
        });
    }
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((word) => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        if (editingMessageId.value) {
            updateMessage();
        } else {
            sendMessage();
        }
    }
};

const handleEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && editingMessageId.value) {
        cancelEditing();
    }
};

type EchoChannel = {
    listen: (event: string, callback: (e: MessageType) => void) => EchoChannel;
};

const channel = ref<EchoChannel | null>(null);

onMounted(async () => {
    document.addEventListener('keydown', handleEscape);

    await chatStore.fetchChats();
    await scrollToBottom();

    if (selectedChatId.value) {
        await fetchMessages(selectedChatId.value);

        channel.value = window.Echo.private(
            `chat.${selectedChatId.value}`,
        ).listen('.message-sent', async (e: MessageType) => {
            const exists = messages.value.some((m) => m.id === e.id);
            const isCurrentSelectedChat = e.chat_id === selectedChatId.value;

            if (!exists && isCurrentSelectedChat) {
                messages.value.push(e);
                await scrollToBottom();
            } else {
                const index = messages.value.findIndex((m) => m.id === e.id);

                if (index !== -1) {
                    messages.value[index] = e;
                }
            }
        });
    }
});

onUnmounted(async () => {
    document.removeEventListener('keydown', handleEscape);

    if (channel.value && selectedChatId.value) {
        window.Echo.leave(`private-chat.${selectedChatId.value}`);
    }
});

watch(selectedChatId, async (newId) => {
    if (newId) {
        await fetchMessages(newId);

        channel.value = window.Echo.private(
            `chat.${selectedChatId.value}`,
        ).listen('.message-sent', async (e: MessageType) => {
            const exists = messages.value.some((m) => m.id === e.id);
            const isCurrentSelectedChat = e.chat_id === selectedChatId.value;

            if (!exists && isCurrentSelectedChat) {
                messages.value.push(e);
                await scrollToBottom();
            } else {
                const index = messages.value.findIndex((m) => m.id === e.id);

                if (index !== -1) {
                    messages.value[index] = e;
                }
            }
        });
    }
});
</script>

<template>
    <Head title="Chats" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex min-h-[600px] flex-1 gap-4 md:min-h-[700px]">
                <div
                    class="bg-sidebar-background flex w-full flex-shrink-0 flex-col overflow-hidden rounded-xl border border-sidebar-border/70 md:w-[320px] dark:border-sidebar-border"
                >
                    <div
                        class="border-b border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <h2 class="mb-2 text-lg font-semibold text-foreground">
                            Conversations
                        </h2>
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search chats..."
                                class="w-full rounded-lg border border-border bg-background px-4 py-2 pl-10 text-sm transition-all duration-200 focus:ring-2 focus:ring-primary/50 focus:outline-none"
                            />
                            <svg
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto">
                        <div
                            v-if="filteredChats.length === 0"
                            class="flex h-full flex-col items-center justify-center p-4 text-center"
                        >
                            <svg
                                class="mb-3 h-12 w-12 text-muted-foreground"
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
                            <p class="text-sm text-muted-foreground">
                                No conversations found
                            </p>
                        </div>
                        <div
                            v-for="chat in filteredChats"
                            :key="chat.id"
                            @click="selectChat(chat.id)"
                            class="cursor-pointer border-b border-sidebar-border/50 p-4 transition-colors duration-200 hover:bg-accent/50 dark:border-sidebar-border"
                            :class="{ 'bg-accent': selectedChatId === chat.id }"
                        >
                            <div class="flex items-start gap-3">
                                <div class="relative flex-shrink-0">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <span
                                            class="text-sm font-medium text-primary"
                                        >
                                            {{
                                                chat.user
                                                    ? getInitials(
                                                          chat.user.name,
                                                      )
                                                    : 'U'
                                            }}
                                        </span>
                                    </div>

                                    <span
                                        v-if="isMessageUnread(chat)"
                                        class="absolute -top-0.5 -right-0.5 flex h-3 w-3"
                                    >
                                        <span
                                            class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary opacity-75"
                                        ></span>
                                        <span
                                            class="relative inline-flex h-3 w-3 rounded-full bg-primary"
                                        ></span>
                                    </span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <h3
                                            class="truncate font-medium text-foreground"
                                            :class="{
                                                'font-semibold':
                                                    isMessageUnread(chat),
                                            }"
                                        >
                                            {{
                                                chat.user?.name ||
                                                'Unknown User'
                                            }}
                                        </h3>
                                        <span
                                            class="flex-shrink-0 text-xs text-muted-foreground"
                                        >
                                            {{
                                                formatDateForHumans(
                                                    chat.last_message_at,
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <p
                                        class="mt-1 truncate text-sm text-muted-foreground"
                                        :class="{
                                            'font-medium text-foreground':
                                                isMessageUnread(chat),
                                            'font-normal':
                                                !isMessageUnread(chat),
                                        }"
                                    >
                                        {{ chat.latest_message.content }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-sidebar-background flex flex-1 flex-col overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <template v-if="selectedChat">
                        <div
                            @click="chatStore.markAsRead(selectedChat.id)"
                            class="flex h-full flex-col"
                        >
                            <div
                                class="flex items-center justify-between border-b border-sidebar-border/70 p-4 dark:border-sidebar-border"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <span
                                            class="text-sm font-medium text-primary"
                                        >
                                            {{
                                                selectedChat.user
                                                    ? getInitials(
                                                          selectedChat.user
                                                              .name,
                                                      )
                                                    : 'U'
                                            }}
                                        </span>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-foreground">
                                            {{
                                                selectedChat.user?.name ||
                                                'Unknown User'
                                            }}
                                        </h3>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ selectedChat.user?.email || '' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                ref="messagesContainer"
                                class="flex-1 space-y-3 overflow-y-auto p-4"
                            >
                                <div
                                    v-if="messages.length === 0"
                                    class="flex h-full flex-col items-center justify-center text-center"
                                >
                                    <svg
                                        class="mb-3 h-12 w-12 text-muted-foreground"
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
                                    <p class="text-sm text-muted-foreground">
                                        No messages yet
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        Start the conversation!
                                    </p>
                                </div>
                                <div
                                    v-for="(msg, index) in messages"
                                    :key="msg.id"
                                >
                                    <div
                                        v-if="
                                            index === 0 ||
                                            getDateSeparator(msg.created_at) !==
                                                getDateSeparator(
                                                    messages[index - 1]
                                                        ?.created_at,
                                                )
                                        "
                                        class="relative my-4 flex justify-center"
                                    >
                                        <span
                                            class="rounded-full bg-muted px-3 py-1 text-xs text-muted-foreground"
                                        >
                                            {{
                                                getDateSeparator(msg.created_at)
                                            }}
                                        </span>
                                    </div>

                                    <div
                                        :class="[
                                            'flex',
                                            msg.is_from_restaurant
                                                ? 'justify-end'
                                                : 'justify-start',
                                        ]"
                                    >
                                        <div
                                            :class="[
                                                'flex max-w-[70%] flex-col',
                                                msg.is_from_restaurant
                                                    ? 'items-end'
                                                    : 'items-start',
                                            ]"
                                        >
                                            <div
                                                v-if="
                                                    msg.edited_at &&
                                                    !(
                                                        msg.unsent_at ||
                                                        msg.deleted_at
                                                    )
                                                "
                                                class="mb-1 text-xs text-muted-foreground"
                                            >
                                                Modified at
                                                {{ formatTime(msg.edited_at) }}
                                            </div>

                                            <div
                                                :class="[
                                                    'group relative rounded-lg p-3',
                                                    {
                                                        'rounded-br-none bg-primary text-primary-foreground shadow-sm':
                                                            msg.is_from_restaurant &&
                                                            !msg.unsent_at &&
                                                            !msg.deleted_at,

                                                        'rounded-bl-none bg-muted text-foreground shadow-sm':
                                                            !msg.is_from_restaurant &&
                                                            !msg.unsent_at &&
                                                            !msg.deleted_at,

                                                        'bg-gray-100/50 text-gray-500 italic':
                                                            msg.unsent_at ||
                                                            msg.deleted_at,
                                                    },
                                                ]"
                                            >
                                                <div
                                                    v-if="
                                                        msg.is_from_restaurant &&
                                                        !(
                                                            msg.unsent_at ||
                                                            msg.deleted_at
                                                        )
                                                    "
                                                    class="absolute top-1/2 -left-10 -translate-y-1/2 transition-opacity"
                                                    :class="
                                                        messageMenuId === msg.id
                                                            ? 'opacity-100'
                                                            : 'opacity-0 group-hover:opacity-100'
                                                    "
                                                >
                                                    <button
                                                        @click.stop="
                                                            openMenu(
                                                                $event,
                                                                msg.id,
                                                            )
                                                        "
                                                        class="rounded-full p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600"
                                                    >
                                                        <MoreHorizontal
                                                            class="h-5 w-5"
                                                        />
                                                    </button>
                                                </div>

                                                <p
                                                    class="text-sm break-words whitespace-pre-wrap"
                                                >
                                                    {{ msg.content }}
                                                </p>

                                                <div
                                                    v-if="
                                                        !(
                                                            msg.unsent_at ||
                                                            msg.deleted_at
                                                        )
                                                    "
                                                    class="mt-2 flex justify-end"
                                                >
                                                    <span
                                                        class="text-[10px] opacity-70"
                                                    >
                                                        {{
                                                            formatTime(
                                                                msg.created_at,
                                                            )
                                                        }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        !isSending &&
                                        selectedChat.latest_message &&
                                        selectedChat.latest_message.read_at &&
                                        selectedChat.latest_message
                                            .is_from_restaurant
                                    "
                                    class="flex justify-end text-xs text-gray-400"
                                >
                                    read at
                                    {{
                                        formatTime(
                                            selectedChat.latest_message.read_at,
                                        )
                                    }}
                                </div>
                            </div>

                            <div
                                class="border-t border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border"
                            >
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
                                        >Press Enter to save, Escape to
                                        cancel</span
                                    >
                                </div>

                                <div class="flex items-end gap-3">
                                    <textarea
                                        v-model="newMessage"
                                        @keydown="handleKeyDown"
                                        rows="1"
                                        placeholder="Type a message..."
                                        class="max-h-32 min-h-[44px] flex-1 resize-none rounded-lg border border-border bg-background px-4 py-2.5 text-sm transition-all duration-200 focus:ring-2 focus:ring-primary/50 focus:outline-none"
                                        :disabled="isSending"
                                    />
                                    <button
                                        v-if="editingMessageId"
                                        @click="updateMessage"
                                        :disabled="
                                            !newMessage.trim() || isEditing
                                        "
                                        class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-primary-foreground transition-colors duration-200 hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <Check
                                            v-if="!isEditing"
                                            class="h-5 w-5"
                                        />
                                        <Loader
                                            v-else
                                            class="h-5 w-5 animate-spin"
                                        />
                                    </button>
                                    <button
                                        v-else
                                        @click="sendMessage"
                                        :disabled="
                                            !newMessage.trim() || isSending
                                        "
                                        class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-primary-foreground transition-colors duration-200 hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <Send
                                            v-if="!isSending"
                                            class="h-5 w-5"
                                        />
                                        <Loader
                                            v-else
                                            class="h-5 w-5 animate-spin"
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-else>
                        <div
                            class="flex flex-1 flex-col items-center justify-center p-8 text-center"
                        >
                            <MessageCircleMore
                                class="mb-4 h-16 w-16 text-muted-foreground"
                            />
                            <h3
                                class="mb-2 text-lg font-semibold text-foreground"
                            >
                                Select a Conversation
                            </h3>
                            <p class="max-w-md text-sm text-muted-foreground">
                                Choose a chat from the sidebar to start
                                messaging with your customers
                            </p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
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
                Delete
            </button>
        </div>
    </Teleport>
</template>

<style scoped>
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: hsl(var(--muted-foreground) / 0.3);
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: hsl(var(--muted-foreground) / 0.5);
}

textarea {
    scrollbar-width: none;
}

textarea::-webkit-scrollbar {
    display: none;
}

* {
    transition-property:
        background-color, border-color, color, fill, stroke, opacity,
        box-shadow, transform;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}
</style>
