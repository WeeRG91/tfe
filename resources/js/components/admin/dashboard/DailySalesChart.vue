<script setup lang="ts">
import type { ChartColors } from '@/composables/useChartColors';
import { LineChart } from 'echarts/charts';
import { GridComponent, TooltipComponent } from 'echarts/components';
import { use, type EChartsCoreOption } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { computed } from 'vue';
import VChart from 'vue-echarts';
import { useI18n } from 'vue-i18n';

use([CanvasRenderer, LineChart, GridComponent, TooltipComponent]);

const props = defineProps<{
    dailySales: {
        date: string;
        sales: number;
    }[];
    periodLabel: string;
    chartColors: ChartColors;
}>();

const { t, locale } = useI18n();

const currencyFormatter = computed(
    () =>
        new Intl.NumberFormat(locale.value, {
            style: 'currency',
            currency: 'EUR',
        }),
);

const option = computed<EChartsCoreOption>(() => ({
    color: props.chartColors.series,
    tooltip: {
        trigger: 'axis',
        backgroundColor: props.chartColors.tooltipBackground,
        borderColor: props.chartColors.gridLine,
        textStyle: { color: props.chartColors.tooltipText },
        valueFormatter: (value: unknown) =>
            currencyFormatter.value.format(Number(value)),
    },
    grid: {
        left: 20,
        right: 20,
        top: 30,
        bottom: 20,
        containLabel: true,
    },
    xAxis: {
        type: 'category',
        boundaryGap: false,
        data: props.dailySales.map((day) => day.date),
        axisLabel: {
            color: props.chartColors.axisText,
            formatter: (value: string) => value.slice(5),
            interval: Math.max(0, Math.ceil(props.dailySales.length / 7) - 1),
        },
        axisLine: {
            lineStyle: { color: props.chartColors.gridLine },
        },
    },
    yAxis: {
        type: 'value',
        min: 0,
        axisLabel: {
            color: props.chartColors.axisText,
            formatter: (value: number) => `${value} €`,
        },
        splitLine: {
            lineStyle: { color: props.chartColors.gridLine },
        },
    },
    series: [
        {
            name: t('dashboard.charts.dailySales.seriesName'),
            type: 'line',
            data: props.dailySales.map((day) => day.sales),
            symbolSize: 8,
            showSymbol: props.dailySales.length <= 7,
        },
    ],
}));
</script>

<template>
    <section class="p-6">
        <h2 class="text-lg font-semibold">
            {{ t('dashboard.charts.dailySales.title') }}
        </h2>

        <p class="mt-1 text-sm text-muted-foreground">
            {{ periodLabel }} · {{ t('dashboard.charts.dailySales.detail') }}
        </p>

        <VChart
            class="mt-6"
            style="height: 320px; width: 100%"
            :option="option"
            autoresize
        />
    </section>
</template>
