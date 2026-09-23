<script setup lang="ts">
import OrderDetailsModal from '@/components/admin/order/OrderDetailsModal.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import { useDateFormatter } from '@/composables/useDateFormatter';
import { useOrderStatusActionFlow } from '@/composables/useOrderStatusActionFlow';
import { usePermission } from '@/composables/usePermission';
import { formatAddress, getOrderStatusVariant } from '@/lib/utils';
import { useOrderStore } from '@/stores/order';
import { DeliveryTypeEnum } from '@/types/delivery';
import { OrderStatusEnum, OrderType, OrderTypeEnum } from '@/types/order';
import { OrderPermissionEnum } from '@/types/permission';
import {
    Building2,
    ChevronDown,
    ChevronUp,
    Coffee,
    LoaderCircle,
    MapPin,
    User,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    order: OrderType;
    updateStatusOrderId: number | null;
    isCancelling?: boolean;
}>();

const { can } = usePermission();

const { t } = useI18n();
const { formatDate, formatDateOnly } = useDateFormatter();

const emit = defineEmits<{
    'cancel-order': [orderId: number];
    'update-status': [orderId: number, newStatus: OrderStatusEnum];
}>();

const orderStatusActionId = ref<string>('');
const isExpanded = ref(false);
const confirmModalOpen = ref<boolean>(false);
const confirmModalMessage = ref<string>('');
const confirmModalType = ref<'destructive' | 'info'>('info');
const confirmModalAction = ref<() => void>(() => {});

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

const cancelOrder = () => {
    emit('cancel-order', props.order.id);
};

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

const isCompanyDelivery = computed(
    () => props.order.delivery_type === DeliveryTypeEnum.COMPANY,
);

const deliveryDestination = computed(() => {
    if (isCompanyDelivery.value && props.order.delivery_company) {
        const companyName = props.order.delivery_company.name;

        return props.order.delivery_date
            ? `${companyName} · ${formatDateOnly(props.order.delivery_date)}`
            : companyName;
    }

    return formatAddress(props.order.delivery_address);
});

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
        toast.success(t('confirmedOrder.orderCard.updateStatusSuccess'));
    } catch (error) {
        console.log(error);
        toast.error(t('confirmedOrder.orderCard.updateStatusFailed'));
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
        @click="toggleExpand"
        class="group rounded-lg border border-sidebar-border/70 bg-card p-3 transition-all hover:shadow-md md:p-4"
    >
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-1">
                    <h4 class="truncate text-sm font-semibold md:text-base">
                        <span>
                            {{ order.order_number }}
                        </span>
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
                    {{ t(`confirmedOrder.status.${order.status.key}`) }}
                </span>
                <button
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

        <div class="hidden md:block">
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
                    <component
                        :is="isCompanyDelivery ? Building2 : MapPin"
                        :size="14"
                        class="mt-0.5 flex-shrink-0 text-muted-foreground"
                    />
                    <span class="text-xs break-words">
                        {{ deliveryDestination }}
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
                    <span class="text-xs">
                        {{
                            t('confirmedOrder.orderCard.table', {
                                number: order.table_number,
                            })
                        }}
                    </span>
                </div>
            </div>

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
                        {{
                            t('confirmedOrder.orderCard.moreItems', {
                                count: order.items.length - 2,
                            })
                        }}
                    </div>
                </div>
            </div>

            <div
                class="mb-3 flex items-center justify-between border-t border-sidebar-border/50 pt-2"
            >
                <span class="text-sm font-medium">
                    {{ t('confirmedOrder.orderCard.total') }}
                </span>
                <span class="text-lg font-bold"
                    >€{{ order.total_inc_vat.toFixed(2) }}</span
                >
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div
                    v-if="can(OrderPermissionEnum.ORDER_UPDATE)"
                    class="flex min-w-[120px] flex-1 gap-2"
                >
                    <button
                        v-for="(btn, index) in orderStatusActions"
                        :key="index"
                        @click="btn.action"
                        class="flex-1 rounded-md px-3 py-2 text-sm font-medium whitespace-nowrap transition-colors md:px-3"
                        :class="btn.class"
                    >
                        <span
                            v-if="
                                orderStatusActionId === btn.id &&
                                updateStatusOrderId === order.id
                            "
                            class="flex items-center justify-center"
                        >
                            <LoaderCircle
                                class="h-3 w-3 animate-spin md:h-4 md:w-4"
                            />
                        </span>
                        <span v-else>{{ btn.label }}</span>
                    </button>

                    <button
                        v-if="order.status.value === OrderStatusEnum.CONFIRMED"
                        @click="
                            openConfirmModal(
                                t(
                                    'confirmedOrder.orderCard.cancelConfirmation',
                                ),
                                'destructive',
                                () => cancelOrder(),
                            )
                        "
                        class="flex-1 rounded-md bg-destructive px-3 py-2 text-sm font-medium whitespace-nowrap text-destructive-foreground transition-colors hover:bg-destructive/90"
                    >
                        <span>
                            {{
                                t(
                                    'confirmedOrder.orderCard.actions.cancelOrder',
                                )
                            }}
                        </span>
                    </button>
                </div>

                <button
                    v-if="can(OrderPermissionEnum.ORDER_VIEW)"
                    @click="openOrderDetails(order)"
                    class="rounded-md border border-sidebar-border/70 px-3 py-2 text-sm font-medium whitespace-nowrap transition-colors hover:bg-sidebar-accent md:px-3"
                >
                    {{ t('confirmedOrder.orderCard.details') }}
                </button>
            </div>
        </div>

        <div class="md:hidden" v-show="isExpanded">
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
                    <component
                        :is="isCompanyDelivery ? Building2 : MapPin"
                        :size="14"
                        class="mt-0.5 flex-shrink-0 text-muted-foreground"
                    />
                    <span class="flex-1 text-xs break-words">
                        {{ deliveryDestination }}
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
                    <span class="text-sm">
                        {{
                            t('confirmedOrder.orderCard.table', {
                                number: order.table_number,
                            })
                        }}
                    </span>
                </div>
            </div>

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

            <div
                class="mb-3 flex items-center justify-between border-t border-sidebar-border/50 pt-2"
            >
                <span class="text-base font-medium">
                    {{ t('confirmedOrder.orderCard.total') }}
                </span>
                <span class="text-xl font-bold"
                    >€{{ order.total_inc_vat.toFixed(2) }}</span
                >
            </div>

            <div class="flex flex-col gap-2">
                <div
                    v-if="can(OrderPermissionEnum.ORDER_UPDATE)"
                    class="flex gap-2"
                >
                    <button
                        v-for="(btn, index) in orderStatusActions"
                        :key="index"
                        @click="btn.action"
                        class="flex-1 rounded-md px-3 py-2 text-sm font-medium transition-colors"
                        :class="btn.class"
                    >
                        <span
                            v-if="
                                orderStatusActionId === btn.id &&
                                updateStatusOrderId === order.id
                            "
                            class="flex items-center justify-center"
                        >
                            <LoaderCircle class="h-4 w-4 animate-spin" />
                        </span>
                        <span v-else>{{ btn.label }}</span>
                    </button>
                </div>

                <button
                    v-if="order.status.value === OrderStatusEnum.CONFIRMED"
                    @click="
                        openConfirmModal(
                            t('confirmedOrder.orderCard.cancelConfirmation'),
                            'destructive',
                            () => cancelOrder(),
                        )
                    "
                    class="flex-1 rounded-md bg-destructive px-3 py-2 text-sm font-medium whitespace-nowrap text-destructive-foreground transition-colors hover:bg-destructive/90"
                >
                    <span>
                        {{ t('confirmedOrder.orderCard.actions.cancelOrder') }}
                    </span>
                </button>

                <button
                    v-if="can(OrderPermissionEnum.ORDER_VIEW)"
                    @click="openOrderDetails(order)"
                    class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 text-sm font-medium transition-colors hover:bg-sidebar-accent"
                >
                    {{ t('confirmedOrder.orderCard.details') }}
                </button>
            </div>
        </div>

        <OrderDetailsModal
            :order="selectedOrder"
            :is-open="isModalOpen"
            @close="closeModal"
            @update-status="handleUpdateStatus"
        />

        <ConfirmModal
            :open="confirmModalOpen"
            :onClose="closeConfirmModal"
            :message="confirmModalMessage"
            :type="confirmModalType"
            :isLoading="isCancelling!"
            @confirm="confirmModalAction"
        />
    </div>
</template>

<style scoped></style>
