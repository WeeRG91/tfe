<script setup lang="ts">
import { LoyaltyPointTransactionType, PointTypeEnum } from '@/types/point';
import { Clock, LucideProps, Minus, Plus, X, RefreshCw } from 'lucide-vue-next';
import { FunctionalComponent } from 'vue';
import { useI18n } from 'vue-i18n';
import { useDateFormatter } from '@/composables/useDateFormatter';

defineProps<{
    transaction: LoyaltyPointTransactionType;
}>();

const { t } = useI18n();

const { formatDate } = useDateFormatter();

const getPointIcon = (type: PointTypeEnum) => {
    const icon: Record<PointTypeEnum, FunctionalComponent<LucideProps>> = {
        [PointTypeEnum.EARNED]: Plus,
        [PointTypeEnum.REDEEMED]: Minus,
        [PointTypeEnum.REFUNDED]: X,
        [PointTypeEnum.REVERSED]: RefreshCw,
    };
    return icon[type] || Clock;
};

const getPointColor = (type: PointTypeEnum) => {
    const color: Record<PointTypeEnum, string> = {
        [PointTypeEnum.EARNED]: 'text-green-600',
        [PointTypeEnum.REDEEMED]: 'text-red-600',
        [PointTypeEnum.REFUNDED]: 'text-yellow-600',
        [PointTypeEnum.REVERSED]: 'text-orange-600',
    };
    return color[type] || 'text-gray-600';
};

const getPointBgColor = (type: PointTypeEnum) => {
    const bgColor: Record<PointTypeEnum, string> = {
        [PointTypeEnum.EARNED]: 'bg-green-100',
        [PointTypeEnum.REDEEMED]: 'bg-red-100',
        [PointTypeEnum.REFUNDED]: 'bg-yellow-100',
        [PointTypeEnum.REVERSED]: 'bg-orange-100',
    };
    return bgColor[type] || 'bg-gray-100';
};

const getPointPrefix = (type: PointTypeEnum) => {
    const prefix: Record<PointTypeEnum, string> = {
        [PointTypeEnum.EARNED]: '+',
        [PointTypeEnum.REDEEMED]: '-',
        [PointTypeEnum.REFUNDED]: '+',
        [PointTypeEnum.REVERSED]: '-',
    };
    return prefix[type] || '';
};
</script>

<template>
    <div
        class="flex items-center justify-between rounded-xl border border-gray-100 bg-white p-3 shadow-sm transition-all duration-200 active:scale-[0.99] md:p-4 md:hover:shadow-md"
        role="listitem"
    >
        <div class="flex min-w-0 flex-1 items-center gap-3 md:gap-4">
            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full md:h-10 md:w-10"
                :class="
                    getPointBgColor(transaction.type.label as PointTypeEnum)
                "
            >
                <component
                    :is="getPointIcon(transaction.type.label as PointTypeEnum)"
                    class="h-4 w-4 md:h-5 md:w-5"
                    :class="
                        getPointColor(transaction.type.label as PointTypeEnum)
                    "
                />
            </div>

            <div class="min-w-0 flex-1">
                <p
                    class="truncate text-sm font-medium text-gray-900 md:text-base"
                >
                    {{ transaction.description }}
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

        <div class="ml-2 shrink-0 text-right md:ml-4">
            <p
                class="text-base font-bold md:text-lg"
                :class="getPointColor(transaction.type.label as PointTypeEnum)"
            >
                {{ getPointPrefix(transaction.type.label as PointTypeEnum)
                }}{{ transaction.points }}
            </p>
            <p class="hidden text-[10px] text-gray-400 md:block md:text-xs">
                {{ t(`common.pointFilters.${transaction.type.key}`) }}
            </p>
        </div>
    </div>
</template>

<style scoped>
@media (hover: none) {
    .active\:scale-\[0\.99\] {
        transition: transform 0.15s ease;
    }
}
</style>
