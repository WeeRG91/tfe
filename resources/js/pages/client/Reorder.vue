<script setup lang="ts">
import { OrderType, OrderTypeEnum, OrderTypeType } from '@/types/order';
import { PaymentMethodType } from '@/types/payment';
import { AddressType } from '@/types/address';
import { ref } from 'vue';

const props = defineProps<{
    orderToReorder: OrderType;
    orderTypes: OrderTypeType[];
    paymentMethods: PaymentMethodType[];
    addresses: AddressType[];
}>();

const selectedOrderTypeValue = ref<OrderTypeEnum | null>(null);
const selectedAddressId = ref<number | null>(null);
const paymentMethod = ref<number | null>(null);
const pickupTime = ref<string>('');
const pickupName = ref<string>('');
const pickupPhone = ref<string>('');
const tableNumber = ref<string>('');
const notes = ref<string>('');
</script>

<template></template>

<style scoped></style>
