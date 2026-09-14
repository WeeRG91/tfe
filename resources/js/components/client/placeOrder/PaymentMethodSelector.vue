<script setup lang="ts">
import { PaymentMethodEnum, PaymentMethodType } from '@/types/payment';
import { HandCoins } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    modelValue: number | null;
    paymentMethods: PaymentMethodType[];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number | null];
}>();

const { t } = useI18n();

const getPaymentIcon = (method: PaymentMethodEnum) => {
    switch (method) {
        case PaymentMethodEnum.CASH:
            return '💵';
        case PaymentMethodEnum.CARD:
            return '💳';
        default:
            return '💰';
    }
};

const getPaymentDescription = (method: PaymentMethodEnum) => {
    switch (method) {
        case PaymentMethodEnum.CASH:
            return t('cart.paymentMethod.descriptions.cash');
        case PaymentMethodEnum.CARD:
            return t('cart.paymentMethod.descriptions.card');
        default:
            return t('cart.paymentMethod.descriptions.default');
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
            {{ t('cart.paymentMethod.title') }}
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
                    {{ getPaymentIcon(method.value) }}
                </span>
                <div class="text-left">
                    <div class="font-medium">
                        {{ t(`common.paymentMethod.${method.key}`) }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ getPaymentDescription(method.value) }}
                    </div>
                </div>
            </button>
        </div>
    </div>
</template>

<style scoped></style>
