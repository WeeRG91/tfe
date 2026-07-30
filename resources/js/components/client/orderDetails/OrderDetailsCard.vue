<script setup lang="ts">
import { formatAddress, getOrderTypeIcon } from '@/lib/utils';
import { OrderType } from '@/types/order';
import { Coins, CreditCard, FileText, MapPin, User } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { PaymentMethodEnum } from '@/types/payment';
import { useDateFormatter } from '@/composables/useDateFormatter';

defineProps<{
    orderToShow: OrderType;
}>();

const { t } = useI18n();
const { formatDate } = useDateFormatter();
</script>

<template>
    <div class="rounded-lg border bg-white p-6">
        <h2
            class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
        >
            <component
                :is="getOrderTypeIcon(orderToShow.type.value)"
                class="h-5 w-5 text-red-500"
            />
            {{ t('order.orderDetailsCard.title') }}
        </h2>

        <div class="space-y-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 rounded-lg bg-red-50 p-2">
                    <component
                        :is="getOrderTypeIcon(orderToShow.type.value)"
                        class="h-5 w-5 text-red-500"
                    />
                </div>
                <div>
                    <p class="text-sm text-gray-500">
                        {{ t('order.orderDetailsCard.orderType') }}
                    </p>
                    <p class="font-medium text-gray-800">
                        {{ t(`common.orderTypes.${orderToShow.type.key}`) }}
                    </p>
                    <p
                        v-if="
                            orderToShow.type.label === 'Dine-in' &&
                            orderToShow.table_number
                        "
                        class="mt-1 text-sm text-gray-600"
                    >
                        {{ t('order.orderDetailsCard.table') }}:
                        {{ orderToShow.table_number }}
                    </p>
                    <p
                        v-if="
                            orderToShow.type.label === 'Takeaway' &&
                            orderToShow.pickup_time
                        "
                        class="mt-1 text-sm text-gray-600"
                    >
                        {{ t('order.orderDetailsCard.pickupTime') }}:
                        {{ formatDate(orderToShow.pickup_time) }}
                    </p>
                </div>
            </div>

            <div
                v-if="orderToShow.type.label === 'Takeaway'"
                class="flex items-start gap-3"
            >
                <div class="flex-shrink-0 rounded-lg bg-red-50 p-2">
                    <User class="h-5 w-5 text-red-500" />
                </div>
                <div>
                    <p class="text-sm text-gray-500">
                        {{ t('order.orderDetailsCard.pickupInformation') }}
                    </p>
                    <p class="font-medium text-gray-800">
                        {{
                            orderToShow.pickup_name ||
                            t('order.orderDetailsCard.notAvailable')
                        }}
                    </p>
                    <p
                        v-if="orderToShow.pickup_phone"
                        class="text-sm text-gray-600"
                    >
                        {{ orderToShow.pickup_phone }}
                    </p>
                </div>
            </div>

            <div
                v-if="
                    orderToShow.type.label === 'Delivery' &&
                    orderToShow.delivery_address
                "
                class="flex items-start gap-3"
            >
                <div class="flex-shrink-0 rounded-lg bg-red-50 p-2">
                    <MapPin class="h-5 w-5 text-red-500" />
                </div>
                <div>
                    <p class="text-sm text-gray-500">
                        {{ t('order.orderDetailsCard.deliveryAddress') }}
                    </p>
                    <p class="font-medium text-gray-800">
                        {{ orderToShow.delivery_address.first_name }}
                        {{ orderToShow.delivery_address.last_name }}
                    </p>
                    <p class="text-sm text-gray-600">
                        {{ orderToShow.delivery_address.phone }}
                    </p>
                    <p class="text-sm text-gray-600">
                        {{ formatAddress(orderToShow.delivery_address) }}
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 rounded-lg bg-red-50 p-2">
                    <Coins
                        v-if="
                            orderToShow.payment_method?.value ===
                            PaymentMethodEnum.CASH
                        "
                        class="h-5 w-5 text-red-500"
                    />
                    <CreditCard v-else class="h-5 w-5 text-red-500" />
                </div>
                <div>
                    <p class="text-sm text-gray-500">
                        {{ t('order.orderDetailsCard.paymentMethod') }}
                    </p>
                    <p class="font-medium text-gray-800">
                        {{
                            t(
                                `common.paymentMethod.${orderToShow.payment_method.key}`,
                            ) || t('order.orderDetailsCard.notAvailable')
                        }}
                    </p>
                    <p
                        v-if="orderToShow.paid_at"
                        class="text-sm text-green-600"
                    >
                        {{ t('order.orderDetailsCard.paidOn') }}
                        {{ formatDate(orderToShow.paid_at) }}
                    </p>
                </div>
            </div>

            <div v-if="orderToShow.notes" class="flex items-start gap-3">
                <div class="flex-shrink-0 rounded-lg bg-red-50 p-2">
                    <FileText class="h-5 w-5 text-red-500" />
                </div>
                <div>
                    <p class="text-sm text-gray-500">
                        {{ t('order.orderDetailsCard.orderNotes') }}
                    </p>
                    <p class="text-sm text-gray-700">{{ orderToShow.notes }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
