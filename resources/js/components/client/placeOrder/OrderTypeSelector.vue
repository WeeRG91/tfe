<script setup lang="ts">
import { OrderTypeEnum, OrderTypeType } from '@/types/order';
import { ShoppingBag, Store, Table, Truck } from 'lucide-vue-next';

const props = defineProps<{
    modelValue: OrderTypeEnum | null;
    orderTypes: OrderTypeType[];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: OrderTypeEnum];
}>();

const getOrderTypeIcon = (orderTypeValue: OrderTypeEnum) => {
    switch (orderTypeValue) {
        case OrderTypeEnum.DINEIN:
            return Table;
        case OrderTypeEnum.TAKEAWAY:
            return ShoppingBag;
        case OrderTypeEnum.DELIVERY:
            return Truck;
        default:
            return Store;
    }
};

const selectType = (value: OrderTypeEnum) => {
    emit('update:modelValue', value);
};
</script>

<template>
    <div class="rounded-lg border bg-white p-6">
        <h2
            class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
        >
            <Store class="h-5 w-5" />
            Order Type
        </h2>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <button
                v-for="orderType in props.orderTypes"
                :key="orderType.value"
                @click="selectType(orderType.value)"
                :class="[
                    'flex items-center justify-center gap-2 rounded-lg border-2 p-4 transition-all',
                    props.modelValue === orderType.value
                        ? 'border-red-500 bg-red-50'
                        : 'border-gray-200 hover:border-red-200',
                ]"
            >
                <component
                    :is="getOrderTypeIcon(orderType.value)"
                    class="h-5 w-5"
                    :class="
                        props.modelValue === orderType.value
                            ? 'text-red-500'
                            : 'text-gray-400'
                    "
                />
                <span class="font-medium">{{ orderType.label }}</span>
            </button>
        </div>
    </div>
</template>

<style scoped></style>
