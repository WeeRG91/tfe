<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
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

onMounted(async () => {
    await orderStore.getConfirmedOrders();

    channel.value = window.Echo.channel('orders').listen(
        '.order.placed',
        async () => {
            await orderStore.getConfirmedOrders();
        },
    );
});

onUnmounted(() => {
    if (channel.value) {
        window.Echo.leave('orders');
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
                            item.title === 'Orders'
                                ? 'flex justify-between'
                                : 'flex'
                        "
                    >
                        <div class="flex items-center gap-2">
                            <component :is="item.icon" :size="16" />
                            <span>{{ item.title }}</span>
                        </div>

                        <span
                            v-if="item.title === 'Orders'"
                            class="rounded-full bg-red-600 px-1.5 py-0.5 text-xs text-white"
                        >
                            {{ ordersConfirmed.length }}
                        </span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
