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
        class="rounded-lg border border-warning/30 bg-warning/10 p-6 text-foreground"
    >
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="mb-3 flex items-center gap-2">
                    <Sparkles class="h-5 w-5 text-warning" />
                    <h2 class="text-lg font-semibold text-foreground">
                        {{ t('cart.loyaltyPoints.title') }}
                    </h2>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-muted-foreground">
                        {{ t('cart.loyaltyPoints.youHave') }}
                        <span class="font-bold text-warning">{{
                            props.loyaltyPoints
                        }}</span>
                        {{ t('cart.loyaltyPoints.points') }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        ✨ {{ t('cart.loyaltyPoints.earnRate') }}
                    </p>
                </div>

                <div class="space-y-2">
                    <p class="text-sm font-medium text-foreground">
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
                                    ? 'bg-warning text-warning-foreground ring-2 ring-warning/40'
                                    : 'border border-warning/40 bg-background text-foreground hover:bg-warning/10',
                                !canUseOption(option.points) &&
                                props.selectedPoints !== option.points
                                    ? 'cursor-not-allowed opacity-50 hover:bg-background'
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
                        class="mt-2 rounded-md border border-warning/20 bg-warning/10 p-2"
                    >
                        <p class="text-xs text-muted-foreground">
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
                        class="mt-2 rounded-md border border-warning/20 bg-warning/10 p-2"
                    >
                        <p class="text-xs text-muted-foreground">
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
                        class="mt-3 flex items-center justify-between rounded-md border border-success/30 bg-success/10 p-3"
                    >
                        <div class="flex items-center gap-2">
                            <Gift class="h-4 w-4 text-success" />
                            <span class="text-sm text-foreground">
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
                            class="text-xs text-muted-foreground underline transition-colors hover:text-foreground"
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
