<script setup lang="ts">
import { Gift, Sparkles } from 'lucide-vue-next';
import { PointsOptions } from '@/composables/useLoyaltyPoints';

const props = defineProps<{
    loyaltyPoints: number;
    earnedPoints: number;
    pointsOptions: PointsOptions[];
    selectedPoints: number | null;
    discountAmount: number;
    canUseOption: (points: number) => boolean;
}>();

const emit = defineEmits<{
    (e: 'apply', points: number): void;
    (e: 'remove'): void;
}>();
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
                        Loyalty Rewards
                    </h2>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-amber-700">
                        You have
                        <span class="font-bold text-amber-900">{{
                            props.loyaltyPoints
                        }}</span>
                        loyalty points
                    </p>
                    <p class="mt-1 text-xs text-amber-600">
                        ✨ Earn 5 points per €1 spent on this order
                    </p>
                </div>

                <div class="space-y-2">
                    <p class="text-sm font-medium text-amber-800">
                        Redeem your points:
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
                                    ? `Need ${option.points} points, you have ${props.loyaltyPoints}`
                                    : ''
                            "
                        >
                            {{ option.label }}
                            <span
                                v-if="!canUseOption(option.points)"
                                class="ml-1 text-xs"
                            >
                                (Need {{ option.points - props.loyaltyPoints }} more)
                            </span>
                        </button>
                    </div>

                    <!-- Show message when user has points but not enough for any option -->
                    <div
                        v-if="props.loyaltyPoints > 0 && props.loyaltyPoints < 300"
                        class="mt-2 rounded-md bg-amber-100/50 p-2"
                    >
                        <p class="text-xs text-amber-700">
                            💡 You need {{ 300 - props.loyaltyPoints }} more points to
                            unlock your first discount! Complete this order to
                            earn {{ props.earnedPoints }} points.
                        </p>
                    </div>

                    <!-- Show info when user has 0 points -->
                    <div
                        v-if="props.loyaltyPoints === 0"
                        class="mt-2 rounded-md bg-amber-100/50 p-2"
                    >
                        <p class="text-xs text-amber-700">
                            💡 Start earning points with every order! You'll
                            earn
                            {{ props.earnedPoints }} points from this order.
                        </p>
                    </div>

                    <!-- Applied discount section -->
                    <div
                        v-if="props.selectedPoints"
                        class="mt-3 flex items-center justify-between rounded-md bg-amber-100 p-3"
                    >
                        <div class="flex items-center gap-2">
                            <Gift class="h-4 w-4 text-amber-600" />
                            <span class="text-sm text-amber-800">
                                €{{ props.discountAmount }} discount applied
                            </span>
                        </div>
                        <button
                            @click="emit('remove')"
                            class="text-xs text-amber-600 underline hover:text-amber-800"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
