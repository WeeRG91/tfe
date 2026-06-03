<script setup lang="ts">
import { Utensils, Coffee, Soup } from 'lucide-vue-next';
import { formatPrice } from '@/lib/utils';
import { OrderType } from '@/types/order';
import { ItemTypeEnum } from '@/types/cart';

defineProps<{
    order: OrderType;
}>();

const getItemTypeIcon = (itemType: number) => {
    return itemType === ItemTypeEnum.DISH ? Utensils : Coffee;
};

const getItemTypeColor = (itemType: number) => {
    return itemType === ItemTypeEnum.DISH ? 'text-red-500' : 'text-blue-500';
};
</script>

<template>
    <div class="rounded-lg bg-white p-3">
        <div class="mb-3 flex items-center gap-2 border-b border-gray-100 pb-2">
            <Soup class="h-4 w-4 text-red-500" />
            <h3
                class="text-sm font-semibold tracking-wide text-gray-700 uppercase"
            >
                Order Items
            </h3>
            <span class="text-xs text-gray-500"
                >{{ order.items?.length || 0 }} items</span
            >
        </div>

        <div class="max-h-[400px] space-y-3 overflow-y-auto">
            <div
                v-for="item in order.items"
                :key="item.id"
                class="flex flex-col gap-2 border-b border-gray-100 pb-3 last:border-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex gap-3">
                    <div
                        class="h-14 w-14 shrink-0 overflow-hidden rounded-lg border bg-gray-50"
                    >
                        <img
                            :src="item.item?.main_image"
                            :alt="item.item?.name"
                            class="h-full w-full object-cover"
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <component
                                :is="getItemTypeIcon(item.item_type)"
                                :class="getItemTypeColor(item.item_type)"
                                class="h-3.5 w-3.5"
                            />
                            <p class="font-medium text-gray-800">
                                {{ item.item?.name }}
                            </p>
                            <span class="text-sm font-semibold text-red-500"
                                >×{{ item.quantity }}</span
                            >
                        </div>
                        <p class="text-xs text-gray-500">
                            €{{ formatPrice(item.unit_price) }} each
                        </p>

                        <!-- Customizations -->
                        <div
                            v-if="item.item_type === ItemTypeEnum.DISH"
                            class="mt-1 space-y-0.5 text-xs"
                        >
                            <p
                                v-if="item.meat"
                                class="flex items-center gap-1 text-gray-600"
                            >
                                <span class="text-red-500">🥩</span>
                                <span>Meat:</span>
                                <span class="font-medium">{{
                                    item.meat.name
                                }}</span>
                                <span
                                    v-if="item.meat.extra_price > 0"
                                    class="text-red-500"
                                    >(+€{{
                                        formatPrice(item.meat.extra_price)
                                    }})</span
                                >
                            </p>
                            <p
                                v-if="item.removed_ingredients?.length"
                                class="flex flex-wrap items-center gap-1 text-gray-500"
                            >
                                <span>🚫 Removed:</span>
                                <span>{{
                                    item.removed_ingredients
                                        .map((i) => i.name)
                                        .join(', ')
                                }}</span>
                            </p>
                            <p
                                v-if="item.notes"
                                class="flex items-start gap-1 text-gray-500 italic"
                            >
                                <span>📝</span>
                                <span>{{ item.notes }}</span>
                            </p>
                        </div>
                        <div
                            v-else-if="item.notes"
                            class="mt-1 flex items-start gap-1 text-xs text-gray-500 italic"
                        >
                            <span>📝</span>
                            <span>{{ item.notes }}</span>
                        </div>
                    </div>
                </div>
                <div class="shrink-0 text-right">
                    <p class="text-base font-semibold text-gray-800">
                        €{{ formatPrice(item.total_inc_vat) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-lg bg-white p-3">
        <div class="space-y-1.5 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-medium"
                    >€{{ formatPrice(order.subtotal) }}</span
                >
            </div>
            <div v-if="order.delivery_fee > 0" class="flex justify-between">
                <span class="text-gray-600">Delivery Fee</span>
                <span class="font-medium"
                    >€{{ formatPrice(order.delivery_fee) }}</span
                >
            </div>
            <div
                v-if="order.discount_total > 0"
                class="flex justify-between text-emerald-600"
            >
                <span class="font-semibold"
                    >-€{{ formatPrice(order.discount_total) }}</span
                >
            </div>
            <div
                class="flex justify-between border-t border-gray-200 pt-2 text-base font-bold"
            >
                <span>Total</span>
                <span class="text-red-500"
                    >€{{ formatPrice(order.total_inc_vat) }}</span
                >
            </div>
        </div>
    </div>
</template>
<style scoped></style>
