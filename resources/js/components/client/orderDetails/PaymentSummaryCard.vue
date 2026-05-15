<script setup lang="ts">
import { OrderType } from '@/types/order';
import { formatPrice } from '@/lib/utils';
import { CreditCard, CheckCircle } from 'lucide-vue-next';

defineProps<{
    orderToShow: OrderType;
}>();

</script>

<template>
    <div class="rounded-lg border bg-white p-6">
        <h2
            class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
        >
            <CreditCard class="h-5 w-5 text-red-500" />
            Payment Summary
        </h2>

        <div class="space-y-2 border-b border-gray-100 pb-4">
            <div
                v-for="vat in orderToShow.vat_breakdown"
                :key="vat.vat_rate"
                class="flex justify-between text-sm text-gray-600"
            >
                <span>VAT ({{ vat.vat_rate }}%</span>
                <span>€{{ formatPrice(vat.vat_total) }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-600">
                <span>Total VAT</span>
                <span>€{{ formatPrice(orderToShow.vat_total) }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-600">
                <span>Subtotal</span>
                <span>€{{ formatPrice(orderToShow.subtotal) }}</span>
            </div>
            <div
                v-if="
                    orderToShow.type.label === 'Delivery' &&
                    orderToShow.delivery_fee
                "
                class="flex justify-between text-sm text-red-600"
            >
                <span>Delivery Fee</span>
                <span>+€{{ formatPrice(orderToShow.delivery_fee) }}</span>
            </div>
            <div
                v-if="orderToShow.discount_total"
                class="flex justify-between text-sm text-green-600"
            >
                <span>-€{{ formatPrice(orderToShow.discount_total) }}</span>
            </div>
        </div>

        <div class="mt-4 flex justify-between text-lg font-semibold">
            <span>Total Paid</span>
            <span class="text-green-600"
                >€{{ formatPrice(orderToShow.total_inc_vat) }}</span
            >
        </div>

        <div
            v-if="orderToShow.payment_status"
            class="mt-4 rounded-md p-3"
            :class="
                orderToShow.payment_status.value === 2
                    ? 'bg-green-50'
                    : 'bg-yellow-50'
            "
        >
            <div class="flex items-center gap-2">
                <CheckCircle
                    v-if="orderToShow.payment_status.value === 2"
                    class="h-4 w-4 text-green-600"
                />
                <CheckCircle v-else class="h-4 w-4 text-yellow-600" />
                <p
                    :class="
                        orderToShow.payment_status.value === 2
                            ? 'text-green-700'
                            : 'text-yellow-700'
                    "
                    class="text-sm"
                >
                    Payment {{ orderToShow.payment_status.label.toLowerCase() }}
                    <span v-if="orderToShow.payment_method?.label">
                        via {{ orderToShow.payment_method.label }}</span
                    >
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
