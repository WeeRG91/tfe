<script setup lang="ts">
import OrderDetailsModal from '@/components/admin/order/OrderDetailsModal.vue';
import { useOrderStatusActionFlow } from '@/composables/useOrderStatusActionFlow';
import {
    formatAddress,
    formatDate,
    getOrderStatusVariant,
    getOrderTypeIcon,
} from '@/lib/utils';
import { useOrderStore } from '@/stores/order';
import { OrderStatusEnum, OrderType, OrderTypeEnum } from '@/types/order';
import { Coffee, MapPin, User } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    order: OrderType;
    updateStatusOrderId: number | null;
}>();

const emit = defineEmits<{
    (e: 'update-status', orderId: number, newStatus: OrderStatusEnum): void;
}>();

const orderStatusActionId = ref<string>('');

const updateStatus = (newStatus: OrderStatusEnum, actionId: string) => {
    orderStatusActionId.value = actionId;
    emit('update-status', props.order.id, newStatus);
};

const { getOrderStatusActions } = useOrderStatusActionFlow(updateStatus);

const orderStore = useOrderStore();

const selectedOrder = ref<OrderType | null>(null);
const isModalOpen = ref<boolean>(false);

const orderStatusActions = computed(() =>
    getOrderStatusActions(props.order.status.value, props.order.type.value),
);

const openOrderDetails = (order: OrderType) => {
    selectedOrder.value = order;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedOrder.value = null;
};

const handleUpdateStatus = async (
    orderId: number,
    newStatus: OrderStatusEnum,
) => {
    try {
        await orderStore.updateOrderStatus(orderId, newStatus);
        toast.success('Updated status order successfully!');
    } catch (error) {
        console.log(error);
        toast.error('Failed to update order status');
    } finally {
        closeModal();
    }
};

watch(
    () => props.updateStatusOrderId,
    (newStatusOrderId) => {
        if (newStatusOrderId === null) {
            orderStatusActionId.value = '';
        }
    },
);
</script>

<template>
    <div
        class="group rounded-lg border border-sidebar-border/70 bg-card p-4 transition-all hover:shadow-md"
    >
        <!-- Order Header -->
        <div class="mb-3 flex items-start justify-between">
            <div>
                <h4
                    class="flex items-center justify-center gap-1 font-semibold"
                >
                    <component
                        :is="getOrderTypeIcon(order.type.value)"
                        :size="15"
                    />
                    <span>Order #{{ order.order_number }}</span>
                </h4>
                <p class="text-xs text-muted-foreground">
                    {{ formatDate(order.created_at) }}
                </p>
            </div>
            <span
                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                :class="getOrderStatusVariant(order.status.value)"
            >
                {{ order.status.label }}
            </span>
        </div>

        <!-- Customer Info -->
        <div class="mb-3 space-y-1 text-sm">
            <div class="flex items-center gap-2">
                <User :size="14" class="text-muted-foreground" />
                <span>{{ order.user.name }}</span>
            </div>
            <div
                v-if="order.type.value === OrderTypeEnum.DELIVERY"
                class="flex items-start gap-2"
            >
                <MapPin :size="14" class="mt-0.5 text-muted-foreground" />
                <span class="text-xs">
                    {{ formatAddress(order.delivery_address) }}
                </span>
            </div>
            <div
                v-if="order.type.value === OrderTypeEnum.TAKEAWAY"
                class="flex items-center gap-2"
            >
                <User :size="14" class="text-muted-foreground" />
                <span class="text-xs"
                    >{{ order.pickup_name }} | {{ order.pickup_phone }}</span
                >
            </div>
            <div
                v-if="order.type.value === OrderTypeEnum.DINEIN"
                class="flex items-center gap-2"
            >
                <Coffee :size="14" class="text-muted-foreground" />
                <span class="text-xs">Table {{ order.table_number }}</span>
            </div>
        </div>

        <!-- Order Items Preview -->
        <div class="mb-3 border-t border-sidebar-border/50 pt-2">
            <div class="space-y-1">
                <div
                    v-for="item in order.items.slice(0, 2)"
                    :key="item.id"
                    class="flex justify-between text-xs"
                >
                    <span>{{ item.quantity }}x {{ item.item.name }}</span>
                    <span>€{{ item.total_inc_vat.toFixed(2) }}</span>
                </div>
                <div
                    v-if="order.items.length > 2"
                    class="text-xs text-muted-foreground"
                >
                    +{{ order.items.length - 2 }} more items
                </div>
            </div>
        </div>

        <!-- Order Total -->
        <div
            class="mb-3 flex items-center justify-between border-t border-sidebar-border/50 pt-2"
        >
            <span class="text-sm font-medium">Total:</span>
            <span class="text-lg font-bold"
                >€{{ order.total_inc_vat.toFixed(2) }}</span
            >
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2">
            <div class="flex flex-1 gap-2">
                <button
                    v-for="(btn, index) in orderStatusActions"
                    :key="index"
                    @click="btn.action"
                    class="flex-1 rounded-md px-3 py-1.5 text-xs font-medium text-white transition-colors"
                    :class="btn.class"
                >
                    <span
                        v-if="
                            orderStatusActionId === btn.id &&
                            updateStatusOrderId === order.id
                        "
                        class="flex items-center justify-center"
                    >
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24">
                            <circle
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="white"
                                stroke-width="3"
                                fill="none"
                                opacity="0.3"
                            />
                            <path
                                d="M22 12a10 10 0 0 1-10 10"
                                stroke="white"
                                stroke-width="3"
                                fill="none"
                            />
                        </svg>
                    </span>
                    <span v-else>{{ btn.label }}</span>
                </button>
            </div>

            <button
                @click="openOrderDetails(order)"
                class="ml-auto rounded-md border border-sidebar-border/70 px-3 py-1.5 text-xs font-medium transition-colors hover:bg-sidebar-accent"
            >
                Details
            </button>
        </div>

        <OrderDetailsModal
            :order="selectedOrder"
            :is-open="isModalOpen"
            @close="closeModal"
            @update-status="handleUpdateStatus"
        />
    </div>
</template>

<style scoped></style>
