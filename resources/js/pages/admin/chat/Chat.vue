<script setup lang="ts">
import { useClickOutside } from '@/composables/useClickOutside';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {
    formatDateForHumans,
    formatTime,
    getInitials,
    getUserAvatarColor,
} from '@/lib/utils';
import chat from '@/routes/admin/chat';
import message from '@/routes/admin/message';
import user from '@/routes/admin/user';
import { useChatStore } from '@/stores/chat';
import { type BreadcrumbItem, CursorPaginated } from '@/types';
import { ChatType, MessageType } from '@/types/chat';
import { UserType } from '@/types/user';
import { Head } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import axios from 'axios';
import {
    ArrowLeft,
    Check,
    CircleX,
    Loader,
    MessageCircleMore,
    MessageCircleOff,
    MoreHorizontal,
    Send,
    SquarePen,
    X,
} from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chats',
        href: chat.chats().url,
    },
];

const chatStore = useChatStore();
const { chats } = storeToRefs(chatStore);

const users = ref<UserType[]>([]);
const selectedChatId = ref<number | null>(null);
const messages = ref<MessageType[]>([]);
const newMessage = ref<string>('');
const isSending = ref<boolean>(false);
const isEditing = ref<boolean>(false);
const isUserLoading = ref<boolean>(false);
const searchQuery = ref<string>('');
const messagesContainer = ref<HTMLElement | null>(null);
const messageMenuDropdownRef = ref<HTMLElement | null>(null);
const messageMenuId = ref<number | null>(null);
const editingMessageId = ref<number | null>(null);
const nextCursorUsers = ref<string>('');
const isSearchFocused = ref<boolean>(false);
const isMobile = ref<boolean>(window.innerWidth < 768);
const showMobileChat = ref<boolean>(false);

const isUnsentOrRemoved = computed(() => {
    const message = messages.value.find((m) => m.id === messageMenuId.value);
    return !!(message?.unsent_at || message?.deleted_at);
});

const visibleChats = computed(() => {
    return chats.value.filter((c) => c.latest_message && c.last_message_at);
});

const loadUsers = async () => {
    isUserLoading.value = true;

    try {
        const { data } = await axios.get<CursorPaginated<UserType>>(
            user.getUsers().url,
            {
                params: {
                    cursor: nextCursorUsers.value,
                    search: searchQuery.value,
                },
            },
        );

        if (data) {
            const newUsers = data.data as UserType[];
            users.value.push(...newUsers);
            nextCursorUsers.value = data.next_cursor ?? '';
        }
    } catch (error) {
        console.log(error);
        toast.error('Failed to load users');
    } finally {
        isUserLoading.value = false;
    }
};

const loadMoreUsers = async () => {
    await loadUsers();
};

const resetUsers = () => {
    users.value = [];
    nextCursorUsers.value = '';
};

const applySearch = () => {
    users.value = [];
    nextCursorUsers.value = '';
    loadUsers();
};

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

    searchQuery.value = '';
    isSearchFocused.value = false;

    if (isMobile.value) {
        showMobileChat.value = true;
    }
};

const goBackToChats = () => {
    showMobileChat.value = false;
    selectedChatId.value = null;
};

const selectUser = async (userId: number) => {
    const existingChat = chats.value.find((chat) => chat.user?.id === userId);

    if (existingChat) {
        await selectChat(existingChat.id);
    } else {
        try {
            const { data } = await axios.post(chat.create(userId).url);

            console.log(data);

            if (data) {
                await chatStore.fetchChats();
                await selectChat(data.id);
            }
        } catch (error) {
            console.error('Failed to create chat:', error);
            toast.error('Failed to start conversation');
        }
    }

    searchQuery.value = '';
    isSearchFocused.value = false;
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
            message.send(selectedChatId.value).url,
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

const handleSearchFocus = () => {
    isSearchFocused.value = true;
};

const clearSearch = async () => {
    searchQuery.value = '';
    isSearchFocused.value = false;

    resetUsers();
    await loadUsers();
};

const handleResize = () => {
    isMobile.value = window.innerWidth < 768;
    if (!isMobile.value) {
        showMobileChat.value = false;
    }
};

type EchoChannel = {
    listen: (event: string, callback: (e: MessageType) => void) => EchoChannel;
};

const channel = ref<EchoChannel | null>(null);

onMounted(async () => {
    document.addEventListener('keydown', handleEscape);
    window.addEventListener('resize', handleResize);

    await loadUsers();
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
    window.removeEventListener('resize', handleResize);

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

watchDebounced(
    searchQuery,
    () => {
        applySearch();
    },
    { debounce: 400 },
);
</script>

<template>
    <Head title="Chats" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-2 sm:p-4"
        >
            <div class="flex min-h-[600px] flex-1 gap-4 md:min-h-[700px]">
                <div
                    :class="[
                        'bg-sidebar-background flex flex-shrink-0 flex-col overflow-hidden rounded-xl border border-sidebar-border/70 transition-all duration-300 dark:border-sidebar-border',
                        isMobile && showMobileChat ? 'hidden' : 'flex',
                        isMobile ? 'w-full' : 'w-[320px]',
                    ]"
                >
                    <div
                        class="border-b border-sidebar-border/70 p-3 sm:p-4 dark:border-sidebar-border"
                    >
                        <div class="flex items-center justify-between">
                            <h2
                                class="mb-2 text-base font-semibold text-foreground sm:text-lg"
                            >
                                {{
                                    isSearchFocused
                                        ? 'Search Users'
                                        : 'Conversations'
                                }}
                            </h2>
                            <button
                                v-if="isSearchFocused"
                                @click="clearSearch"
                                class="text-muted-foreground hover:text-foreground"
                            >
                                <X class="h-5 w-5" />
                            </button>
                        </div>

                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                @focus="handleSearchFocus"
                                type="text"
                                placeholder="Search for users..."
                                class="w-full rounded-lg border border-border bg-background px-3 py-2 pl-9 text-sm transition-all duration-200 focus:ring-2 focus:ring-primary/50 focus:outline-none sm:px-4 sm:pl-10"
                            />
                            <svg
                                class="absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2 transform text-muted-foreground sm:left-3"
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
                        <div v-if="isSearchFocused">
                            <div
                                v-if="users.length === 0 && !isUserLoading"
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
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                                <p class="text-sm text-muted-foreground">
                                    {{
                                        searchQuery
                                            ? 'No users found'
                                            : 'Start typing to search for users...'
                                    }}
                                </p>
                            </div>
                            <div
                                v-for="user in users"
                                :key="user.id"
                                @click="selectUser(user.id)"
                                class="cursor-pointer border-b border-sidebar-border/50 p-4 transition-colors duration-200 hover:bg-accent/50 dark:border-sidebar-border"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="relative flex-shrink-0">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full"
                                            :class="getUserAvatarColor(user.id)"
                                        >
                                            <span
                                                class="text-sm font-medium text-white"
                                            >
                                                {{ getInitials(user.name) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <h3
                                                class="truncate text-base font-medium text-foreground"
                                            >
                                                {{ user.name }}
                                            </h3>
                                        </div>
                                        <p
                                            class="mt-1 truncate text-sm text-muted-foreground"
                                        >
                                            {{ user.email }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="isUserLoading && users.length > 0"
                                class="mt-4 flex items-center justify-center gap-2 p-3 text-xs text-muted-foreground"
                            >
                                <Loader
                                    class="h-4 w-4 animate-spin text-primary"
                                />
                            </div>

                            <button
                                v-if="!isUserLoading && nextCursorUsers"
                                @click.stop="loadMoreUsers"
                                class="group mt-4 w-full rounded-lg p-3 text-xs text-muted-foreground transition-all duration-200 hover:bg-accent/30 hover:text-foreground"
                            >
                                <span
                                    class="flex items-center justify-center gap-1.5"
                                >
                                    Load more
                                    <span
                                        class="transition-transform duration-200 group-hover:translate-y-0.5"
                                        >↓</span
                                    >
                                </span>
                            </button>
                        </div>

                        <div v-else>
                            <div
                                v-if="visibleChats.length === 0"
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
                                v-for="chat in visibleChats"
                                :key="chat.id"
                                @click="selectChat(chat.id)"
                                class="cursor-pointer border-b border-sidebar-border/50 p-4 transition-colors duration-200 hover:bg-accent/50 dark:border-sidebar-border"
                                :class="{
                                    'bg-accent': selectedChatId === chat.id,
                                }"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="relative flex-shrink-0">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full"
                                            :class="
                                                getUserAvatarColor(chat.user.id)
                                            "
                                        >
                                            <span
                                                class="text-sm font-medium text-white"
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
                                            class="flex items-center justify-between gap-2"
                                        >
                                            <h3
                                                class="truncate text-base font-medium text-foreground"
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
                </div>

                <div
                    :class="[
                        'bg-sidebar-background flex flex-1 flex-col overflow-hidden rounded-xl border border-sidebar-border/70 transition-all duration-300 dark:border-sidebar-border',
                        isMobile && !showMobileChat ? 'hidden' : 'flex',
                    ]"
                >
                    <template v-if="selectedChat">
                        <div
                            @click="chatStore.markAsRead(selectedChat?.id)"
                            class="flex h-full flex-col"
                        >
                            <div
                                class="flex items-center justify-between border-b border-sidebar-border/70 p-3 sm:p-4 dark:border-sidebar-border"
                            >
                                <div class="flex items-center gap-3">
                                    <button
                                        v-if="isMobile"
                                        @click="goBackToChats"
                                        class="-ml-1 rounded-lg p-1 text-foreground transition-colors hover:bg-accent"
                                    >
                                        <ArrowLeft class="h-5 w-5" />
                                    </button>

                                    <div
                                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-primary/10 sm:h-9 sm:w-9"
                                    >
                                        <span
                                            class="text-xs font-medium text-primary sm:text-sm"
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
                                    <div class="min-w-0">
                                        <h3
                                            class="truncate text-sm font-medium text-foreground sm:text-base"
                                        >
                                            {{
                                                selectedChat.user?.name ||
                                                'Unknown User'
                                            }}
                                        </h3>
                                        <p
                                            class="truncate text-[10px] text-muted-foreground sm:text-xs"
                                        >
                                            {{ selectedChat.user?.email || '' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                ref="messagesContainer"
                                class="flex-1 space-y-3 overflow-y-auto p-3 sm:p-4"
                            >
                                <div
                                    v-if="messages.length === 0"
                                    class="flex h-full flex-col items-center justify-center text-center"
                                >
                                    <svg
                                        class="mb-3 h-10 w-10 text-muted-foreground sm:h-12 sm:w-12"
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
                                            class="rounded-full bg-muted px-3 py-1 text-[10px] text-muted-foreground sm:text-xs"
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
                                                'flex max-w-[85%] flex-col sm:max-w-[70%]',
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
                                                class="mb-1 text-[10px] text-muted-foreground sm:text-xs"
                                            >
                                                Modified at
                                                {{ formatTime(msg.edited_at) }}
                                            </div>

                                            <div
                                                :class="[
                                                    'group relative rounded-lg p-2.5 sm:p-3',
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
                                                    class="absolute top-1/2 -left-8 -translate-y-1/2 transition-opacity sm:-left-10"
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
                                                            class="h-4 w-4 sm:h-5 sm:w-5"
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
                                                    class="mt-1 flex justify-end sm:mt-2"
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
                                    class="flex justify-end text-[10px] text-gray-400 sm:text-xs"
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
                                class="border-t border-sidebar-border/70 bg-background p-3 sm:p-4 dark:border-sidebar-border"
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
                                    <span
                                        class="text-[10px] text-gray-400 sm:text-xs"
                                        >Press Enter to save, Escape to
                                        cancel</span
                                    >
                                </div>

                                <div class="flex items-end gap-2 sm:gap-3">
                                    <textarea
                                        v-model="newMessage"
                                        @keydown="handleKeyDown"
                                        rows="1"
                                        placeholder="Type a message..."
                                        class="max-h-32 min-h-[40px] flex-1 resize-none rounded-lg border border-border bg-background px-3 py-2 text-sm transition-all duration-200 focus:ring-2 focus:ring-primary/50 focus:outline-none sm:min-h-[44px] sm:px-4"
                                        :disabled="isSending"
                                    />
                                    <button
                                        v-if="editingMessageId"
                                        @click="updateMessage"
                                        :disabled="
                                            !newMessage.trim() || isEditing
                                        "
                                        class="flex items-center gap-2 rounded-lg bg-primary px-3 py-2 text-primary-foreground transition-colors duration-200 hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50 sm:px-4"
                                    >
                                        <Check
                                            v-if="!isEditing"
                                            class="h-4 w-4 sm:h-5 sm:w-5"
                                        />
                                        <Loader
                                            v-else
                                            class="h-4 w-4 animate-spin sm:h-5 sm:w-5"
                                        />
                                    </button>
                                    <button
                                        v-else
                                        @click="sendMessage"
                                        :disabled="
                                            !newMessage.trim() || isSending
                                        "
                                        class="flex items-center gap-2 rounded-lg bg-primary px-3 py-2 text-primary-foreground transition-colors duration-200 hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50 sm:px-4"
                                    >
                                        <Send
                                            v-if="!isSending"
                                            class="h-4 w-4 sm:h-5 sm:w-5"
                                        />
                                        <Loader
                                            v-else
                                            class="h-4 w-4 animate-spin sm:h-5 sm:w-5"
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-else>
                        <div
                            class="flex flex-1 flex-col items-center justify-center p-6 text-center sm:p-8"
                        >
                            <MessageCircleMore
                                class="mb-4 h-12 w-12 text-muted-foreground sm:h-16 sm:w-16"
                            />
                            <h3
                                class="mb-2 text-base font-semibold text-foreground sm:text-lg"
                            >
                                Select a Conversation
                            </h3>
                            <p
                                class="max-w-md text-xs text-muted-foreground sm:text-sm"
                            >
                                Choose a chat from the sidebar to start
                                messaging with your customers
                            </p>
                        </div>
                    </template>
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
                    Delete
                </button>
            </div>
        </Teleport>
    </AdminLayout>
</template>

<style scoped>
.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
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

@media (max-width: 768px) {
    .overflow-y-auto::-webkit-scrollbar {
        width: 3px;
    }
}
</style>
