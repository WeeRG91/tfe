<script setup lang="ts">
import DailySalesChart from '@/components/admin/dashboard/DailySalesChart.vue';
import OrdersByHourChart from '@/components/admin/dashboard/OrdersByHourChart.vue';
import OrdersByTypeChart from '@/components/admin/dashboard/OrdersByTypeChart.vue';
import TopDishesChart from '@/components/admin/dashboard/TopDishesChart.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { dashboard } from '@/routes/admin';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    completedOrders: number;
    paidCompletedSales: number;
    averageOrderValue: number;
    dailySales: {
        date: string;
        sales: number;
    }[];
    period: number;
    cancelledPeriodOrders: number;
    totalPeriodOrders: number;
    cancellationRate: number;
    topDishes: {
        name: string;
        quantity: number;
    }[];
    ordersByType: {
        key: string;
        count: number;
    }[];
    ordersByHour: {
        hour: string;
        count: number;
    }[];
    averagePreparationMinutes: number | null;
    preparationSampleSize: number;
    payingCustomersInPeriod: number;
    returningPayingCustomers: number;
    returningCustomerRate: number | null;
}>();

const { t, locale } = useI18n();

const periodLabel = computed(() => {
    switch (props.period) {
        case 1:
            return t('dashboard.periods.today');
        case 30:
            return t('dashboard.periods.last30');
        case 90:
            return t('dashboard.periods.last90');
        default:
            return t('dashboard.periods.last7');
    }
});

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('dashboard.title'),
        href: dashboard().url,
    },
]);

const currencyFormatter = computed(
    () =>
        new Intl.NumberFormat(locale.value, {
            style: 'currency',
            currency: 'EUR',
        }),
);

const percentageFormatter = computed(
    () =>
        new Intl.NumberFormat(locale.value, {
            maximumFractionDigits: 2,
        }),
);

const changePeriod = (event: Event) => {
    const selectedPeriod = Number((event.target as HTMLSelectElement).value);

    router.get(
        dashboard().url,
        { period: selectedPeriod },
        { preserveScroll: true },
    );
};
</script>

<template>
    <Head :title="t('dashboard.title')" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-end gap-3">
                <label for="dashboard-period" class="text-sm font-medium">
                    {{ t('dashboard.period') }}
                </label>

                <select
                    id="dashboard-period"
                    :value="period"
                    class="rounded-md border border-sidebar-border bg-background px-3 py-2 text-sm"
                    @change="changePeriod"
                >
                    <option value="1">
                        {{ t('dashboard.periodOptions.today') }}
                    </option>
                    <option value="7">
                        {{ t('dashboard.periodOptions.last7') }}
                    </option>
                    <option value="30">
                        {{ t('dashboard.periodOptions.last30') }}
                    </option>
                    <option value="90">
                        {{ t('dashboard.periodOptions.last90') }}
                    </option>
                </select>
            </div>

            <div class="grid auto-rows-min gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <p class="text-sm text-muted-foreground">
                        {{ t('dashboard.cards.completedOrders') }}
                    </p>

                    <p class="mt-2 text-3xl font-semibold">
                        {{ completedOrders }}
                    </p>

                    <p class="mt-2 text-sm text-muted-foreground">
                        {{ periodLabel }}
                    </p>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <p class="text-sm text-muted-foreground">
                        {{ t('dashboard.cards.paidSales') }}
                    </p>

                    <p class="mt-2 text-3xl font-semibold">
                        {{ currencyFormatter.format(paidCompletedSales) }}
                    </p>

                    <p class="mt-2 text-sm text-muted-foreground">
                        {{ periodLabel }}
                    </p>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <p class="text-sm text-muted-foreground">
                        {{ t('dashboard.cards.averageOrderValue') }}
                    </p>

                    <p class="mt-2 text-3xl font-semibold">
                        {{ currencyFormatter.format(averageOrderValue) }}
                    </p>

                    <p class="mt-2 text-sm text-muted-foreground">
                        {{ periodLabel }}
                    </p>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <p class="text-sm text-muted-foreground">
                        {{ t('dashboard.cards.cancellationRate') }}
                    </p>

                    <p class="mt-2 text-3xl font-semibold">
                        {{ percentageFormatter.format(cancellationRate) }}%
                    </p>

                    <p class="mt-2 text-sm text-muted-foreground">
                        {{
                            t('dashboard.details.cancellation', {
                                cancelled: cancelledPeriodOrders,
                                total: totalPeriodOrders,
                                period: periodLabel,
                            })
                        }}
                    </p>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <p class="text-sm text-muted-foreground">
                        {{ t('dashboard.cards.preparationTime') }}
                    </p>

                    <p class="mt-2 text-3xl font-semibold">
                        <template v-if="averagePreparationMinutes !== null">
                            {{ averagePreparationMinutes }}
                            {{ t('dashboard.units.minutes') }}
                        </template>
                        <template v-else>—</template>
                    </p>

                    <p class="mt-2 text-sm text-muted-foreground">
                        {{
                            preparationSampleSize > 0
                                ? t('dashboard.details.preparationSample', {
                                      count: preparationSampleSize,
                                      period: periodLabel,
                                  })
                                : t('dashboard.details.noTimedOrders', {
                                      period: periodLabel,
                                  })
                        }}
                    </p>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <p class="text-sm text-muted-foreground">
                        {{ t('dashboard.cards.returningCustomers') }}
                    </p>

                    <p class="mt-2 text-3xl font-semibold">
                        <template v-if="returningCustomerRate !== null">
                            {{
                                percentageFormatter.format(
                                    returningCustomerRate,
                                )
                            }}%
                        </template>
                        <template v-else>—</template>
                    </p>

                    <p class="mt-2 text-sm text-muted-foreground">
                        {{
                            payingCustomersInPeriod > 0
                                ? t('dashboard.details.returningCustomers', {
                                      returning: returningPayingCustomers,
                                      total: payingCustomersInPeriod,
                                      period: periodLabel,
                                  })
                                : t('dashboard.details.noPayingCustomers', {
                                      period: periodLabel,
                                  })
                        }}
                    </p>
                </div>
            </div>

            <div
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <DailySalesChart
                    :daily-sales="dailySales"
                    :period-label="periodLabel"
                />
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div
                    class="min-w-0 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <TopDishesChart
                        :top-dishes="topDishes"
                        :period-label="periodLabel"
                    />
                </div>

                <div
                    class="min-w-0 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <OrdersByTypeChart
                        :orders-by-type="ordersByType"
                        :period-label="periodLabel"
                    />
                </div>
            </div>

            <div
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <OrdersByHourChart
                    :orders-by-hour="ordersByHour"
                    :period-label="periodLabel"
                />
            </div>
        </div>
    </AdminLayout>
</template>
