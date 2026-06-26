<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { useChatStore } from '@/stores/chat';
import { useOrderStore } from '@/stores/order';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { computed, onMounted, onUnmounted, ref } from 'vue';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();

const orderStore = useOrderStore();
const { confirmedOrders } = storeToRefs(orderStore);
const chatStore = useChatStore();
const { chats } = storeToRefs(chatStore);

const unreadCount = computed(() => {
    return chats.value.filter(
        (c) =>
            c.latest_message &&
            !c.latest_message.is_from_restaurant &&
            !c.latest_message.read_at,
    ).length;
});

const ordersConfirmed = computed(() =>
    (confirmedOrders.value ?? []).filter(
        (order) =>
            order.confirmed_at &&
            !order.prepare_at &&
            !order.ready_at &&
            !order.delivered_at &&
            !order.completed_at,
    ),
);

type EchoChannel = {
    listen: (event: string, callback: () => void) => EchoChannel;
};

const channel = ref<EchoChannel | null>(null);
const channelAdminChat = ref<EchoChannel | null>(null);

onMounted(async () => {
    await orderStore.getConfirmedOrders();

    channel.value = window.Echo.channel('orders').listen(
        '.order.placed',
        async () => {
            await orderStore.getConfirmedOrders();
        },
    );

    channelAdminChat.value = window.Echo.private('admin.chats').listen(
        '.message-sent',
        async () => {
            await chatStore.fetchChats();
        },
    );
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
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="urlIsActive(item.href, page.url)"
                    :tooltip="item.title"
                >
                    <Link
                        :href="item.href"
                        class="items-center"
                        :class="
                            item.title === 'Orders' || item.title === 'Messages'
                                ? 'flex justify-between'
                                : 'flex'
                        "
                    >
                        <div class="flex items-center gap-2">
                            <component :is="item.icon" :size="16" />
                            <span>{{ item.title }}</span>
                        </div>

                        <span
                            v-if="
                                item.title === 'Orders' &&
                                ordersConfirmed.length > 0
                            "
                            class="rounded-full bg-red-600 px-2 py-1 text-[10px] text-white"
                        >
                            {{ ordersConfirmed.length }}
                        </span>

                        <span
                            v-if="item.title === 'Messages' && unreadCount > 0"
                            class="rounded-full bg-red-600 px-2 py-1 text-[10px] text-white"
                        >
                            {{ unreadCount }}
                        </span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
