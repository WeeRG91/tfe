<script setup lang="ts">
import ConfirmModal from '@/components/ConfirmModal.vue';
import { useClickOutside } from '@/composables/useClickOutside';
import { useDateFormatter } from '@/composables/useDateFormatter';
import { useNotificationTranslator } from '@/composables/useNotificationTranslator';
import {
    getNotificationIcon,
    getNotificationIconBgColor,
    getNotificationIconColor,
    notificationRoutes,
} from '@/lib/utils';
import notification from '@/routes/notification';
import { NotifiableTypeEnum, NotificationType } from '@/types/notification';
import { router } from '@inertiajs/vue3';
import {
    Bell,
    CheckCheck,
    CheckCircle2,
    Eye,
    MoreHorizontal,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    open: boolean;
    onClose: () => void;
    notificationsToShow: NotificationType[];
    isConfirmLoading: boolean;
}>();

const emit = defineEmits<{
    'mark-read': [notificationId: number];
    'mark-all-read': [];
    delete: [notificationId: number];
    'delete-all': [];
}>();

const { t } = useI18n();
const { formatDateForHumans } = useDateFormatter();
const { getNotificationTitle, getNotificationMessage } =
    useNotificationTranslator();

const confirmModalOpen = ref<boolean>(false);
const confirmModalMessage = ref<string>('');
const confirmModalType = ref<'destructive' | 'info'>('info');
const confirmModalAction = ref<() => void>(() => {});
const showDropdown = ref<boolean>(false);
const dropdownRef = ref<HTMLDivElement | null>(null);

const unreadCount = computed(() => {
    return props.notificationsToShow.filter((n) => !n.read_at).length;
});

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

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

const closeDropdown = () => {
    showDropdown.value = false;
};

const markAsRead = (notificationId: number) => {
    emit('mark-read', notificationId);
};

const markAllAsRead = () => {
    emit('mark-all-read');
};

const deleteNotification = (notificationId: number) => {
    emit('delete', notificationId);
    closeConfirmModal();
};

const deleteAllNotifications = () => {
    emit('delete-all');
    closeConfirmModal();
};

const goToNotifications = () => {
    router.visit(notification.myNotifications().url);
};

const goToDetails = (
    notifiableType: NotifiableTypeEnum,
    notifiableId: number,
) => {
    if (!notifiableType || !notifiableId) return;

    const route = notificationRoutes[notifiableType];

    if (!route) return;

    router.visit(route(notifiableId));
};

const handleNotificationClick = (notification: NotificationType) => {
    if (!notification.read_at) {
        emit('mark-read', notification.id);
    }

    goToDetails(notification.notifiable_type, notification.notifiable_id);

    props.onClose();
};

useClickOutside(dropdownRef, () => {
    if (showDropdown.value) {
        showDropdown.value = false;
    }
});

onMounted(() => {
    window.addEventListener('scroll', closeDropdown, true);

    if (props.open) {
        document.body.style.overflow = 'hidden';
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
    window.removeEventListener('scroll', closeDropdown, true);
});

watch(
    () => props.open,
    async (isOpen) => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    },
);
</script>

<template>
    <Transition name="slide">
        <div
            v-if="open"
            class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-background text-foreground shadow-2xl"
        >
            <div class="flex h-full flex-col">
                <div
                    class="flex items-center justify-between border-b border-border p-4"
                >
                    <div class="flex items-center gap-2">
                        <Bell class="h-5 w-5 text-primary" />
                        <h2 class="text-lg font-semibold text-foreground">
                            {{ t('notification.notificationDrawer.title') }}
                        </h2>
                        <span
                            v-if="unreadCount > 0"
                            class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary"
                        >
                            {{ unreadCount }}
                            {{ t('notification.notificationDrawer.new') }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div ref="dropdownRef" class="relative">
                            <button
                                @click.stop="toggleDropdown"
                                class="rounded-full p-2 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                aria-label="More options"
                            >
                                <MoreHorizontal class="h-5 w-5" />
                            </button>

                            <Transition name="dropdown-fade">
                                <div
                                    v-if="showDropdown"
                                    class="absolute top-full right-0 z-50 mt-2 w-48 origin-top-right rounded-lg border border-border bg-popover p-1 text-popover-foreground shadow-lg focus:outline-none"
                                    @click.stop
                                >
                                    <button
                                        @click="goToNotifications"
                                        class="flex w-full items-center rounded-sm gap-3 px-4 py-2 text-sm text-popover-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                    >
                                        <Eye class="h-4 w-4" />
                                        <span>{{
                                            t(
                                                'notification.notificationDrawer.menu.seeAll',
                                            )
                                        }}</span>
                                    </button>
                                    <button
                                        @click.stop="markAllAsRead"
                                        class="flex w-full items-center rounded-sm gap-3 px-4 py-2 text-sm text-popover-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                    >
                                        <CheckCheck class="h-4 w-4" />
                                        <span>{{
                                            t(
                                                'notification.notificationDrawer.menu.markAllAsRead',
                                            )
                                        }}</span>
                                    </button>
                                    <button
                                        @click.stop="
                                            openConfirmModal(
                                                t(
                                                    'notification.notificationDrawer.confirm.deleteAll',
                                                ),
                                                'destructive',
                                                () => deleteAllNotifications(),
                                            )
                                        "
                                        class="flex w-full items-center rounded-sm gap-3 px-4 py-2 text-sm text-destructive transition-colors hover:bg-destructive/10"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        <span>{{
                                            t(
                                                'notification.notificationDrawer.menu.deleteAll',
                                            )
                                        }}</span>
                                    </button>
                                </div>
                            </Transition>
                        </div>

                        <button
                            @click="onClose"
                            class="rounded-full p-2 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                            aria-label="Close"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <div
                        v-if="notificationsToShow.length === 0"
                        class="flex h-full flex-col items-center justify-center p-8 text-center"
                    >
                        <Bell class="mb-3 h-12 w-12 text-muted-foreground/50" />
                        <p class="text-muted-foreground">
                            {{
                                t('notification.notificationDrawer.empty.title')
                            }}
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{
                                t(
                                    'notification.notificationDrawer.empty.description',
                                )
                            }}
                        </p>
                    </div>

                    <div v-else class="divide-y divide-border">
                        <div
                            v-for="notification in notificationsToShow"
                            :key="notification.id"
                            :class="[
                                'group relative transition-colors hover:bg-accent/40',
                                !notification.read_at ? 'bg-primary/5' : '',
                            ]"
                        >
                            <div
                                @click="handleNotificationClick(notification)"
                                class="cursor-pointer p-4"
                            >
                                <div class="flex gap-3">
                                    <div
                                        :class="[
                                            'mt-1 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full',
                                            getNotificationIconBgColor(
                                                notification.type,
                                            ),
                                        ]"
                                    >
                                        <component
                                            :is="
                                                getNotificationIcon(
                                                    notification.type,
                                                )
                                            "
                                            :class="[
                                                'h-4 w-4',
                                                getNotificationIconColor(
                                                    notification.type,
                                                ),
                                            ]"
                                        />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="flex items-start justify-between gap-2"
                                        >
                                            <p
                                                class="flex-1 text-sm font-medium text-foreground"
                                            >
                                                {{
                                                    getNotificationTitle(
                                                        notification,
                                                    )
                                                }}
                                            </p>
                                            <span
                                                class="flex-shrink-0 text-xs text-muted-foreground"
                                            >
                                                {{
                                                    formatDateForHumans(
                                                        notification.created_at,
                                                    )
                                                }}
                                            </span>
                                        </div>

                                        <p
                                            class="mt-1 text-sm text-muted-foreground"
                                        >
                                            {{
                                                getNotificationMessage(
                                                    notification,
                                                )
                                            }}
                                        </p>

                                        <div
                                            class="mt-2 flex items-center gap-2"
                                        >
                                            <div
                                                v-if="!notification.read_at"
                                                class="flex items-center"
                                            >
                                                <span
                                                    class="inline-flex h-1.5 w-1.5 rounded-full bg-primary"
                                                ></span>
                                                <span
                                                    class="ml-1 text-xs text-primary"
                                                    >{{
                                                        t(
                                                            'notification.notificationDrawer.new',
                                                        )
                                                    }}</span
                                                >
                                            </div>

                                            <div
                                                class="ml-auto flex gap-1 opacity-0 transition-all duration-300 group-hover:opacity-100"
                                            >
                                                <button
                                                    v-if="!notification.read_at"
                                                    @click.stop="
                                                        markAsRead(
                                                            notification.id,
                                                        )
                                                    "
                                                    class="rounded-md px-2 py-1 text-xs font-medium text-info transition-colors hover:bg-info/10"
                                                    :title="
                                                        t(
                                                            'notification.notificationDrawer.actions.markAsRead',
                                                        )
                                                    "
                                                >
                                                    <CheckCircle2
                                                        class="h-3.5 w-3.5"
                                                    />
                                                    <span class="sr-only">{{
                                                        t(
                                                            'notification.notificationDrawer.actions.markAsRead',
                                                        )
                                                    }}</span>
                                                </button>

                                                <button
                                                    @click.stop="
                                                        openConfirmModal(
                                                            t(
                                                                'notification.notificationDrawer.confirm.delete',
                                                            ),
                                                            'destructive',
                                                            () =>
                                                                deleteNotification(
                                                                    notification.id,
                                                                ),
                                                        )
                                                    "
                                                    class="rounded-md px-2 py-1 text-xs font-medium text-destructive transition-colors hover:bg-destructive/10"
                                                    :title="
                                                        t(
                                                            'notification.notificationDrawer.actions.delete',
                                                        )
                                                    "
                                                >
                                                    <Trash2
                                                        class="h-3.5 w-3.5"
                                                    />
                                                    <span class="sr-only">{{
                                                        t(
                                                            'notification.notificationDrawer.actions.delete',
                                                        )
                                                    }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button
                        v-if="notificationsToShow.length > 8"
                        @click="goToNotifications"
                        class="mt-2 flex w-full items-center justify-center gap-2 border-t border-border bg-background px-4 py-3 text-sm font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-primary"
                    >
                        <Eye class="h-4 w-4" />
                        <span>{{
                            t(
                                'notification.notificationDrawer.menu.seeAllNotifications',
                            )
                        }}</span>
                    </button>
                </div>
            </div>

            <ConfirmModal
                :open="confirmModalOpen"
                :onClose="closeConfirmModal"
                :message="confirmModalMessage"
                :type="confirmModalType"
                :isLoading="isConfirmLoading"
                @confirm="confirmModalAction"
            />
        </div>
    </Transition>

    <Transition name="fade">
        <div
            v-if="open"
            @click="onClose"
            class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"
        ></div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
    transform: translateX(100%);
}

.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
    transition: all 0.2s ease;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
    opacity: 0;
    transform: scale(0.95);
}

/* Ensure proper text wrapping */
.min-w-0 {
    min-width: 0;
}

/* Button hover effects */
button {
    cursor: pointer;
}

.rounded-md {
    border-radius: 0.375rem;
}
</style>
