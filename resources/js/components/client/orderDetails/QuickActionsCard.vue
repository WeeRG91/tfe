<script setup lang="ts">
import menu from '@/routes/menu';
import { OrderStatusEnum, OrderType } from '@/types/order';
import {
    CreditCard,
    Printer,
    ShoppingBag,
    Trash,
    XCircle,
} from 'lucide-vue-next';
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
    <div
        class="rounded-lg border border-border bg-card p-6 text-card-foreground"
    >
        <div class="space-y-3">
            <button
                v-if="
                    ![
                        OrderStatusEnum.CANCELLED,
                        OrderStatusEnum.PENDING,
                    ].includes(orderToShow.status.value)
                "
                @click="emit('print')"
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-input bg-background px-4 py-2.5 text-sm font-medium text-foreground transition-all hover:border-primary hover:bg-accent hover:text-accent-foreground"
            >
                <Printer class="h-4 w-4" />
                {{ t('order.quickActionsCard.printOrderSummary') }}
            </button>
            <button
                v-if="orderToShow.status.value === OrderStatusEnum.CANCELLED"
                @click="emit('remove', orderToShow.id)"
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-input bg-background px-4 py-2.5 text-sm font-medium text-foreground transition-all hover:border-destructive hover:bg-destructive/10 hover:text-destructive"
            >
                <Trash class="h-4 w-4" />
                {{ t('order.quickActionsCard.removeOrder') }}
            </button>
            <button
                v-if="orderToShow.status.value === OrderStatusEnum.PENDING"
                @click="emit('remove', orderToShow.id)"
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-primary bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90"
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
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-input bg-background px-4 py-2.5 text-sm font-medium text-foreground transition-all hover:border-destructive hover:bg-destructive/10 hover:text-destructive"
            >
                <XCircle class="h-4 w-4" />
                <span>{{ t('order.orderCard.cancelOrder') }}</span>
            </button>

            <a
                :href="menu.dish().url"
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-input bg-background px-4 py-2.5 text-sm font-medium text-foreground transition-all hover:border-primary hover:bg-accent hover:text-accent-foreground"
            >
                <ShoppingBag class="h-4 w-4" />
                {{ t('order.quickActionsCard.continueShopping') }}
            </a>
        </div>
    </div>

    <div
        class="rounded-lg border border-border bg-card p-6 text-card-foreground"
    >
        <div class="text-center">
            <h3 class="font-medium text-card-foreground">
                {{ t('order.quickActionsCard.needHelp') }}
            </h3>
            <p class="mt-1 text-xs text-muted-foreground">
                {{ t('order.quickActionsCard.contactSupport') }}
            </p>
            <p class="mt-2 text-sm font-medium text-primary">
                📞 +1 (555) 123-4567
            </p>
            <p class="text-xs text-muted-foreground">
                support@yourrestaurant.com
            </p>
        </div>
    </div>
</template>

<style scoped></style>
