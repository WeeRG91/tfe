<script setup lang="ts">
import {
    formatDateForHumans,
    getNotificationIcon,
    getNotificationIconBgColor,
    getNotificationIconColor,
    notificationRoutes,
} from '@/lib/utils';
import {
    NotifiableTypeEnum,
    NotificationType,
} from '@/types/notification';
import { CheckCircle2, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    notification: NotificationType;
}>();

const emit = defineEmits(['mark-read', 'delete']);

const isRead = computed(() => !!props.notification.read_at);

const goToDetails = (
    notifiableType: NotifiableTypeEnum,
    notifiableId: number,
) => {
    if (!notifiableType || !notifiableId) return;

    const route = notificationRoutes[notifiableType];

    if (!route) return;

    router.visit(route(notifiableId));
};

const handleNotificationClick = () => {
    if (!props.notification.read_at) {
        emit('mark-read', props.notification.id);
    }

    goToDetails(props.notification.notifiable_type, props.notification.notifiable_id);
};

const markAsRead = () => {
    if (!props.notification.read_at) {
        emit('mark-read', props.notification.id);
    }
};

const deleteNotification = () => {
    emit('delete', props.notification.id);
};
</script>

<template>
    <div
        @click="handleNotificationClick"
        class="group flex cursor-pointer items-start gap-4 rounded-xl border p-4 transition-all duration-200 hover:shadow-sm"
        :class="[
            isRead ? 'bg-white' : 'border-l-4 border-l-red-500 bg-red-50/30',
            notification.read_at ? 'opacity-80' : '',
        ]"
    >
        <!-- Icon based on notification type -->
        <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
            :class="getNotificationIconBgColor(notification.type)"
        >
            <component
                :is="getNotificationIcon(notification.type)"
                class="h-5 w-5"
                :class="getNotificationIconColor(notification.type)"
            />
        </div>

        <!-- Content -->
        <div class="flex-1 space-y-1">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <h4 class="font-medium text-gray-900">
                    {{ notification.title }}
                </h4>
                <span class="text-xs text-gray-400">
                    {{ formatDateForHumans(notification.created_at) }}
                </span>
            </div>

            <div class="flex flex-wrap items-start justify-between gap-2">
                <p class="text-sm text-gray-600">
                    {{ notification.message }}
                </p>
                <!-- Action Buttons -->
                <div
                    class="flex shrink-0 items-center gap-1 opacity-0 transition-opacity duration-200 group-hover:opacity-100"
                >
                    <!-- Mark as read button -->
                    <button
                        v-if="!notification.read_at"
                        @click.stop="markAsRead"
                        class="rounded-md p-1.5 text-blue-600 transition-colors hover:bg-blue-50"
                        title="Mark as read"
                    >
                        <CheckCircle2 class="h-4 w-4" />
                        <span class="sr-only">Mark as read</span>
                    </button>

                    <!-- Delete button -->
                    <button
                        @click.stop="deleteNotification"
                        class="rounded-md p-1.5 text-red-600 transition-colors hover:bg-red-50"
                        title="Delete"
                    >
                        <Trash2 class="h-4 w-4" />
                        <span class="sr-only">Delete</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Smooth transitions for hover effects */
.group {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Ensure buttons don't cause layout shift */
button {
    cursor: pointer;
    user-select: none;
}

/* Better touch targets for mobile */
@media (max-width: 640px) {
    button {
        padding: 0.5rem;
    }

    .group .opacity-0 {
        opacity: 1;
    }
}
</style>
