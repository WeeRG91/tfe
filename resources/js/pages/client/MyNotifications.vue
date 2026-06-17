<script setup lang="ts">
import EmptyNotification from '@/components/client/myNotifications/EmptyNotification.vue';
import NotificationItem from '@/components/client/myNotifications/NotificationItem.vue';
import { useClickOutside } from '@/composables/useClickOutside';
import ClientLayout from '@/layouts/ClientLayout.vue';
import notification from '@/routes/notification';
import { CursorPaginated } from '@/types';
import { FilterNotificationEnum, NotificationType } from '@/types/notification';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { CheckCheck, Loader, MoreHorizontal, Trash2 } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import { useNotificationStore } from '@/stores/notification';
import ConfirmModal from '@/components/ConfirmModal.vue';
import { toast } from 'vue-sonner';

const notificationStore = useNotificationStore();

const filterOptions: { label: string; value: FilterNotificationEnum }[] = [
    { label: 'All', value: FilterNotificationEnum.ALL },
    { label: 'Read', value: FilterNotificationEnum.READ },
    { label: 'Unread', value: FilterNotificationEnum.UNREAD },
];

const confirmModalOpen = ref<boolean>(false);
const confirmModalMessage = ref<string>('');
const confirmModalType = ref<'destructive' | 'info'>('info');
const confirmModalAction = ref<() => void>(() => {});
const isLoading = ref<boolean>(false);
const isConfirmLoading = ref<boolean>(false);
const activeFilter = ref<FilterNotificationEnum>(FilterNotificationEnum.ALL);
const showDropdown = ref<boolean>(false);
const dropdownRef = ref<HTMLDivElement | null>(null);
const notifications = ref<NotificationType[]>([]);
const nextCursor = ref<string>('');
const sentinel = ref<HTMLElement | null>(null);
const observer = ref<IntersectionObserver | null>(null);

const openConfirmModal = (
    message: string,
    type: 'destructive' | 'info',
    action: () => void,
) => {
    confirmModalMessage.value = message;
    confirmModalType.value = type;
    confirmModalAction.value = action;
    confirmModalOpen.value = true;
};

const closeConfirmModal = () => {
    confirmModalOpen.value = false;
};

const groupedNotifications = computed(() => {
    const groups: Record<string, NotificationType[]> = {};

    notifications.value.forEach((notification) => {
        const date = new Date(notification.created_at);
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        let label: string;
        if (date.toDateString() === today.toDateString()) {
            label = 'Today';
        } else if (date.toDateString() === yesterday.toDateString()) {
            label = 'Yesterday';
        } else if (date.getFullYear() === today.getFullYear()) {
            label = date.toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
            });
        } else {
            label = date.toLocaleDateString('en-US', {
                month: 'long',
                year: 'numeric',
            });
        }

        if (!groups[label]) groups[label] = [];
        groups[label].push(notification);
    });

    return groups;
});

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

const markAsRead = async (id: number) => {
    const index = notifications.value.findIndex((n) => n.id === id);

    try {
        const response = await notificationStore.markAsRead(id);

        if (index !== -1) {
            notifications.value[index].read_at = response.read_at;
        }
    } catch (error) {
        console.log(error);
    }
};

const markAllAsRead = async () => {
    try {
        await notificationStore.markAllAsRead();

        const now = new Date().toISOString();

        notifications.value = notifications.value.map((n) => ({
            ...n,
            read_at: n.read_at ?? now,
        }));
    } catch (error) {
        console.log(error);
    }
};

const deleteNotification = async (id: number) => {
    isConfirmLoading.value = true;

    try {
        await notificationStore.delete(id);

        notifications.value = notifications.value.filter((n) => n.id !== id);

        toast.success('Notification deleted successfully.');
    } catch (error) {
        console.log(error);
        toast.error('Failed to delete notification.');
    } finally {
        isConfirmLoading.value = false;
    }
};

const deleteAll = async () => {
    isConfirmLoading.value = true;

    try {
        await notificationStore.deleteAll();

        notifications.value = [];

        toast.success('All notifications deleted successfully.');
    } catch (error) {
        console.log(error);
        toast.error('Failed to delete all notifications.');
    } finally {
        isConfirmLoading.value = false;
    }
};

useClickOutside(dropdownRef, () => {
    if (showDropdown.value) {
        showDropdown.value = false;
    }
});

const loadNotifications = async () => {
    isLoading.value = true;

    const { data } = await axios.get<CursorPaginated<NotificationType>>(
        notification.getNotifications().url,
        {
            params: {
                cursor: nextCursor.value,
                filter: activeFilter.value,
            },
        },
    );

    if (data) {
        const newNotifications = data.data as NotificationType[];
        notifications.value.push(...newNotifications);
        nextCursor.value = data.next_cursor ?? '';
        isLoading.value = false;
    }
};

onMounted(() => {
    loadNotifications();

    observer.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    loadNotifications();
                }
            });
        },
        {
            root: null,
            rootMargin: '100px',
            threshold: 0.1,
        },
    );

    watch(sentinel, (element) => {
        if (element) observer.value?.observe(element);
    });
});

watch(activeFilter, () => {
    notifications.value = [];
    nextCursor.value = '';

    observer.value?.disconnect();

    loadNotifications();

    if (sentinel.value) {
        observer.value?.observe(sentinel.value);
    }
});
</script>

<template>
    <Head title="Notifications" />
    <ClientLayout>
        <section class="mx-auto max-w-6xl px-6 py-4">
            <div class="mb-6">
                <p class="text-sm tracking-widest text-red-500 uppercase">
                    [ My Account ]
                </p>
                <h1 class="text-4xl font-semibold uppercase md:text-5xl">
                    Notifications
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Stay updated with your order status and exclusive offers
                </p>
            </div>

            <div class="mb-6 flex flex-wrap items-center gap-4">
                <button
                    v-for="filter in filterOptions"
                    :key="filter.value"
                    @click="activeFilter = filter.value"
                    class="rounded-full border px-4 py-1 text-sm transition-all duration-500"
                    :class="
                        activeFilter === filter.value
                            ? 'border-red-500 bg-red-500 text-white hover:bg-red-600'
                            : 'text-gray-600 hover:bg-gray-100'
                    "
                >
                    {{ filter.label }}
                </button>

                <div
                    v-if="notifications.length > 0"
                    ref="dropdownRef"
                    class="relative ml-auto"
                >
                    <button
                        @click.stop="toggleDropdown"
                        class="rounded-full p-2 text-gray-400 transition-all duration-200 hover:bg-gray-100 hover:text-gray-600"
                        aria-label="More options"
                    >
                        <MoreHorizontal class="h-5 w-5" />
                    </button>

                    <Transition name="dropdown-fade">
                        <div
                            v-if="showDropdown"
                            class="ring-opacity-5 absolute top-full right-0 z-50 mt-2 w-48 origin-top-right rounded-lg bg-white py-1 shadow-lg ring-1 ring-gray-100 focus:outline-none"
                            @click.stop
                        >
                            <button
                                @click.stop="markAllAsRead"
                                class="flex w-full items-center gap-3 px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-100"
                            >
                                <CheckCheck class="h-4 w-4" />
                                <span>Mark all as read</span>
                            </button>
                            <button
                                @click.stop="
                                    openConfirmModal(
                                        'Are you sure you want to delete all notifications?',
                                        'destructive',
                                        () => deleteAll(),
                                    )
                                "
                                class="flex w-full items-center gap-3 px-4 py-2 text-sm text-red-600 transition-colors hover:bg-red-50"
                            >
                                <Trash2 class="h-4 w-4" />
                                <span>Delete all</span>
                            </button>
                        </div>
                    </Transition>
                </div>
            </div>

            <EmptyNotification v-if="!notifications.length && !isLoading" />

            <div v-else class="space-y-6">
                <div
                    v-for="(group, dateLabel) in groupedNotifications"
                    :key="dateLabel"
                    class="space-y-3"
                >
                    <h3 class="text-sm font-semibold text-gray-400">
                        {{ dateLabel }}
                    </h3>

                    <div class="space-y-2">
                        <NotificationItem
                            v-for="notification in group"
                            :key="notification.id"
                            :notification="notification"
                            :is-confirm-loading="isConfirmLoading"
                            @mark-read="markAsRead"
                            @delete="deleteNotification"
                        />
                    </div>
                </div>

                <div
                    ref="sentinel"
                    v-if="nextCursor"
                    class="flex w-full justify-center"
                ></div>
            </div>

            <div
                v-if="isLoading"
                class="flex h-24 items-center justify-center sm:h-32"
            >
                <Loader class="mx-auto animate-spin text-muted-foreground" />
            </div>
        </section>

        <ConfirmModal
            :open="confirmModalOpen"
            :onClose="closeConfirmModal"
            :message="confirmModalMessage"
            :type="confirmModalType"
            :isLoading="isConfirmLoading"
            @confirm="confirmModalAction"
        />
    </ClientLayout>
</template>
