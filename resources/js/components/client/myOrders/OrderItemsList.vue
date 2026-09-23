<script setup lang="ts">
import { getSpicyLevelLabels } from '@/lib/const';
import { formatPrice } from '@/lib/utils';
import { ItemTypeEnum } from '@/types/cart';
import { OrderType } from '@/types/order';
import { Coffee, Soup, Utensils } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

defineProps<{
    order: OrderType;
}>();

const { t } = useI18n();
const spicyLevelLabels = computed(() => getSpicyLevelLabels(t));

const getItemTypeIcon = (itemType: number) => {
    return itemType === ItemTypeEnum.DISH ? Utensils : Coffee;
};

const getItemTypeColor = (itemType: number) => {
    return itemType === ItemTypeEnum.DISH ? 'text-primary' : 'text-info';
};
</script>

<template>
    <div
        class="rounded-lg border border-border bg-card p-3 text-card-foreground"
    >
        <div class="mb-3 flex items-center gap-2 border-b border-border pb-2">
            <Soup class="h-4 w-4 text-primary" />
            <h3
                class="text-sm font-semibold tracking-wide text-card-foreground uppercase"
            >
                {{ t('order.orderItemsList.title') }}
            </h3>
            <span class="text-xs text-muted-foreground">{{
                t('order.orderItemsList.itemCount', order.items?.length || 0)
            }}</span>
        </div>

        <div class="max-h-[400px] space-y-3 overflow-y-auto">
            <div
                v-for="item in order.items"
                :key="item.id"
                class="flex flex-col gap-2 border-b border-border pb-3 last:border-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex gap-3">
                    <div
                        class="h-14 w-14 shrink-0 overflow-hidden rounded-lg border border-border bg-muted"
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
                            <p class="font-medium text-card-foreground">
                                {{ item.item?.name }}
                            </p>
                            <span class="text-sm font-semibold text-primary"
                                >×{{ item.quantity }}</span
                            >
                        </div>
                        <p class="text-xs text-muted-foreground">
                            €{{ formatPrice(item.unit_price) }}
                            {{ t('order.orderItemsList.each') }}
                        </p>

                        <div
                            v-if="item.item_type === ItemTypeEnum.DISH"
                            class="mt-1 space-y-0.5 text-xs"
                        >
                            <p
                                class="flex items-center gap-1 text-muted-foreground"
                            >
                                <span>🌶️</span>
                                <span>{{ t('common.spicyLevel.title') }}:</span>
                                <span class="font-medium">
                                    {{ spicyLevelLabels[item.spicy_level] }}
                                </span>
                            </p>
                            <p
                                v-if="item.meat"
                                class="flex items-center gap-1 text-muted-foreground"
                            >
                                <span>🥩</span>
                                <span
                                    >{{ t('order.orderItemsList.meat') }}:</span
                                >
                                <span class="font-medium">{{
                                    item.meat.name
                                }}</span>
                                <span
                                    v-if="item.meat.extra_price > 0"
                                    class="text-primary"
                                    >(+€{{
                                        formatPrice(item.meat.extra_price)
                                    }})</span
                                >
                            </p>
                            <p
                                v-if="item.removed_ingredients?.length"
                                class="flex flex-wrap items-center gap-1 text-muted-foreground"
                            >
                                <span
                                    >🚫
                                    {{
                                        t('order.orderItemsList.removed')
                                    }}:</span
                                >
                                <span>{{
                                    item.removed_ingredients
                                        .map((i) => i.name)
                                        .join(', ')
                                }}</span>
                            </p>
                            <p
                                v-if="item.notes"
                                class="flex items-start gap-1 text-muted-foreground italic"
                            >
                                <span>📝</span>
                                <span>{{ item.notes }}</span>
                            </p>
                        </div>
                        <div
                            v-else-if="item.notes"
                            class="mt-1 flex items-start gap-1 text-xs text-muted-foreground italic"
                        >
                            <span>📝</span>
                            <span>{{ item.notes }}</span>
                        </div>
                    </div>
                </div>
                <div class="shrink-0 text-right">
                    <p class="text-base font-semibold text-card-foreground">
                        €{{ formatPrice(item.total_inc_vat) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div
        class="rounded-lg border border-border bg-card p-3 text-card-foreground"
    >
        <div class="space-y-1.5 text-sm">
            <div class="flex justify-between">
                <span class="text-muted-foreground">{{
                    t('order.orderItemsList.subtotal')
                }}</span>
                <span class="font-medium"
                    >€{{ formatPrice(order.subtotal) }}</span
                >
            </div>
            <div v-if="order.delivery_fee > 0" class="flex justify-between">
                <span class="text-muted-foreground">{{
                    t('order.orderItemsList.deliveryFee')
                }}</span>
                <span class="font-medium"
                    >€{{ formatPrice(order.delivery_fee) }}</span
                >
            </div>
            <div
                v-if="order.discount_total > 0"
                class="flex justify-between text-success"
            >
                <span class="font-semibold"
                    >-€{{ formatPrice(order.discount_total) }}</span
                >
            </div>
            <div
                class="flex justify-between border-t border-border pt-2 text-base font-bold"
            >
                <span>{{ t('order.orderItemsList.total') }}</span>
                <span class="text-primary"
                    >€{{ formatPrice(order.total_inc_vat) }}</span
                >
            </div>
        </div>
    </div>
</template>
<style scoped></style>
