<script setup lang="ts">
import { useOrderStore } from '@/stores/order';
import { OrderType } from '@/types/order';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';
import OrderCard from './OrderCard.vue';

const orderStore = useOrderStore();

type OrderListTone = 'warning' | 'success' | 'destructive';

const props = defineProps<{
    title: string;
    icon: any;
    tone: OrderListTone;
    orders: OrderType[];
}>();

const { t } = useI18n();

const toneClasses = computed(() => {
    const tones: Record<
        OrderListTone,
        {
            border: string;
            icon: string;
        }
    > = {
        warning: {
            border: 'border-warning',
            icon: 'text-warning',
        },
        success: {
            border: 'border-success',
            icon: 'text-success',
        },
        destructive: {
            border: 'border-destructive',
            icon: 'text-destructive',
        },
    };

    return tones[props.tone];
});

const ordersToShow = ref<OrderType[]>(props.orders ?? []);
const isConfirmLoading = ref<boolean>(false);
const expandedOrderId = ref<number | null>(null);

const toggleOrder = (orderId: number) => {
    expandedOrderId.value = expandedOrderId.value === orderId ? null : orderId;
};

const cancelOrder = async (orderId: number) => {
    isConfirmLoading.value = true;

    try {
        await orderStore.cancel(orderId);

        ordersToShow.value = ordersToShow.value.filter(
            (order) => order.id !== orderId,
        );
    } catch (error) {
        console.error(error);
        toast.error(t('order.orderList.errors.cancel'));
    } finally {
        isConfirmLoading.value = false;
    }
};

const removeOrder = async (orderId: number) => {
    isConfirmLoading.value = true;

    try {
        await orderStore.remove(orderId);

        ordersToShow.value = ordersToShow.value.filter(
            (order) => order.id !== orderId,
        );
    } catch (error) {
        console.error(error);
        toast.error(t('order.orderList.errors.remove'));
    } finally {
        isConfirmLoading.value = false;
    }
};
</script>

<template>
    <div v-if="ordersToShow.length" class="space-y-3">
        <div
            class="flex items-center gap-2 border-l-4 pl-3"
            :class="toneClasses.border"
        >
            <component :is="icon" class="h-5 w-5" :class="toneClasses.icon" />
            <h2
                class="text-lg font-semibold tracking-wide text-foreground uppercase"
            >
                {{ title }}
            </h2>
            <span class="text-xs text-muted-foreground">{{
                ordersToShow.length
            }}</span>
        </div>

        <div class="space-y-4">
            <OrderCard
                v-for="order in ordersToShow"
                :key="order.id"
                :order-to-show="order"
                :is-confirm-loading="isConfirmLoading"
                :is-expanded="expandedOrderId === order.id"
                @toggle="toggleOrder"
                @cancel="cancelOrder"
                @remove="removeOrder"
            />
        </div>
    </div>
</template>

<style scoped></style>
