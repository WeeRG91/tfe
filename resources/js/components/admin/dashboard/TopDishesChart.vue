<script setup lang="ts">
import { BarChart } from 'echarts/charts';
import { GridComponent, TooltipComponent } from 'echarts/components';
import { use, type EChartsCoreOption } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { computed } from 'vue';
import VChart from 'vue-echarts';
import { useI18n } from 'vue-i18n';
import type { ChartColors } from '@/composables/useChartColors';

use([CanvasRenderer, BarChart, GridComponent, TooltipComponent]);

const props = defineProps<{
    topDishes: {
        name: string;
        quantity: number;
    }[];
    periodLabel: string;
    chartColors: ChartColors;
}>();

const { t } = useI18n();

const option = computed<EChartsCoreOption>(() => ({
    color: props.chartColors.series,
    tooltip: {
        trigger: 'axis',
        backgroundColor: props.chartColors.tooltipBackground,
        borderColor: props.chartColors.gridLine,
        textStyle: { color: props.chartColors.tooltipText },
        valueFormatter: (value: unknown) =>
            t('dashboard.charts.topDishes.tooltip', {
                count: Number(value),
            }),
    },
    grid: {
        left: 20,
        right: 30,
        top: 20,
        bottom: 20,
        containLabel: true,
    },
    xAxis: {
        type: 'value',
        min: 0,
        minInterval: 1,
        axisLabel: { color: props.chartColors.axisText },
        splitLine: {
            lineStyle: { color: props.chartColors.gridLine },
        },
    },
    yAxis: {
        type: 'category',
        inverse: true,
        data: props.topDishes.map((dish) => dish.name),
        axisLabel: { color: props.chartColors.axisText },
        axisLine: {
            lineStyle: { color: props.chartColors.gridLine },
        },
    },
    series: [
        {
            name: t('dashboard.charts.topDishes.series'),
            type: 'bar',
            data: props.topDishes.map((dish) => dish.quantity),
        },
    ],
}));
</script>

<template>
    <section class="p-6">
        <h2 class="text-lg font-semibold">
            {{ t('dashboard.charts.topDishes.title') }}
        </h2>
        <p class="mt-1 text-sm text-muted-foreground">
            {{ periodLabel }} · {{ t('dashboard.charts.topDishes.detail') }}
        </p>

        <p
            v-if="topDishes.length === 0"
            class="mt-6 text-sm text-muted-foreground"
        >
            {{ t('dashboard.charts.topDishes.empty') }}
        </p>

        <VChart
            v-else
            class="mt-6"
            style="height: 320px; width: 100%"
            :option="option"
            autoresize
        />
    </section>
</template>
