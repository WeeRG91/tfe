<script setup lang="ts">
import { OrderType } from '@/types/order';
import { formatDate, formatPrice } from '@/lib/utils';

defineProps<{
    orderToShow: OrderType;
}>();
</script>

<template>
    <div class="receipt">
        <div class="mb-2 text-center">
            <h2 class="text-lg font-bold">WEE RESTAURANT</h2>
            <p>Rue de Wee 123</p>
            <p>6000 Arlon, Belgium</p>
            <p>TVA: BE0123.456.789</p>
            <p>Tel: +32 123 45 67 89</p>
        </div>

        <div class="divider"></div>

        <div class="text-xs">
            <p>Order: #{{ orderToShow.order_number }}</p>
            <p>Date: {{ formatDate(orderToShow.created_at) }}</p>
            <p>Type: {{ orderToShow.type.label }}</p>
            <p v-if="orderToShow.table_number">
                Table: {{ orderToShow.table_number }}
            </p>
        </div>

        <div class="divider"></div>

        <div class="text-xs">
            <div v-for="item in orderToShow.items" :key="item.id" class="mb-1">
                <div class="flex justify-between">
                    <span>{{ item.quantity }} x {{ item.item?.name }}</span>
                    <span>€{{ formatPrice(item.total_inc_vat) }}</span>
                </div>
                <div v-if="item.meat">
                    &nbsp;+ {{ item.meat.name }} (€{{
                        formatPrice(item.meat.extra_price)
                    }})
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
                <span>VAT {{ vat.vat_rate }}%</span>
                <span>€{{ formatPrice(vat.vat_total) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Total VAT</span>
                <span>€{{ formatPrice(orderToShow.vat_total) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Subtotal</span>
                <span>€{{ formatPrice(orderToShow.subtotal) }}</span>
            </div>
            <div v-if="orderToShow.delivery_fee" class="flex justify-between">
                <span>Delivery</span>
                <span>+€{{ formatPrice(orderToShow.delivery_fee) }}</span>
            </div>
            <div v-if="orderToShow.discount_total" class="flex justify-between">
                <span>Discount</span>
                <span>-€{{ formatPrice(orderToShow.discount_total) }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="flex justify-between font-bold">
            <span>TOTAL</span>
            <span>€{{ formatPrice(orderToShow.total_inc_vat) }}</span>
        </div>

        <div class="divider"></div>

        <div class="mt-2 text-center text-xs">
            <p>Paid via Stripe</p>
            <p>Thank you for your visit</p>
        </div>
    </div>
</template>

<style scoped>
.divider {
    border-top: 1px dashed black;
    margin: 6px 0;
}
</style>
