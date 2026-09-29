<script setup lang="ts">
import type { ChartColors } from '@/composables/useChartColors';
import { PieChart } from 'echarts/charts';
import { LegendComponent, TooltipComponent } from 'echarts/components';
import { use, type EChartsCoreOption } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { computed } from 'vue';
import VChart from 'vue-echarts';
import { useI18n } from 'vue-i18n';

use([CanvasRenderer, PieChart, LegendComponent, TooltipComponent]);

const props = defineProps<{
    ordersByType: {
        key: string;
        count: number;
    }[];
    periodLabel: string;
    chartColors: ChartColors;
}>();

const { t } = useI18n();

const totalOrders = computed(() =>
    props.ordersByType.reduce((total, type) => total + type.count, 0),
);

type TypeTooltipPoint = {
    name: string;
    value: number;
    percent: number;
};

const option = computed<EChartsCoreOption>(() => ({
    color: props.chartColors.series,
    tooltip: {
        trigger: 'item',
        backgroundColor: props.chartColors.tooltipBackground,
        borderColor: props.chartColors.gridLine,
        textStyle: { color: props.chartColors.tooltipText },
        formatter: (params: unknown) => {
            const point = params as TypeTooltipPoint;

            return t('dashboard.charts.ordersByType.tooltip', {
                type: point.name,
                count: Number(point.value),
                percent: Number(point.percent),
            });
        },
    },
    legend: {
        bottom: 0,
        textStyle: { color: props.chartColors.axisText },
    },
    series: [
        {
            name: t('dashboard.charts.ordersByType.seriesName'),
            type: 'pie',
            radius: ['45%', '70%'],
            center: ['50%', '42%'],
            label: {
                show: false,
            },
            data: props.ordersByType.map((type) => ({
                name: t(`confirmedOrder.types.${type.key}`),
                value: type.count,
            })),
        },
    ],
}));
</script>

<template>
    <section class="p-6">
        <h2 class="text-lg font-semibold">
            {{ t('dashboard.charts.ordersByType.title') }}
        </h2>

        <p class="mt-1 text-sm text-muted-foreground">
            {{ periodLabel }} ·
            {{ t('dashboard.charts.ordersByType.detail') }}
        </p>

        <p v-if="totalOrders === 0" class="mt-6 text-sm text-muted-foreground">
            {{ t('dashboard.charts.ordersByType.empty') }}
        </p>

        <template v-else>
            <VChart
                class="mt-6"
                style="height: 320px; width: 100%"
                :option="option"
                autoresize
            />

            <ul class="mt-4 space-y-1 text-sm">
                <li
                    v-for="type in ordersByType"
                    :key="type.key"
                    class="flex justify-between gap-4"
                >
                    <span>{{ t(`confirmedOrder.types.${type.key}`) }}</span>
                    <span class="font-medium">{{ type.count }}</span>
                </li>
            </ul>
        </template>
    </section>
</template>
