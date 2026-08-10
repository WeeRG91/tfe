<script setup lang="ts">
import { PointOption } from '@/composables/useLoyaltyPoints';
import { Gift, Sparkles } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    loyaltyPoints: number;
    earnedPoints: number;
    pointsOptions: PointOption[];
    selectedPoints: number | null;
    discountAmount: number;
    canUseOption: (points: number) => boolean;
}>();

const emit = defineEmits<{
    apply: [points: number];
    remove: [];
}>();

const { t } = useI18n();
</script>

<template>
    <div
        class="rounded-lg border bg-gradient-to-r from-amber-50 to-yellow-50 p-6"
    >
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="mb-3 flex items-center gap-2">
                    <Sparkles class="h-5 w-5 text-amber-500" />
                    <h2 class="text-lg font-semibold text-amber-800">
                        {{ t('cart.loyaltyPoints.title') }}
                    </h2>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-amber-700">
                        {{ t('cart.loyaltyPoints.youHave') }}
                        <span class="font-bold text-amber-900">{{
                            props.loyaltyPoints
                        }}</span>
                        {{ t('cart.loyaltyPoints.points') }}
                    </p>
                    <p class="mt-1 text-xs text-amber-600">
                        ✨ {{ t('cart.loyaltyPoints.earnRate') }}
                    </p>
                </div>

                <div class="space-y-2">
                    <p class="text-sm font-medium text-amber-800">
                        {{ t('cart.loyaltyPoints.redeemTitle') }}
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <button
                            v-for="option in props.pointsOptions"
                            :key="option.points"
                            @click="emit('apply', option.points)"
                            :disabled="!canUseOption(option.points)"
                            :class="[
                                'rounded-lg px-4 py-2 text-sm font-medium transition-all',
                                props.selectedPoints === option.points
                                    ? 'bg-amber-600 text-white ring-2 ring-amber-400'
                                    : 'border border-amber-300 bg-white text-amber-700 hover:bg-amber-100',
                                !canUseOption(option.points) &&
                                props.selectedPoints !== option.points
                                    ? 'cursor-not-allowed opacity-50 hover:bg-white'
                                    : '',
                            ]"
                            :title="
                                !canUseOption(option.points)
                                    ? t('loyaltyPoints.tooltipNeed', {
                                          required: option.points,
                                          current: props.loyaltyPoints,
                                      })
                                    : ''
                            "
                        >
                            {{ option.label }}
                            <span
                                v-if="!canUseOption(option.points)"
                                class="ml-1 text-xs"
                            >
                                {{
                                    t(
                                        'cart.loyaltyPoints.needMore',
                                        option.points - props.loyaltyPoints,
                                    )
                                }}
                            </span>
                        </button>
                    </div>
                    <div
                        v-if="
                            props.loyaltyPoints > 0 && props.loyaltyPoints < 300
                        "
                        class="mt-2 rounded-md bg-amber-100/50 p-2"
                    >
                        <p class="text-xs text-amber-700">
                            💡
                            {{
                                t('cart.loyaltyPoints.firstDiscount', {
                                    count: 300 - props.loyaltyPoints,
                                    earned: props.earnedPoints,
                                })
                            }}
                        </p>
                    </div>
                    <div
                        v-if="props.loyaltyPoints === 0"
                        class="mt-2 rounded-md bg-amber-100/50 p-2"
                    >
                        <p class="text-xs text-amber-700">
                            💡
                            {{
                                t(
                                    'cart.loyaltyPoints.startEarning',
                                    props.earnedPoints,
                                )
                            }}
                        </p>
                    </div>

                    <div
                        v-if="props.selectedPoints"
                        class="mt-3 flex items-center justify-between rounded-md bg-amber-100 p-3"
                    >
                        <div class="flex items-center gap-2">
                            <Gift class="h-4 w-4 text-amber-600" />
                            <span class="text-sm text-amber-800">
                                €{{
                                    t(
                                        'cart.loyaltyPoints.discountApplied',
                                        props.discountAmount,
                                    )
                                }}
                            </span>
                        </div>
                        <button
                            @click="emit('remove')"
                            class="text-xs text-amber-600 underline hover:text-amber-800"
                        >
                            {{ t('cart.loyaltyPoints.remove') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
