<script setup lang="ts">
import ConfirmModal from '@/components/ConfirmModal.vue';
import {
    formatPrice,
    getOrderStatusIcon,
    getOrderStatusVariant,
    getOrderTypeIcon,
    getPaymentMethodIcon,
    getPaymentStatusIcon,
    getPaymentStatusVariant,
} from '@/lib/utils';
import order from '@/routes/order';
import paymentOrder from '@/routes/payment-order';
import { OrderStatusEnum, OrderType } from '@/types/order';
import { PaymentMethodEnum, PaymentStatusEnum } from '@/types/payment';
import { router } from '@inertiajs/vue3';
import {
    ChevronDown,
    CreditCard,
    MessageCircle,
    NotebookText,
    Phone,
    Repeat,
    User,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import OrderItemsList from './OrderItemsList.vue';
import { useI18n } from 'vue-i18n';
import { useDateFormatter } from '@/composables/useDateFormatter';

const props = defineProps<{
    orderToShow: OrderType;
    isConfirmLoading: boolean;
    isExpanded: boolean;
}>();

const emit = defineEmits<{
    cancel: [orderId: number];
    remove: [orderId: number];
    toggle: [orderId: number];
}>();

const { t } = useI18n();

const { formatDate } = useDateFormatter();

const confirmModalOpen = ref<boolean>(false);
const confirmModalMessage = ref<string>('');
const confirmModalType = ref<'destructive' | 'info'>('info');
const confirmModalAction = ref<() => void>(() => {});

const cancellationDescription = computed(() => {
    if (
        props.orderToShow.payment_method.value === PaymentMethodEnum.CARD &&
        props.orderToShow.payment_status.value === PaymentStatusEnum.PAID
    ) {
        return t(
            'order.orderCard.refundConfirmation',
            formatPrice(props.orderToShow.total_inc_vat),
        );
    }

    return t('order.orderCard.cancelConfirmation');
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

const toggleDetails = () => {
    emit('toggle', props.orderToShow.id);
};

const cancelOrder = () => {
    emit('cancel', props.orderToShow.id);
};

const removeOrder = async () => {
    emit('remove', props.orderToShow.id);
};

const getOrderStatusProgress = (status: OrderStatusEnum) => {
    const progresses: Record<OrderStatusEnum, number> = {
        [OrderStatusEnum.PENDING]: 25,
        [OrderStatusEnum.CONFIRMED]: 40,
        [OrderStatusEnum.PREPARING]: 60,
        [OrderStatusEnum.READY]: 80,
        [OrderStatusEnum.DELIVERING]: 85,
        [OrderStatusEnum.COMPLETED]: 100,
        [OrderStatusEnum.CANCELLED]: 0,
    };

    return progresses[status] || 0;
};
</script>

<template>
    <div
        class="rounded-lg border bg-white shadow-sm transition-all hover:shadow-md"
    >
        <div
            @click="toggleDetails"
            class="cursor-pointer p-4 transition-colors hover:bg-gray-50"
        >
            <div class="flex flex-col gap-3">
                <div class="flex items-start justify-between">
                    <div class="flex flex-1 items-center gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100"
                        >
                            <component
                                :is="getOrderTypeIcon(orderToShow.type.value)"
                                class="h-5 w-5 text-gray-600"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold text-gray-800">
                                    #{{ orderToShow.order_number }}
                                </p>
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium',
                                        getOrderStatusVariant(
                                            orderToShow.status.value,
                                        ),
                                    ]"
                                >
                                    <component
                                        :is="
                                            getOrderStatusIcon(
                                                orderToShow.status.value,
                                            )
                                        "
                                        class="h-3 w-3"
                                    />
                                    {{
                                        t(
                                            `common.orderStatus.${orderToShow.status.key}`,
                                        )
                                    }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500">
                                {{ formatDate(orderToShow.created_at) }}
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-xs text-gray-500">
                            {{ t('order.orderCard.total') }}
                        </p>
                        <p class="text-lg font-bold text-red-500">
                            €{{ formatPrice(orderToShow.total_inc_vat) }}
                        </p>
                    </div>
                </div>

                <div class="mt-2">
                    <div
                        class="mb-1 flex justify-between text-xs text-gray-600"
                    >
                        <span>{{ t('order.orderCard.orderProgress') }}</span>
                        <span
                            >{{
                                getOrderStatusProgress(
                                    orderToShow.status?.value,
                                )
                            }}%</span
                        >
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-gray-100">
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-amber-500 to-red-500 transition-all duration-500"
                            :style="{
                                width: `${getOrderStatusProgress(orderToShow.status?.value)}%`,
                            }"
                        ></div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <component
                            :is="getOrderTypeIcon(orderToShow.type.value)"
                            class="h-3 w-3"
                        />
                        <span>{{
                            t(`common.orderTypes.${orderToShow.type.key}`)
                        }}</span>
                    </div>
                    <ChevronDown
                        class="h-4 w-4 text-gray-400 transition-transform duration-200"
                        :class="{ 'rotate-180': isExpanded }"
                    />
                </div>
            </div>
        </div>

        <div v-show="isExpanded" class="border-t border-gray-100">
            <div class="space-y-4 bg-gray-50 p-4">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="flex items-start gap-2 rounded-lg bg-white p-3">
                        <User class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" />
                        <div>
                            <p
                                class="text-xs font-semibold text-gray-500 uppercase"
                            >
                                {{ t('order.orderCard.customer') }}
                            </p>
                            <p class="text-sm font-medium text-gray-800">
                                {{
                                    orderToShow.user?.name ||
                                    t('order.orderCard.guest')
                                }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ orderToShow.user?.email }}
                            </p>
                            <p
                                v-if="orderToShow.pickup_phone"
                                class="flex items-center gap-1 text-xs text-gray-500"
                            >
                                <Phone class="h-3 w-3" />
                                {{ orderToShow.pickup_phone }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-2 rounded-lg bg-white p-3">
                        <component
                            :is="
                                getPaymentMethodIcon(
                                    orderToShow.payment_method.value,
                                )
                            "
                            class="mt-0.5 h-4 w-4 shrink-0 text-purple-500"
                        />
                        <div>
                            <p
                                class="text-xs font-semibold text-gray-500 uppercase"
                            >
                                {{ t('order.orderCard.payment') }}
                            </p>
                            <p class="text-sm text-gray-700">
                                {{
                                    t(
                                        `common.paymentMethod.${orderToShow.payment_method.key}`,
                                    )
                                }}
                            </p>
                            <span
                                :class="[
                                    'mt-1 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium',
                                    getPaymentStatusVariant(
                                        orderToShow.payment_status.value,
                                    ),
                                ]"
                            >
                                <component
                                    :is="
                                        getPaymentStatusIcon(
                                            orderToShow.payment_status.value,
                                        )
                                    "
                                    class="h-3 w-3"
                                />
                                {{
                                    t(
                                        `common.paymentStatus.${orderToShow.payment_status.key}`,
                                    )
                                }}
                            </span>
                        </div>
                    </div>

                    <div v-if="orderToShow.notes" class="sm:col-span-2">
                        <div
                            class="flex items-start gap-2 rounded-lg bg-amber-50 p-3"
                        >
                            <MessageCircle
                                class="mt-0.5 h-4 w-4 shrink-0 text-amber-600"
                            />
                            <div>
                                <p
                                    class="text-xs font-semibold text-amber-700 uppercase"
                                >
                                    {{
                                        t('order.orderCard.specialInstructions')
                                    }}
                                </p>
                                <p class="text-sm text-gray-600 italic">
                                    "{{ orderToShow.notes }}"
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <OrderItemsList :order="orderToShow" />

                <div
                    class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"
                >
                    <button
                        @click.stop="
                            router.visit(order.orderDetails(orderToShow.id).url)
                        "
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-all hover:bg-red-50 disabled:opacity-50 sm:w-40"
                    >
                        <NotebookText class="h-4 w-4" />
                        <span>{{ t('order.orderCard.seeDetails') }}</span>
                    </button>

                    <button
                        v-if="
                            orderToShow.status?.value ===
                                OrderStatusEnum.PENDING ||
                            orderToShow.status.value ===
                                OrderStatusEnum.CONFIRMED
                        "
                        @click="
                            openConfirmModal(
                                cancellationDescription,
                                'destructive',
                                () => cancelOrder(),
                            )
                        "
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-all hover:bg-red-50 disabled:opacity-50 sm:w-40"
                    >
                        <XCircle class="h-4 w-4" />
                        <span>{{ t('order.orderCard.cancelOrder') }}</span>
                    </button>

                    <button
                        v-if="
                            orderToShow.status?.value ===
                            OrderStatusEnum.PENDING
                        "
                        @click="
                            router.visit(
                                paymentOrder.payment(orderToShow.id).url,
                            )
                        "
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-all hover:bg-red-50 disabled:opacity-50 sm:w-40"
                    >
                        <CreditCard class="h-4 w-4" />
                        <span>{{ t('order.orderCard.completeOrder') }}</span>
                    </button>

                    <button
                        v-if="
                            orderToShow.status?.value ===
                            OrderStatusEnum.CANCELLED
                        "
                        @click="
                            openConfirmModal(
                                t('order.orderCard.removeConfirmation'),
                                'destructive',
                                () => removeOrder(),
                            )
                        "
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-all hover:bg-red-50 disabled:opacity-50 sm:w-40"
                    >
                        <XCircle class="h-4 w-4" />
                        <span>{{ t('order.orderCard.removeOrder') }}</span>
                    </button>

                    <button
                        v-if="
                            orderToShow.status?.value ===
                                OrderStatusEnum.COMPLETED ||
                            orderToShow.status?.value ===
                                OrderStatusEnum.CANCELLED
                        "
                        @click.stop="
                            router.visit(
                                order.reorder(props.orderToShow.id).url,
                            )
                        "
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-500 px-5 py-2 text-sm font-semibold text-white transition-all hover:bg-red-600 disabled:opacity-50 sm:w-40"
                    >
                        <Repeat class="h-4 w-4" />
                        <span>{{ t('order.orderCard.reorder') }}</span>
                    </button>
                </div>
            </div>
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
</template>

<style scoped></style>
