<script setup lang="ts">
import { BarChart } from 'echarts/charts';
import { GridComponent, TooltipComponent } from 'echarts/components';
import { use, type EChartsCoreOption } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { computed } from 'vue';
import VChart from 'vue-echarts';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    ordersByHour: {
        hour: string;
        count: number;
    }[];
    periodLabel: string;
}>();

const { t } = useI18n();

use([CanvasRenderer, BarChart, GridComponent, TooltipComponent]);

const totalOrders = computed(() =>
    props.ordersByHour.reduce((total, hour) => total + hour.count, 0),
);

const option = computed<EChartsCoreOption>(() => ({
    tooltip: {
        trigger: 'axis',
        valueFormatter: (value: unknown) =>
            t('dashboard.charts.ordersByHour.tooltip', {
                count: Number(value),
            }),
    },
    grid: {
        left: 20,
        right: 20,
        top: 20,
        bottom: 30,
        containLabel: true,
    },
    xAxis: {
        type: 'category',
        data: props.ordersByHour.map((hour) => hour.hour),
        axisLabel: {
            interval: 2,
        },
    },
    yAxis: {
        type: 'value',
        min: 0,
        minInterval: 1,
    },
    series: [
        {
            name: t('dashboard.charts.ordersByHour.seriesName'),
            type: 'bar',
            data: props.ordersByHour.map((hour) => hour.count),
        },
    ],
}));
</script>

<template>
    <section class="p-6">
        <h2 class="text-lg font-semibold">
            {{ t('dashboard.charts.ordersByHour.title') }}
        </h2>

        <p class="mt-1 text-sm text-muted-foreground">
            {{ periodLabel }} · {{ t('dashboard.charts.ordersByHour.detail') }}
        </p>

        <p v-if="totalOrders === 0" class="mt-6 text-sm text-muted-foreground">
            {{ t('dashboard.charts.ordersByHour.empty') }}
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
