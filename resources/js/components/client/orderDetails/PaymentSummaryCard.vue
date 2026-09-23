<script setup lang="ts">
import { formatPrice } from '@/lib/utils';
import { OrderType, OrderTypeEnum } from '@/types/order';
import { PaymentMethodEnum, PaymentStatusEnum } from '@/types/payment';
import { CheckCircle, Clock, CreditCard } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

defineProps<{
    orderToShow: OrderType;
}>();

const { t } = useI18n();
</script>

<template>
    <div
        class="rounded-lg border border-border bg-card p-6 text-card-foreground"
    >
        <h2
            class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
        >
            <CreditCard class="h-5 w-5 text-primary" />
            {{ t('order.paymentSummaryCard.title') }}
        </h2>

        <div class="space-y-2 border-b border-border pb-4">
            <div
                v-for="vat in orderToShow.vat_breakdown"
                :key="vat.vat_rate"
                class="flex justify-between text-sm text-muted-foreground"
            >
                <span>
                    {{
                        t('order.paymentSummaryCard.vat', {
                            rate: vat.vat_rate,
                        })
                    }}</span
                >
                <span>€{{ formatPrice(vat.vat_total) }}</span>
            </div>
            <div class="flex justify-between text-sm text-muted-foreground">
                <span>{{ t('order.paymentSummaryCard.totalVat') }}</span>
                <span>€{{ formatPrice(orderToShow.vat_total) }}</span>
            </div>
            <div class="flex justify-between text-sm text-muted-foreground">
                <span>{{ t('order.paymentSummaryCard.subtotal') }}</span>
                <span>€{{ formatPrice(orderToShow.subtotal) }}</span>
            </div>
            <div
                v-if="
                    orderToShow.type.value === OrderTypeEnum.DELIVERY &&
                    orderToShow.delivery_fee
                "
                class="flex justify-between text-sm text-primary"
            >
                <span>{{ t('order.paymentSummaryCard.deliveryFee') }}</span>
                <span>+€{{ formatPrice(orderToShow.delivery_fee) }}</span>
            </div>
            <div
                v-if="orderToShow.discount_total"
                class="flex justify-between text-sm text-success"
            >
                <span>-€{{ formatPrice(orderToShow.discount_total) }}</span>
            </div>
        </div>

        <div class="mt-4 flex justify-between text-lg font-semibold">
            <span>{{ t('order.paymentSummaryCard.totalPaid') }}</span>
            <span class="text-success"
                >€{{ formatPrice(orderToShow.total_inc_vat) }}</span
            >
        </div>

        <div
            v-if="orderToShow.payment_status"
            class="mt-4 rounded-md p-3"
            :class="
                orderToShow.payment_status.value === PaymentStatusEnum.PAID
                    ? 'bg-success/10'
                    : 'bg-warning/10'
            "
        >
            <div class="flex items-center gap-2">
                <CheckCircle
                    v-if="
                        orderToShow.payment_status.value ===
                        PaymentStatusEnum.PAID
                    "
                    class="h-4 w-4 text-success"
                />
                <Clock v-else class="h-4 w-4 text-warning" />
                <p
                    :class="
                        orderToShow.payment_status.value ===
                        PaymentStatusEnum.PAID
                            ? 'text-success'
                            : 'text-warning'
                    "
                    class="text-sm"
                >
                    {{
                        t(
                            `common.paymentStatus.${orderToShow.payment_status.key}`,
                        )
                    }}
                    <span
                        v-if="
                            orderToShow.payment_method.value ===
                            PaymentMethodEnum.CARD
                        "
                    >
                        {{ t('order.paymentSummaryCard.via') }}
                        {{
                            t(
                                `common.paymentMethod.${orderToShow.payment_method.key}`,
                            )
                        }}</span
                    >
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
