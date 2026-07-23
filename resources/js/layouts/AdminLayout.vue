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

const channelOrderPlaced = ref<EchoChannel | null>(null);
const channelOrderCancelled = ref<EchoChannel | null>(null);
const channelAdminChat = ref<EchoChannel | null>(null);

onMounted(async () => {
    if (can(OrderPermissionEnum.ORDER_VIEW)) {
        await orderStore.getConfirmedOrders();

        channelOrderPlaced.value = window.Echo.private('order-placed').listen(
            '.order-placed',
            async () => {
                await orderStore.getConfirmedOrders();
            },
        );

        channelOrderCancelled.value = window.Echo.private(
            'order-cancelled',
        ).listen('.order-cancelled', async () => {
            await orderStore.getConfirmedOrders();
        });
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
    if (channelOrderPlaced.value) {
        window.Echo.leave('order-placed');
    }

    if (channelOrderCancelled.value) {
        window.Echo.leave('order-cancelled');
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
