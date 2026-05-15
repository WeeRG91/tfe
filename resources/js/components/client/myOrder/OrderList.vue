<script setup lang="ts">
import OrderCard from './OrderCard.vue';

defineProps<{
    title: string;
    icon: any;
    iconColor: string;
    orders: any[];
}>();
</script>

<template>
    <div v-if="orders.length" class="space-y-3">
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
            <span class="text-xs text-gray-500">{{ orders.length }}</span>
        </div>

        <div class="space-y-4">
            <OrderCard
                v-for="order in orders"
                :key="order.id"
                :order-to-show="order"
            />
        </div>
    </div>
</template>

<style scoped></style>
