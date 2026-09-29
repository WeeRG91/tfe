<script setup lang="ts">
import { useDateFormatter } from '@/composables/useDateFormatter';
import { getSpicyLevelLabels } from '@/lib/const';
import { formatPrice } from '@/lib/utils';
import { RestaurantDetails } from '@/types';
import { OrderType } from '@/types/order';
import { PaymentMethodEnum } from '@/types/payment';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

defineProps<{
    orderToShow: OrderType;
    restaurantDetails: RestaurantDetails;
}>();

const { t } = useI18n();
const spicyLevelLabels = computed(() => getSpicyLevelLabels(t));
const { formatDate } = useDateFormatter();
</script>

<template>
    <div class="receipt">
        <div class="mb-2 text-center">
            <h2 class="text-lg font-bold">{{ restaurantDetails.name }}</h2>
            <p>{{ restaurantDetails.address.street }}</p>
            <p>
                {{ restaurantDetails.address.postalCode }}
                {{ restaurantDetails.address.city }},
                {{ restaurantDetails.address.country }}
            </p>
            <p>{{ t('order.receipt.vatNumber') }}: BE0123.456.789</p>
            <p>
                {{ t('order.receipt.telephone') }}:
                {{ restaurantDetails.contactPhone }}
            </p>
        </div>

        <div class="divider"></div>

        <div class="text-xs">
            <p style="font-size: 15px; font-weight: bold">
                {{ orderToShow.order_number }}
            </p>
            <p>
                {{ t('order.receipt.date') }}:
                {{ formatDate(orderToShow.created_at) }}
            </p>
            <p>
                {{ t('order.receipt.type') }}:
                {{ t(`common.orderTypes.${orderToShow.type.key}`) }}
            </p>
            <p v-if="orderToShow.table_number">
                {{ t('order.receipt.table') }}: {{ orderToShow.table_number }}
            </p>
        </div>

        <div class="divider"></div>

        <div class="text-xs">
            <div v-for="item in orderToShow.items" :key="item.id" class="mb-1">
                <div class="flex justify-between">
                    <span>{{ item.quantity }} x {{ item.item?.name }}</span>
                    <span>€{{ formatPrice(item.total_inc_vat) }}</span>
                </div>
                <div v-if="item.spicy_level">
                    &nbsp;•
                    {{ spicyLevelLabels[item.spicy_level] }}
                </div>
                <div v-if="item.meat">
                    &nbsp;+ {{ item.meat.name }} (€{{ item.meat.extra_price }})
                </div>
                <div v-for="ing in item.removed_ingredients" :key="ing.id">
                    &nbsp;- {{ ing.name }}
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="text-xs">
            <div
                v-for="vat in orderToShow.vat_breakdown"
                :key="vat.vat_rate"
                class="flex justify-between"
            >
                <span>{{ t('order.receipt.vat') }} {{ vat.vat_rate }}%</span>
                <span>€{{ formatPrice(vat.vat_total) }}</span>
            </div>
            <div class="flex justify-between">
                <span>{{ t('order.receipt.totalVat') }}</span>
                <span>€{{ formatPrice(orderToShow.vat_total) }}</span>
            </div>
            <div class="flex justify-between">
                <span>{{ t('order.receipt.subtotal') }}</span>
                <span>€{{ formatPrice(orderToShow.subtotal) }}</span>
            </div>
            <div v-if="orderToShow.delivery_fee" class="flex justify-between">
                <span>{{ t('order.receipt.delivery') }}</span>
                <span>+€{{ formatPrice(orderToShow.delivery_fee) }}</span>
            </div>
            <div v-if="orderToShow.discount_total" class="flex justify-between">
                <span>{{ t('order.receipt.discount') }}</span>
                <span>-€{{ formatPrice(orderToShow.discount_total) }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="flex justify-between font-bold">
            <span>{{ t('order.receipt.total') }}</span>
            <span>€{{ formatPrice(orderToShow.total_inc_vat) }}</span>
        </div>

        <div class="divider"></div>

        <div class="mt-2 text-center text-xs">
            <p
                v-if="
                    orderToShow.payment_method.value === PaymentMethodEnum.CARD
                "
            >
                {{ t('order.receipt.paidBy.card') }}
            </p>
            <p
                v-if="
                    orderToShow.payment_method.value === PaymentMethodEnum.CASH
                "
            >
                {{ t('order.receipt.paidBy.cash') }}
            </p>
            <p>{{ t('order.receipt.thankYou') }}</p>
        </div>
    </div>
</template>

<style scoped>
.divider {
    border-top: 1px dashed black;
    margin: 6px 0;
}
</style>
