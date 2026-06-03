<script setup lang="ts">
import { Smartphone, Printer, Trash, ShoppingBag } from 'lucide-vue-next';
import menu from '@/routes/menu';
import { OrderStatusEnum, OrderType } from '@/types/order';

defineProps<{
    orderToShow: OrderType;
}>();

defineEmits<{
    print: [];
    remove: [orderId: number];
}>();
</script>

<template>
    <div class="rounded-lg border bg-white p-6">
        <h2
            class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
        >
            <Smartphone class="h-5 w-5 text-red-500" />
            Quick Actions
        </h2>

        <div class="space-y-3">
            <button
                v-if="orderToShow.status.value !== OrderStatusEnum.CANCELLED"
                @click="$emit('print')"
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-all hover:border-red-500 hover:bg-red-50"
            >
                <Printer class="h-4 w-4" />
                Print Order Summary
            </button>
            <button
                v-if="orderToShow.status.value === OrderStatusEnum.CANCELLED"
                @click="$emit('remove', orderToShow.id)"
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-all hover:border-red-500 hover:bg-red-50"
            >
                <Trash class="h-4 w-4" />
                Remove Order
            </button>

            <a
                :href="menu.dish().url"
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-all hover:border-red-500 hover:bg-red-50"
            >
                <ShoppingBag class="h-4 w-4" />
                Continue Shopping
            </a>
        </div>
    </div>

    <div class="rounded-lg border bg-white p-6">
        <div class="text-center">
            <h3 class="font-medium text-gray-800">Need Help?</h3>
            <p class="mt-1 text-xs text-gray-500">
                Contact our customer support
            </p>
            <p class="mt-2 text-sm font-medium text-red-600">
                📞 +1 (555) 123-4567
            </p>
            <p class="text-xs text-gray-500">support@yourrestaurant.com</p>
        </div>
    </div>
</template>

<style scoped></style>
