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
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
const { t } = useI18n();

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
            !order.completed_at &&
            !order.cancelled_at,
    ),
);
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
                            item.title === t('layout.navigation.orders') ||
                            item.title === t('layout.navigation.messages')
                                ? 'flex justify-between'
                                : 'flex'
                        "
                    >
                        <div class="flex items-center gap-2">
                            <component :is="item.icon" :size="16" />
                            <span class="text-nowrap">{{ item.title }}</span>
                        </div>

                        <span
                            v-if="
                                item.title === t('layout.navigation.orders') &&
                                ordersConfirmed.length > 0
                            "
                            class="rounded-full bg-sidebar-primary px-2 py-1 text-[10px] text-sidebar-primary-foreground"
                        >
                            {{ ordersConfirmed.length }}
                        </span>

                        <span
                            v-if="
                                item.title ===
                                    t('layout.navigation.messages') &&
                                unreadCount > 0
                            "
                            class="rounded-full bg-sidebar-primary px-2 py-1 text-[10px] text-sidebar-primary-foreground"
                        >
                            {{ unreadCount }}
                        </span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
