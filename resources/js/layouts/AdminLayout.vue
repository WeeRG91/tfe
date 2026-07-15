<script setup lang="ts">
import { usePermission } from '@/composables/usePermission';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { useChatStore } from '@/stores/chat';
import { useOrderStore } from '@/stores/order';
import type { BreadcrumbItemType } from '@/types';
import { ChatPermissionEnum, OrderPermissionEnum } from '@/types/permission';
import { onMounted, onUnmounted, ref } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const { can } = usePermission();

const orderStore = useOrderStore();
const chatStore = useChatStore();

type EchoChannel = {
    listen: (event: string, callback: () => void) => EchoChannel;
};

const channel = ref<EchoChannel | null>(null);
const channelAdminChat = ref<EchoChannel | null>(null);

onMounted(async () => {
    if (can(OrderPermissionEnum.ORDER_VIEW)) {
        await orderStore.getConfirmedOrders();

        channel.value = window.Echo.channel('orders').listen(
            '.order.placed',
            async () => {
                await orderStore.getConfirmedOrders();
            },
        );
    }

    if (can(ChatPermissionEnum.CHAT_VIEW)) {
        await chatStore.fetchChats();

        channelAdminChat.value = window.Echo.private('admin.chats').listen(
            '.message-sent',
            async () => {
                console.log('update chat');
                await chatStore.fetchChats();
            },
        );
    }
});

onUnmounted(() => {
    if (channel.value) {
        window.Echo.leave('orders');
    }

    if (channelAdminChat.value) {
        window.Echo.leave('private-admin.chats');
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
