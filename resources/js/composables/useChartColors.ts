import { onMounted, onUnmounted, ref } from 'vue';

export interface ChartColors {
    series: string[];
    axisText: string;
    gridLine: string;
    tooltipBackground: string;
    tooltipText: string;
}

const colorVariables = [
    '--chart-1',
    '--chart-2',
    '--chart-3',
    '--chart-4',
    '--chart-5',
] as const;

function readChartColors(): ChartColors {
    if (typeof document === 'undefined') {
        return {
            series: [],
            axisText: '',
            gridLine: '',
            tooltipBackground: '',
            tooltipText: '',
        };
    }

    const styles = getComputedStyle(document.documentElement);

    const get = (variable: string) => styles.getPropertyValue(variable).trim();

    return {
        series: colorVariables.map(get),
        axisText: get('--card-foreground'),
        gridLine: get('--border'),
        tooltipBackground: get('--popover'),
        tooltipText: get('--popover-foreground'),
    };
}

export function useChartColors() {
    const chartColors = ref<ChartColors>(readChartColors());
    let observer: MutationObserver | null = null;

    onMounted(() => {
        chartColors.value = readChartColors();

        observer = new MutationObserver(() => {
            chartColors.value = readChartColors();
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['style', 'class', 'data-theme'],
        });
    });

    onUnmounted(() => observer?.disconnect());

    return chartColors;
}
