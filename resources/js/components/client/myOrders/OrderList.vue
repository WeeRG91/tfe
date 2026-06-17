<script setup lang="ts">
import OrderCard from './OrderCard.vue';
import { OrderType } from '@/types/order';
import { ref } from 'vue';
import { useOrderStore } from '@/stores/order';
import { toast } from 'vue-sonner';

const orderStore = useOrderStore();

const props = defineProps<{
    title: string;
    icon: any;
    iconColor: string;
    orders: OrderType[];
}>();

const ordersToShow = ref<OrderType[]>(props.orders ?? []);
const isConfirmLoading = ref<boolean>(false);

const cancelOrder = async (orderId: number) => {
    isConfirmLoading.value = true;

    try {
        await orderStore.cancel(orderId);

        ordersToShow.value = ordersToShow.value.filter(
            (order) => order.id !== orderId,
        );
    } catch (error) {
        console.error(error);
        toast.error('Failed to cancel the order.');
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
        toast.error('Failed to remove the order.');
    } finally {
        isConfirmLoading.value = false;
    }
};
</script>

<template>
    <div v-if="ordersToShow.length" class="space-y-3">
        <div
            class="flex items-center gap-2 border-l-4 pl-3"
            :class="`border-${iconColor}-500`"
        >
            <component
                :is="icon"
                class="h-5 w-5"
                :class="`text-${iconColor}-500`"
            />
            <h2
                class="text-lg font-semibold tracking-wide text-gray-800 uppercase"
            >
                {{ title }}
            </h2>
            <span class="text-xs text-gray-500">{{ ordersToShow.length }}</span>
        </div>

        <div class="space-y-4">
            <OrderCard
                v-for="order in ordersToShow"
                :key="order.id"
                :order-to-show="order"
                :is-confirm-loading="isConfirmLoading"
                @cancel="cancelOrder"
                @remove="removeOrder"
            />
        </div>
    </div>
</template>

<style scoped></style>
