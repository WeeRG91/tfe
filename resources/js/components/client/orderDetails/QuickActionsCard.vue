<script setup lang="ts">
import {
    Printer,
    Trash,
    ShoppingBag,
    CreditCard,
    XCircle,
} from 'lucide-vue-next';
import menu from '@/routes/menu';
import { OrderStatusEnum, OrderType } from '@/types/order';
import { useI18n } from 'vue-i18n';

defineProps<{
    orderToShow: OrderType;
}>();

const emit = defineEmits<{
    print: [];
    remove: [orderId: number];
}>();

const { t } = useI18n();
</script>

<template>
    <div class="rounded-lg border bg-white p-6">
        <div class="space-y-3">
            <button
                v-if="
                    ![
                        OrderStatusEnum.CANCELLED,
                        OrderStatusEnum.PENDING,
                    ].includes(orderToShow.status.value)
                "
                @click="emit('print')"
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-all hover:border-red-500 hover:bg-red-50"
            >
                <Printer class="h-4 w-4" />
                {{ t('order.quickActionsCard.printOrderSummary') }}
            </button>
            <button
                v-if="orderToShow.status.value === OrderStatusEnum.CANCELLED"
                @click="emit('remove', orderToShow.id)"
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-all hover:border-red-500 hover:bg-red-50"
            >
                <Trash class="h-4 w-4" />
                {{ t('order.quickActionsCard.removeOrder') }}
            </button>
            <button
                v-if="orderToShow.status.value === OrderStatusEnum.PENDING"
                @click="emit('remove', orderToShow.id)"
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-all hover:border-red-500 hover:bg-red-50"
            >
                <CreditCard class="h-4 w-4" />
                <span>{{ t('order.orderCard.completeOrder') }}</span>
            </button>
            <button
                v-if="
                    orderToShow.status?.value === OrderStatusEnum.PENDING ||
                    orderToShow.status.value === OrderStatusEnum.CONFIRMED
                "
                @click="emit('remove', orderToShow.id)"
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-all hover:border-red-500 hover:bg-red-50"
            >
                <XCircle class="h-4 w-4" />
                <span>{{ t('order.orderCard.cancelOrder') }}</span>
            </button>

            <a
                :href="menu.dish().url"
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-all hover:border-red-500 hover:bg-red-50"
            >
                <ShoppingBag class="h-4 w-4" />
                {{ t('order.quickActionsCard.continueShopping') }}
            </a>
        </div>
    </div>

    <div class="rounded-lg border bg-white p-6">
        <div class="text-center">
            <h3 class="font-medium text-gray-800">
                {{ t('order.quickActionsCard.needHelp') }}
            </h3>
            <p class="mt-1 text-xs text-gray-500">
                {{ t('order.quickActionsCard.contactSupport') }}
            </p>
            <p class="mt-2 text-sm font-medium text-red-600">
                📞 +1 (555) 123-4567
            </p>
            <p class="text-xs text-gray-500">support@yourrestaurant.com</p>
        </div>
    </div>
</template>

<style scoped></style>
