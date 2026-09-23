<script setup lang="ts">
import { useDateFormatter } from '@/composables/useDateFormatter';
import { formatAddress, getOrderTypeIcon } from '@/lib/utils';
import { OrderType, OrderTypeEnum } from '@/types/order';
import { PaymentMethodEnum } from '@/types/payment';
import {
    Coins,
    CreditCard,
    FileText,
    MapPin,
    User,
    Building2,
} from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { DeliveryTypeEnum } from '@/types/delivery';

defineProps<{
    orderToShow: OrderType;
}>();

const { t } = useI18n();
const { formatDate, formatDateOnly } = useDateFormatter();
</script>

<template>
    <div
        class="rounded-lg border border-border bg-card p-6 text-card-foreground"
    >
        <h2
            class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
        >
            <component
                :is="getOrderTypeIcon(orderToShow.type.value)"
                class="h-5 w-5 text-primary"
            />
            {{ t('order.orderDetailsCard.title') }}
        </h2>

        <div class="space-y-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 rounded-lg bg-primary/10 p-2">
                    <component
                        :is="getOrderTypeIcon(orderToShow.type.value)"
                        class="h-5 w-5 text-primary"
                    />
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        {{ t('order.orderDetailsCard.orderType') }}
                    </p>
                    <p class="font-medium text-card-foreground">
                        {{ t(`common.orderTypes.${orderToShow.type.key}`) }}
                    </p>
                    <p
                        v-if="
                            orderToShow.type.value === OrderTypeEnum.DINEIN &&
                            orderToShow.table_number
                        "
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        {{ t('order.orderDetailsCard.table') }}:
                        {{ orderToShow.table_number }}
                    </p>
                    <p
                        v-if="
                            orderToShow.type.label === 'Takeaway' &&
                            orderToShow.pickup_time
                        "
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        {{ t('order.orderDetailsCard.pickupTime') }}:
                        {{ formatDate(orderToShow.pickup_time) }}
                    </p>
                </div>
            </div>

            <div
                v-if="orderToShow.type.value === OrderTypeEnum.TAKEAWAY"
                class="flex items-start gap-3"
            >
                <div class="flex-shrink-0 rounded-lg bg-primary/10 p-2">
                    <User class="h-5 w-5 text-primary" />
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        {{ t('order.orderDetailsCard.pickupInformation') }}
                    </p>
                    <p class="font-medium text-card-foreground">
                        {{
                            orderToShow.pickup_name ||
                            t('order.orderDetailsCard.notAvailable')
                        }}
                    </p>
                    <p
                        v-if="orderToShow.pickup_phone"
                        class="text-sm text-muted-foreground"
                    >
                        {{ orderToShow.pickup_phone }}
                    </p>
                </div>
            </div>

            <div
                v-if="
                    orderToShow.delivery_address &&
                    orderToShow.delivery_type !== DeliveryTypeEnum.COMPANY
                "
                class="flex items-start gap-3"
            >
                <div class="flex-shrink-0 rounded-lg bg-primary/10 p-2">
                    <MapPin class="h-5 w-5 text-primary" />
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        {{ t('order.orderDetailsCard.deliveryAddress') }}
                    </p>
                    <p class="font-medium text-card-foreground">
                        {{ orderToShow.delivery_address.first_name }}
                        {{ orderToShow.delivery_address.last_name }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ orderToShow.delivery_address.phone }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ formatAddress(orderToShow.delivery_address) }}
                    </p>
                </div>
            </div>

            <div
                v-if="
                    orderToShow.delivery_type === DeliveryTypeEnum.COMPANY &&
                    orderToShow.delivery_company
                "
                class="flex items-start gap-3"
            >
                <div class="flex-shrink-0 rounded-lg bg-primary/10 p-2">
                    <Building2 class="h-5 w-5 text-primary" />
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">
                        {{ t('order.orderDetailsCard.deliveryCompany') }}
                    </p>

                    <p class="font-medium text-card-foreground">
                        {{ orderToShow.delivery_company.name }}
                    </p>

                    <p
                        v-if="orderToShow.delivery_date"
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        {{ t('order.orderDetailsCard.deliveryDate') }}
                    </p>

                    <p class="font-medium text-card-foreground">
                        {{ formatDateOnly(orderToShow.delivery_date) }}
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 rounded-lg bg-primary/10 p-2">
                    <Coins
                        v-if="
                            orderToShow.payment_method?.value ===
                            PaymentMethodEnum.CASH
                        "
                        class="h-5 w-5 text-primary"
                    />
                    <CreditCard v-else class="h-5 w-5 text-primary" />
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        {{ t('order.orderDetailsCard.paymentMethod') }}
                    </p>
                    <p class="font-medium text-card-foreground">
                        {{
                            t(
                                `common.paymentMethod.${orderToShow.payment_method.key}`,
                            ) || t('order.orderDetailsCard.notAvailable')
                        }}
                    </p>
                    <p
                        v-if="orderToShow.paid_at"
                        class="text-sm text-success"
                    >
                        {{ t('order.orderDetailsCard.paidOn') }}
                        {{ formatDate(orderToShow.paid_at) }}
                    </p>
                </div>
            </div>

            <div v-if="orderToShow.notes" class="flex items-start gap-3">
                <div class="flex-shrink-0 rounded-lg bg-primary/10 p-2">
                    <FileText class="h-5 w-5 text-primary" />
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        {{ t('order.orderDetailsCard.orderNotes') }}
                    </p>
                    <p class="text-sm text-muted-foreground">{{ orderToShow.notes }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
