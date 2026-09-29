<script setup lang="ts">
import CartDrawer from '@/components/client/cart/CartDrawer.vue';
import ChatBubble from '@/components/client/clientLayout/ChatBubble.vue';
import ClientFooter from '@/components/client/clientLayout/ClientFooter.vue';
import ClientGlobalSearchModal from '@/components/client/clientLayout/ClientGlobalSearchModal.vue';
import LayoutHeader from '@/components/client/clientLayout/LayoutHeader.vue';
import RestaurantClosedModal from '@/components/client/clientLayout/RestaurantClosedModal.vue';
import RestaurantClosureBanner from '@/components/client/clientLayout/RestaurantClosureBanner.vue';
import NotificationsDrawer from '@/components/client/notification/NotificationsDrawer.vue';
import { useAppearance } from '@/composables/useAppearance';
import { useCartStore } from '@/stores/cart';
import { useNotificationStore } from '@/stores/notification';
import { useRestaurantStore } from '@/stores/restaurant';
import { AppPageProps } from '@/types';
import { FilterNotificationEnum } from '@/types/notification';
import { router, usePage } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { toast } from 'vue-sonner';

useAppearance('client');

const page = usePage<AppPageProps>();
const user = computed(() => page.props.auth?.user);

const notificationStore = useNotificationStore();
const { notifications } = storeToRefs(notificationStore);
const cartStore = useCartStore();
const { items } = storeToRefs(cartStore);

const restaurantStore = useRestaurantStore();

let restaurantRefreshTimer: ReturnType<typeof setInterval> | null = null;

const refreshRestaurantStatus = async (): Promise<void> => {
    if (document.visibilityState !== 'visible') {
        return;
    }

    const refreshed = await restaurantStore.refresh();

    if (refreshed && restaurantStore.isClosed) {
        restaurantStore.showClosedModalOnce();
    }
};

const isCartOpen = ref<boolean>(false);
const isSearchOpen = ref<boolean>(false);
const isNotificationsOpen = ref<boolean>(false);
const isConfirmLoading = ref<boolean>(false);

const openCart = () => {
    if (!user.value) {
        router.visit('/login');

        return;
    }

    isCartOpen.value = true;
};
const closeCart = () => {
    isCartOpen.value = false;
};

const openSearch = () => {
    isSearchOpen.value = true;
};
const closeSearch = () => {
    isSearchOpen.value = false;
};

const openNotifications = () => {
    isNotificationsOpen.value = true;
};
const closeNotifications = () => {
    isNotificationsOpen.value = false;
};

const markAsRead = async (notificationId: number) => {
    await notificationStore.markAsRead(notificationId);
};

const markAllAsRead = async () => {
    await notificationStore.markAllAsRead();
};

const deleteNotification = async (id: number) => {
    isConfirmLoading.value = true;
    try {
        await notificationStore.delete(id);

        toast.success('Notification deleted successfully.');
    } catch (error) {
        console.log(error);
        toast.error('Failed to delete the notification');
    } finally {
        isConfirmLoading.value = false;
    }
};

const deleteAllNotifications = async () => {
    isConfirmLoading.value = true;

    try {
        await notificationStore.deleteAll();

        toast.success('All notifications deleted successfully.');
    } catch (error) {
        console.log(error);
        toast.error('Failed to delete the notification');
    } finally {
        isConfirmLoading.value = false;
    }
};

type EchoChannel = {
    listen: (event: string, callback: () => void) => EchoChannel;
};

const channel = ref<EchoChannel | null>(null);

onMounted(async () => {
    await refreshRestaurantStatus();

    restaurantRefreshTimer = setInterval(refreshRestaurantStatus, 60_000);

    document.addEventListener('visibilitychange', refreshRestaurantStatus);

    if (user.value) {
        await cartStore.getCart();
        await notificationStore.getNotifications(FilterNotificationEnum.ALL);
    } else {
        cartStore.$reset();
    }

    if (user.value?.id) {
        channel.value = window.Echo.private(`user.${user.value?.id}`).listen(
            '.order.updated',
            async () => {
                await notificationStore.getNotifications(
                    FilterNotificationEnum.ALL,
                );
            },
        );
    }
});

onUnmounted(() => {
    if (restaurantRefreshTimer !== null) {
        clearInterval(restaurantRefreshTimer);
        restaurantRefreshTimer = null;
    }

    document.removeEventListener('visibilitychange', refreshRestaurantStatus);

    if (channel.value) {
        window.Echo.leave(`private-user.${user.value?.id}`);
    }
});
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <LayoutHeader
            :user="user"
            :cart-item-count="items.length"
            :unread-notifications-count="
                notifications.filter((n) => !n.read_at).length
            "
            @open-cart="openCart"
            @open-search="openSearch"
            @open-notifications="openNotifications"
        />

        <RestaurantClosureBanner />

        <div class="relative flex-1">
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div
                    class="absolute -top-24 -right-24 h-110 w-110 rounded-full bg-primary/10 blur-3xl"
                ></div>
                <div
                    class="absolute -bottom-24 -left-24 h-110 w-110 rounded-full bg-accent/20 blur-3xl"
                ></div>
            </div>

            <main class="relative">
                <slot />
            </main>
        </div>

        <ClientFooter
            :restaurant-name="page.props.restaurant.name"
            :contact-email="page.props.restaurant.contactEmail"
            :contact-phone="page.props.restaurant.contactPhone"
        />

        <ChatBubble v-if="user" />

        <RestaurantClosedModal />

        <CartDrawer :open="isCartOpen" :onClose="closeCart" />

        <ClientGlobalSearchModal :open="isSearchOpen" :onClose="closeSearch" />

        <NotificationsDrawer
            :open="isNotificationsOpen"
            :onClose="closeNotifications"
            :notifications-to-show="notifications"
            :is-confirm-loading="isConfirmLoading"
            @mark-read="markAsRead"
            @mark-all-read="markAllAsRead"
            @delete="deleteNotification"
            @delete-all="deleteAllNotifications"
        />
    </div>
</template>
