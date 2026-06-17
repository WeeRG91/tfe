<script setup lang="ts">
import { formatDate } from '@/lib/utils';
import { LoyaltyPointTransactionType, PointTypeEnum } from '@/types/point';
import { Clock, LucideProps, Minus, Plus, X } from 'lucide-vue-next';
import { FunctionalComponent } from 'vue';

defineProps<{
    transaction: LoyaltyPointTransactionType;
}>();

const getPointIcon = (type: PointTypeEnum) => {
    const icon: Record<PointTypeEnum, FunctionalComponent<LucideProps>> = {
        [PointTypeEnum.EARNED]: Plus,
        [PointTypeEnum.REDEEMED]: Minus,
        [PointTypeEnum.REFUNDED]: X,
    };
    return icon[type] || Clock;
};

const getPointColor = (type: PointTypeEnum) => {
    const color: Record<PointTypeEnum, string> = {
        [PointTypeEnum.EARNED]: 'text-green-600',
        [PointTypeEnum.REDEEMED]: 'text-red-600',
        [PointTypeEnum.REFUNDED]: 'text-yellow-600',
    };
    return color[type] || 'text-gray-600';
};

const getPointBgColor = (type: PointTypeEnum) => {
    const bgColor: Record<PointTypeEnum, string> = {
        [PointTypeEnum.EARNED]: 'bg-green-100',
        [PointTypeEnum.REDEEMED]: 'bg-red-100',
        [PointTypeEnum.REFUNDED]: 'bg-yellow-100',
    };
    return bgColor[type] || 'bg-gray-100';
};

const getPointPrefix = (type: PointTypeEnum) => {
    const prefix: Record<PointTypeEnum, string> = {
        [PointTypeEnum.EARNED]: '+',
        [PointTypeEnum.REDEEMED]: '-',
        [PointTypeEnum.REFUNDED]: '+',
    };
    return prefix[type] || '';
};

const getTypeLabel = (type: PointTypeEnum) => {
    return type.charAt(0) + type.slice(1).toLowerCase();
};
</script>

<template>
    <div
        class="flex items-center justify-between rounded-xl border border-gray-100 bg-white p-3 shadow-sm transition-all duration-200 active:scale-[0.99] md:p-4 md:hover:shadow-md"
        role="listitem"
    >
        <!-- Left Section -->
        <div class="flex min-w-0 flex-1 items-center gap-3 md:gap-4">
            <!-- Icon -->
            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full md:h-10 md:w-10"
                :class="getPointBgColor(transaction.type as PointTypeEnum)"
            >
                <component
                    :is="getPointIcon(transaction.type as PointTypeEnum)"
                    class="h-4 w-4 md:h-5 md:w-5"
                    :class="getPointColor(transaction.type as PointTypeEnum)"
                />
            </div>

            <!-- Content -->
            <div class="min-w-0 flex-1">
                <p
                    class="truncate text-sm font-medium text-gray-900 md:text-base"
                >
                    {{
                        transaction.description ||
                        getTypeLabel(transaction.type as PointTypeEnum)
                    }}
                </p>
                <div
                    class="flex items-center gap-1.5 text-xs text-gray-500 md:gap-2"
                >
                    <Clock class="h-3 w-3 shrink-0" />
                    <span class="truncate">{{
                        formatDate(transaction.created_at)
                    }}</span>
                </div>
            </div>
        </div>

        <!-- Right Section - Points -->
        <div class="ml-2 shrink-0 text-right md:ml-4">
            <p
                class="text-base font-bold md:text-lg"
                :class="getPointColor(transaction.type as PointTypeEnum)"
            >
                {{ getPointPrefix(transaction.type as PointTypeEnum)
                }}{{ transaction.points }}
            </p>
            <p class="hidden text-[10px] text-gray-400 md:block md:text-xs">
                {{ getTypeLabel(transaction.type as PointTypeEnum) }}
            </p>
        </div>
    </div>
</template>

<style scoped>
/* Add smooth touch feedback */
@media (hover: none) {
    .active\:scale-\[0\.99\] {
        transition: transform 0.15s ease;
    }
}
</style>
