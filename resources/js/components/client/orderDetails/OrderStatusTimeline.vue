<script setup lang="ts">
import { formatTime } from '@/lib/utils';
import { OrderType, OrderTypeEnum } from '@/types/order';
import { Check, Clock } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    orderToShow: OrderType;
}>();

const { t } = useI18n();

type OrderDateKey =
    | 'created_at'
    | 'confirmed_at'
    | 'prepare_at'
    | 'ready_at'
    | 'delivered_at'
    | 'completed_at';

type Step = {
    value: number;
    label: string;
    description: string;
    dateKey?: OrderDateKey;
};

const orderSteps = computed<Step[]>(() => {
    const isDelivery = props.orderToShow.type?.value === OrderTypeEnum.DELIVERY;

    const steps: Step[] = [
        {
            value: 1,
            label: t('order.orderStatusTimeline.steps.placed.title'),
            description: t(
                'order.orderStatusTimeline.steps.placed.description',
            ),
            dateKey: 'created_at',
        },
        {
            value: 2,
            label: t('order.orderStatusTimeline.steps.confirmed.title'),
            description: t(
                'order.orderStatusTimeline.steps.confirmed.description',
            ),
            dateKey: 'confirmed_at',
        },
    ];

    if (isDelivery) {
        steps.push(
            {
                value: 3,
                label: t('order.orderStatusTimeline.steps.preparing.title'),
                description: t(
                    'order.orderStatusTimeline.steps.preparing.description',
                ),
                dateKey: 'prepare_at',
            },
            {
                value: 4,
                label: t('order.orderStatusTimeline.steps.ready.title'),
                description: t(
                    'order.orderStatusTimeline.steps.ready.description',
                ),
                dateKey: 'ready_at',
            },
            {
                value: 5,
                label: t('order.orderStatusTimeline.steps.delivering.title'),
                description: t(
                    'order.orderStatusTimeline.steps.delivering.description',
                ),
                dateKey: 'delivered_at',
            },
            {
                value: 6,
                label: t('order.orderStatusTimeline.steps.completed.title'),
                description: t(
                    'order.orderStatusTimeline.steps.completed.description',
                ),
                dateKey: 'completed_at',
            },
        );
    } else {
        steps.push(
            {
                value: 3,
                label: t('order.orderStatusTimeline.steps.preparing.title'),
                description: t(
                    'order.orderStatusTimeline.steps.preparing.description',
                ),
                dateKey: 'prepare_at',
            },
            {
                value: 4,
                label: t('order.orderStatusTimeline.steps.ready.title'),
                description: t(
                    'order.orderStatusTimeline.steps.ready.description',
                ),
                dateKey: 'ready_at',
            },
            {
                value: 6,
                label: t('order.orderStatusTimeline.steps.completed.title'),
                description: t(
                    'order.orderStatusTimeline.steps.completed.description',
                ),
                dateKey: 'completed_at',
            },
        );
    }

    return steps;
});

const isStepCompleted = (stepValue: number) => {
    const statusOrder = orderSteps.value.map((s) => s.value);
    const currentStatusIndex = statusOrder.indexOf(
        props.orderToShow.status?.value,
    );
    const stepIndex = statusOrder.indexOf(stepValue);
    return stepIndex < currentStatusIndex;
};

const isCurrentStep = (stepValue: number) => {
    return props.orderToShow.status?.value === stepValue;
};

const getStepDate = (stepValue: number) => {
    const step = orderSteps.value.find((s) => s.value === stepValue);
    if (step?.dateKey && props.orderToShow[step.dateKey]) {
        return props.orderToShow[step.dateKey];
    }
    return null;
};
</script>

<template>
    <div
        class="rounded-lg border border-border bg-card p-6 text-card-foreground"
    >
        <h2
            class="mb-6 flex items-center gap-2 text-lg font-semibold uppercase"
        >
            <Clock class="h-5 w-5 text-primary" />
            {{ t('order.orderStatusTimeline.title') }}
        </h2>

        <div class="md:hidden">
            <div class="relative">
                <div
                    class="absolute top-3 left-5 h-84 w-0.5 bg-border"
                    :class="
                        orderToShow.type.label === 'Delivery' &&
                        orderToShow.status.label !== 'Completed'
                            ? 'h-104'
                            : orderToShow.status.label === 'Completed'
                              ? 'h-96'
                              : 'h-84'
                    "
                ></div>

                <div
                    v-for="step in orderSteps"
                    :key="step.value"
                    class="relative mb-8 flex items-start gap-4 last:mb-0"
                >
                    <div class="relative z-10">
                        <div
                            :class="[
                                'flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border-2 shadow-sm transition-all',
                                isStepCompleted(step.value)
                                    ? 'border-success/50 bg-success text-success-foreground'
                                    : isCurrentStep(step.value)
                                      ? 'border-primary bg-primary text-primary-foreground ring-4 ring-primary/15'
                                      : 'border-border bg-background text-muted-foreground',
                            ]"
                        >
                            <Check
                                v-if="
                                    isStepCompleted(step.value) ||
                                    isCurrentStep(step.value)
                                "
                                class="h-5 w-5"
                            />
                        </div>
                    </div>

                    <div class="flex-1">
                        <div
                            class="rounded-lg bg-muted/50 p-3 transition-all"
                            :class="{
                                'border-l-4 border-primary bg-primary/10':
                                    isCurrentStep(step.value),
                            }"
                        >
                            <div class="flex items-center justify-between">
                                <p
                                    :class="[
                                        'font-semibold',
                                        isStepCompleted(step.value) ||
                                        isCurrentStep(step.value)
                                            ? 'text-card-foreground'
                                            : 'text-muted-foreground',
                                    ]"
                                >
                                    {{ step.label }}
                                </p>
                                <p
                                    v-if="getStepDate(step.value)"
                                    class="text-xs text-muted-foreground"
                                >
                                    {{
                                        getStepDate(step.value)
                                            ? formatTime(
                                                  getStepDate(step.value)!,
                                              )
                                            : ''
                                    }}
                                </p>
                            </div>
                            <p
                                v-if="
                                    isCurrentStep(step.value) &&
                                    step.description
                                "
                                class="mt-1 text-xs text-muted-foreground"
                            >
                                {{ step.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="hidden md:block">
            <div class="relative px-4">
                <div class="flex items-start justify-between">
                    <div
                        v-for="(step, index) in orderSteps"
                        :key="step.value"
                        class="relative flex flex-1 flex-col items-center"
                    >
                        <div
                            :class="[
                                'z-10 flex h-12 w-12 items-center justify-center rounded-full border-2 shadow-sm transition-all',
                                isStepCompleted(step.value)
                                    ? 'border-success/50 bg-success text-success-foreground'
                                    : isCurrentStep(step.value)
                                      ? 'border-primary bg-primary text-primary-foreground ring-4 ring-primary/15'
                                      : 'border-border bg-background text-muted-foreground',
                            ]"
                        >
                            <Check
                                v-if="
                                    isStepCompleted(step.value) ||
                                    isCurrentStep(step.value)
                                "
                                class="h-5 w-5"
                            />
                        </div>

                        <div class="mt-3 text-center">
                            <p
                                :class="[
                                    'text-sm font-medium',
                                    isStepCompleted(step.value) ||
                                    isCurrentStep(step.value)
                                        ? 'text-card-foreground'
                                        : 'text-muted-foreground',
                                ]"
                            >
                                {{ step.label }}
                            </p>
                            <p
                                v-if="getStepDate(step.value)"
                                class="mt-1 text-xs whitespace-nowrap text-muted-foreground"
                            >
                                {{
                                    getStepDate(step.value)
                                        ? formatTime(getStepDate(step.value)!)
                                        : ''
                                }}
                            </p>
                        </div>

                        <div
                            v-if="index < orderSteps.length - 1"
                            class="absolute top-6 right-0 left-[calc(50%+0.75rem)] h-0.5 -translate-y-1/2"
                            :class="[
                                isStepCompleted(step.value)
                                    ? 'bg-success'
                                    : 'bg-border',
                            ]"
                            :style="{ width: 'calc(100% - 1.5rem)' }"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
