<script setup lang="ts">
import { formatPrice } from '@/lib/utils';
import { OrderType } from '@/types/order';
import { Soup } from 'lucide-vue-next';

defineProps<{
    orderToShow: OrderType;
}>();
</script>

<template>
    <div class="rounded-lg border bg-white p-6">
        <h2
            class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
        >
            <Soup class="h-5 w-5 text-red-500" />
            Order Items
        </h2>

        <div class="space-y-3">
            <div
                v-for="item in orderToShow.items"
                :key="item.id"
                class="border-b border-gray-100 pb-3 last:border-0"
            >
                <div class="flex justify-between text-sm">
                    <div class="flex-1">
                        <div class="font-medium">
                            {{ item.quantity }}x {{ item.item?.name }}
                        </div>
                        <div
                            class="mt-1 text-xs text-gray-500"
                        >
                            Spicy level:
                            {{
                                ['No spicy', 'Mild', 'Spicy', 'Hot'][
                                    item.spicy_level
                                ]
                            }}
                        </div>
                        <div
                            v-if="item.meat"
                            class="mt-1 text-xs text-gray-500"
                        >
                            Meat: {{ item.meat.name }}
                            <span
                                v-if="item.meat.extra_price > 0"
                                class="text-gray-400"
                            >
                                (+€{{ formatPrice(item.meat.extra_price) }})
                            </span>
                        </div>
                        <div
                            v-if="
                                item.removed_ingredients &&
                                item.removed_ingredients.length > 0
                            "
                            class="mt-1 text-xs text-gray-400"
                        >
                            Without:
                            {{
                                item.removed_ingredients
                                    .map((ing) => ing.name)
                                    .join(', ')
                            }}
                        </div>
                        <div
                            v-if="item.notes"
                            class="mt-1 text-xs text-gray-400"
                        >
                            Note: {{ item.notes }}
                        </div>
                    </div>
                    <div class="ml-4 font-medium whitespace-nowrap">
                        €{{ formatPrice(item.total_inc_vat) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
