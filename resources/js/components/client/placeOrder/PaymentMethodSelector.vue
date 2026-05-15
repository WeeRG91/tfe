<script setup lang="ts">
import { HandCoins } from 'lucide-vue-next';
import type { PaymentMethodType } from '@/types/payment';

const props = defineProps<{
    modelValue: number | null;
    paymentMethods: PaymentMethodType[];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number | null];
}>();

const getPaymentIcon = (method: PaymentMethodType) => {
    switch (method.value) {
        case 1:
            return '💵';
        case 2:
            return '💳';
        default:
            return '💰';
    }
};

const getPaymentDescription = (method: PaymentMethodType) => {
    switch (method.value) {
        case 1:
            return 'Pay after receiving your order or your meal';
        case 2:
            return 'Pay securely with card';
        default:
            return 'Select payment method';
    }
};

const selectMethod = (value: number) => {
    emit('update:modelValue', value);
};
</script>

<template>
    <div class="rounded-lg border bg-white p-6">
        <h2
            class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
        >
            <HandCoins class="h-5 w-5" />
            Payment Method
        </h2>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <button
                v-for="method in props.paymentMethods"
                :key="method.value"
                @click="selectMethod(method.value)"
                :class="[
                    'flex items-center justify-center gap-3 rounded-lg border-2 p-4 transition-all',
                    props.modelValue === method.value
                        ? 'border-red-500 bg-red-50'
                        : 'border-gray-200 hover:border-red-200',
                ]"
            >
                <span class="text-2xl">
                    {{ getPaymentIcon(method) }}
                </span>
                <div class="text-left">
                    <div class="font-medium">
                        {{ method.label }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ getPaymentDescription(method) }}
                    </div>
                </div>
            </button>
        </div>
    </div>
</template>

<style scoped></style>
