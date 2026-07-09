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
import { ChevronDown, ChevronUp, Coffee, MapPin, User } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { usePermission } from '@/composables/usePermission';
import { OrderPermissionEnum } from '@/types/permission';

const props = defineProps<{
    order: OrderType;
    updateStatusOrderId: number | null;
}>();

const {can} = usePermission();

const emit = defineEmits<{
    'update-status': [orderId: number, newStatus: OrderStatusEnum];
}>();

const orderStatusActionId = ref<string>('');
const isExpanded = ref(false);

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

const toggleExpand = () => {
    isExpanded.value = !isExpanded.value;
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
        class="group rounded-lg border border-sidebar-border/70 bg-card p-3 transition-all hover:shadow-md md:p-4"
    >
        <!-- Order Header - Always Visible -->
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-1">
                    <component
                        :is="getOrderTypeIcon(order.type.value)"
                        :size="14"
                        class="flex-shrink-0 md:h-4 md:w-4"
                    />
                    <h4 class="truncate text-sm font-semibold md:text-base">
                        <span>Order #{{ order.order_number }}</span>
                    </h4>
                </div>
                <p class="text-xs text-muted-foreground">
                    {{ formatDate(order.created_at) }}
                </p>
            </div>
            <div class="flex flex-shrink-0 items-center gap-2">
                <span
                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium whitespace-nowrap"
                    :class="getOrderStatusVariant(order.status.value)"
                >
                    {{ order.status.label }}
                </span>
                <button
                    @click="toggleExpand"
                    class="rounded-md p-1 transition-colors hover:bg-sidebar-accent md:hidden"
                >
                    <component
                        :is="isExpanded ? ChevronUp : ChevronDown"
                        :size="16"
                        class="text-muted-foreground"
                    />
                </button>
            </div>
        </div>

        <!-- Collapsible Content for Mobile -->
        <div class="hidden md:block">
            <!-- Customer Info -->
            <div class="mt-3 mb-3 space-y-1 text-sm">
                <div class="flex items-center gap-2">
                    <User
                        :size="14"
                        class="flex-shrink-0 text-muted-foreground"
                    />
                    <span class="truncate">{{ order.user.name }}</span>
                </div>
                <div
                    v-if="order.type.value === OrderTypeEnum.DELIVERY"
                    class="flex items-start gap-2"
                >
                    <MapPin
                        :size="14"
                        class="mt-0.5 flex-shrink-0 text-muted-foreground"
                    />
                    <span class="text-xs break-words">
                        {{ formatAddress(order.delivery_address) }}
                    </span>
                </div>
                <div
                    v-if="order.type.value === OrderTypeEnum.TAKEAWAY"
                    class="flex flex-wrap items-center gap-2"
                >
                    <User
                        :size="14"
                        class="flex-shrink-0 text-muted-foreground"
                    />
                    <span class="text-xs break-words"
                        >{{ order.pickup_name }} |
                        {{ order.pickup_phone }}</span
                    >
                </div>
                <div
                    v-if="order.type.value === OrderTypeEnum.DINEIN"
                    class="flex items-center gap-2"
                >
                    <Coffee
                        :size="14"
                        class="flex-shrink-0 text-muted-foreground"
                    />
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
                        <span class="flex-1 truncate"
                            >{{ item.quantity }}x {{ item.item.name }}</span
                        >
                        <span class="ml-2 flex-shrink-0"
                            >€{{ item.total_inc_vat.toFixed(2) }}</span
                        >
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
            <div class="flex flex-wrap items-center gap-2">
                <div v-if="can(OrderPermissionEnum.ORDER_UPDATE)" class="flex min-w-[120px] flex-1 gap-2">
                    <button
                        v-for="(btn, index) in orderStatusActions"
                        :key="index"
                        @click="btn.action"
                        class="flex-1 rounded-md px-2 py-1.5 text-xs font-medium whitespace-nowrap text-white transition-colors md:px-3"
                        :class="btn.class"
                    >
                        <span
                            v-if="
                                orderStatusActionId === btn.id &&
                                updateStatusOrderId === order.id
                            "
                            class="flex items-center justify-center"
                        >
                            <svg
                                class="h-3 w-3 animate-spin md:h-4 md:w-4"
                                viewBox="0 0 24 24"
                            >
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
                    v-if="can(OrderPermissionEnum.ORDER_VIEW)"
                    @click="openOrderDetails(order)"
                    class="rounded-md border border-sidebar-border/70 px-2 py-1.5 text-xs font-medium whitespace-nowrap transition-colors hover:bg-sidebar-accent md:px-3"
                >
                    Details
                </button>
            </div>
        </div>

        <!-- Mobile Expanded Content -->
        <div class="md:hidden" v-show="isExpanded">
            <!-- Customer Info -->
            <div class="mt-3 mb-3 space-y-2 text-sm">
                <div class="flex items-center gap-2">
                    <User
                        :size="14"
                        class="flex-shrink-0 text-muted-foreground"
                    />
                    <span class="text-sm">{{ order.user.name }}</span>
                </div>
                <div
                    v-if="order.type.value === OrderTypeEnum.DELIVERY"
                    class="flex items-start gap-2"
                >
                    <MapPin
                        :size="14"
                        class="mt-0.5 flex-shrink-0 text-muted-foreground"
                    />
                    <span class="flex-1 text-xs break-words">
                        {{ formatAddress(order.delivery_address) }}
                    </span>
                </div>
                <div
                    v-if="order.type.value === OrderTypeEnum.TAKEAWAY"
                    class="flex flex-wrap items-center gap-2"
                >
                    <User
                        :size="14"
                        class="flex-shrink-0 text-muted-foreground"
                    />
                    <span class="text-sm"
                        >{{ order.pickup_name }} |
                        {{ order.pickup_phone }}</span
                    >
                </div>
                <div
                    v-if="order.type.value === OrderTypeEnum.DINEIN"
                    class="flex items-center gap-2"
                >
                    <Coffee
                        :size="14"
                        class="flex-shrink-0 text-muted-foreground"
                    />
                    <span class="text-sm">Table {{ order.table_number }}</span>
                </div>
            </div>

            <!-- Order Items Preview -->
            <div class="mb-3 border-t border-sidebar-border/50 pt-2">
                <div class="space-y-2">
                    <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="flex justify-between text-sm"
                    >
                        <span class="flex-1"
                            >{{ item.quantity }}x {{ item.item.name }}</span
                        >
                        <span class="ml-2"
                            >€{{ item.total_inc_vat.toFixed(2) }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Order Total -->
            <div
                class="mb-3 flex items-center justify-between border-t border-sidebar-border/50 pt-2"
            >
                <span class="text-base font-medium">Total:</span>
                <span class="text-xl font-bold"
                    >€{{ order.total_inc_vat.toFixed(2) }}</span
                >
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-2">
                <div v-if="can(OrderPermissionEnum.ORDER_UPDATE)" class="flex gap-2">
                    <button
                        v-for="(btn, index) in orderStatusActions"
                        :key="index"
                        @click="btn.action"
                        class="flex-1 rounded-md px-3 py-2 text-sm font-medium text-white transition-colors"
                        :class="btn.class"
                    >
                        <span
                            v-if="
                                orderStatusActionId === btn.id &&
                                updateStatusOrderId === order.id
                            "
                            class="flex items-center justify-center"
                        >
                            <svg
                                class="h-4 w-4 animate-spin"
                                viewBox="0 0 24 24"
                            >
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
                    v-if="can(OrderPermissionEnum.ORDER_VIEW)"
                    @click="openOrderDetails(order)"
                    class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 text-sm font-medium transition-colors hover:bg-sidebar-accent"
                >
                    View Full Details
                </button>
            </div>
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
