<script setup lang="ts">
import { formatPrice } from '@/lib/utils';
import { CartItemType } from '@/types/cart';
import { OrderItemType } from '@/types/order';
import { Soup } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';
import { getSpicyLevelLabels } from '@/lib/const';

defineProps<{
    items: CartItemType[] | OrderItemType[];
    subtotal: number;
    deliveryFee: number;
    discountAmount: number;
    vat12Total: number;
    vat21Total: number;
    totalVat: number;
    totalIncVat: number;
    earnedPoints: number;
}>();

const { t } = useI18n();
const spicyLevelLabels = computed(() => getSpicyLevelLabels(t));
</script>

<template>
    <div class="rounded-lg border bg-white p-6">
        <h2
            class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
        >
            <Soup class="h-5 w-5" />
            {{ t('cart.orderSummary.title') }}
        </h2>

        <div class="space-y-3">
            <div
                v-for="item in items"
                :key="item.id"
                class="border-b border-gray-100 pb-2 last:border-0"
            >
                <div class="flex justify-between text-sm">
                    <div class="flex-1">
                        <div class="font-medium">
                            {{ item.quantity }}x {{ item.item.name }}
                        </div>
                        <div v-if="item.spicy_level" class="mt-1 text-xs text-gray-600">
                            <span class="font-medium">{{ t('common.spicyLevel.title') }}:</span>
                            <span class="ml-0.5 text-gray-500">
                                {{ spicyLevelLabels[item.spicy_level] }}
                            </span>
                        </div>
                        <div
                            v-if="item.meat"
                            class="mt-1 text-xs text-gray-600"
                        >
                            <span class="font-medium"
                                >{{ t('cart.orderSummary.meat') }}:</span
                            >
                            {{ item.meat.name }}
                            <span
                                v-if="item.meat.extra_price > 0"
                                class="text-gray-500"
                            >
                                (+€{{ formatPrice(item.meat.extra_price) }})
                            </span>
                        </div>
                        <div
                            v-if="item.removed_ingredients?.length > 0"
                            class="mt-1 text-xs text-gray-500"
                        >
                            <span class="font-medium text-gray-600"
                                >{{ t('cart.orderSummary.without') }}:</span
                            >
                            <span class="ml-1">
                                {{
                                    item.removed_ingredients
                                        .map((ing) => ing.name)
                                        .join(', ')
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="ml-4 font-medium whitespace-nowrap">
                        €{{ formatPrice(item.total_inc_vat) }}
                    </div>
                </div>
            </div>

            <div class="mt-3 space-y-1 border-t pt-3">
                <div
                    v-if="vat12Total"
                    class="flex justify-between text-sm text-gray-600"
                >
                    <span>{{ t('cart.orderSummary.vat12') }}</span>
                    <span>€{{ formatPrice(vat12Total) }}</span>
                </div>
                <div
                    v-if="vat21Total"
                    class="flex justify-between text-sm text-gray-600"
                >
                    <span>{{ t('cart.orderSummary.vat21') }}</span>
                    <span>€{{ formatPrice(vat21Total) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600">
                    <span>{{ t('cart.orderSummary.totalVat') }}</span>
                    <span>€{{ formatPrice(totalVat) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600">
                    <span>{{ t('cart.orderSummary.subtotal') }}</span>
                    <span>€{{ formatPrice(subtotal) }}</span>
                </div>
                <div
                    v-if="deliveryFee > 0"
                    class="flex justify-between border-t border-red-100 pt-2 text-sm text-red-600"
                >
                    <span>{{ t('cart.orderSummary.deliveryFee') }}</span>
                    <span>+€{{ formatPrice(deliveryFee) }}</span>
                </div>
                <div
                    v-if="discountAmount > 0"
                    class="flex justify-between border-t border-green-100 pt-2 text-sm text-green-600"
                >
                    <span>{{ t('cart.orderSummary.loyaltyDiscount') }}</span>
                    <span>-€{{ formatPrice(discountAmount) }}</span>
                </div>
                <div class="flex justify-between pt-2 text-base font-semibold">
                    <span>{{ t('cart.orderSummary.total') }}</span>
                    <span class="text-red-500"
                        >€{{ formatPrice(totalIncVat) }}</span
                    >
                </div>
                <div
                    class="mt-2 flex justify-between border-t border-gray-100 pt-2 text-xs text-amber-600"
                >
                    <span>🌟 {{ t('cart.orderSummary.pointsToEarn') }}</span>
                    <span class="font-medium"
                        >
                        {{ t('cart.orderSummary.points', earnedPoints) }}</span
                    >
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
