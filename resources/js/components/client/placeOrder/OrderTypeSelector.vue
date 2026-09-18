<script setup lang="ts">
import { OrderTypeEnum, OrderTypeType } from '@/types/order';
import { ShoppingBag, Store, Table, Truck } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    modelValue: OrderTypeEnum | null;
    orderTypes: OrderTypeType[];
    disabledOrderTypes: OrderTypeEnum[];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: OrderTypeEnum];
}>();

const { t } = useI18n();

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

const isTypeDisabled = (value: OrderTypeEnum) =>
    props.disabledOrderTypes.includes(value);

const selectType = (value: OrderTypeEnum) => {
    if (isTypeDisabled(value)) {
        return;
    }

    emit('update:modelValue', value);
};
</script>

<template>
    <div class="rounded-lg border bg-white p-6">
        <h2
            class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
        >
            <Store class="h-5 w-5" />
            {{ t('cart.orderType.title') }}
        </h2>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <button
                v-for="orderType in props.orderTypes"
                :key="orderType.value"
                type="button"
                :disabled="isTypeDisabled(orderType.value)"
                @click="selectType(orderType.value)"
                :class="[
                    'flex items-center justify-center gap-2 rounded-lg border-2 p-4 transition-all',
                    isTypeDisabled(orderType.value)
                        ? 'cursor-not-allowed border-gray-200 bg-gray-100 text-gray-400 opacity-60'
                        : props.modelValue === orderType.value
                          ? 'border-red-500 bg-red-50'
                          : 'border-gray-200 hover:border-red-200',
                ]"
            >
                <component
                    :is="getOrderTypeIcon(orderType.value)"
                    class="h-5 w-5"
                    :class="
                        isTypeDisabled(orderType.value)
                            ? 'text-gray-400'
                            : props.modelValue === orderType.value
                              ? 'text-red-500'
                              : 'text-gray-400'
                    "
                />
                <div class="flex flex-col items-start">
                    <span class="font-medium">
                        {{ t(`common.orderTypes.${orderType.key}`) }}
                    </span>

                    <span
                        v-if="isTypeDisabled(orderType.value)"
                        class="text-xs font-normal text-gray-500"
                    >
                        {{ t('cart.orderType.unavailableWhileClosed') }}
                    </span>
                </div>
            </button>
        </div>
    </div>
</template>

<style scoped></style>
