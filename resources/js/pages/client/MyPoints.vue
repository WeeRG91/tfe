<script setup lang="ts">
import EmptyPoint from '@/components/client/myPoints/EmptyPoint.vue';
import PointItem from '@/components/client/myPoints/PointItem.vue';
import { useDateFormatter } from '@/composables/useDateFormatter';
import { useLocale } from '@/composables/useLocale';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { getTotalPoints } from '@/lib/utils';
import loyaltyPointTransaction from '@/routes/loyalty-point-transaction';
import { CursorPaginated } from '@/types';
import { FilterPointEnum, LoyaltyPointTransactionType } from '@/types/point';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { Award, ClockArrowUp, Loader } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    availablePoints: LoyaltyPointTransactionType[];
}>();

const { t } = useI18n();
const { getIntlLocale } = useLocale();

const { formatDate } = useDateFormatter();

const filterOptions = computed<{ label: string; value: FilterPointEnum }[]>(
    () => [
        {
            label: t('common.pointFilters.all'),
            value: FilterPointEnum.ALL,
        },
        {
            label: t('common.pointFilters.earned'),
            value: FilterPointEnum.EARNED,
        },
        {
            label: t('common.pointFilters.redeemed'),
            value: FilterPointEnum.REDEEMED,
        },
        {
            label: t('common.pointFilters.refunded'),
            value: FilterPointEnum.REFUNDED,
        },
        {
            label: t('common.pointFilters.reversed'),
            value: FilterPointEnum.REVERSED,
        },
    ],
);

const loyaltyPointTransactions = ref<LoyaltyPointTransactionType[]>([]);
const nextCursor = ref<string>('');
const filterPoint = ref<FilterPointEnum>(FilterPointEnum.ALL);
const isLoading = ref<boolean>(false);
const sentinel = ref<HTMLElement | null>(null);
const observer = ref<IntersectionObserver | null>(null);

const latestPointTransaction = computed(
    () => props.availablePoints.at(0) ?? null,
);

const loadLoyaltyPointTransactions = async () => {
    if (isLoading.value) return;
    isLoading.value = true;

    try {
        const { data } = await axios.get<
            CursorPaginated<LoyaltyPointTransactionType>
        >(loyaltyPointTransaction.getLoyaltyPointTransactions().url, {
            params: {
                cursor: nextCursor.value,
                filter: filterPoint.value,
            },
        });

        if (data) {
            const newLoyaltyPointTransactions =
                data.data as LoyaltyPointTransactionType[];
            loyaltyPointTransactions.value.push(...newLoyaltyPointTransactions);
            nextCursor.value = data.next_cursor ?? '';
        }
    } catch (error) {
        console.log(error);
        toast.error(t('poinyt.errors.loadFailed'));
    } finally {
        isLoading.value = false;
    }
};

const groupedTransactions = computed(() => {
    const groups: Record<string, LoyaltyPointTransactionType[]> = {};
    const currentLocale = getIntlLocale();

    loyaltyPointTransactions.value.forEach((transaction) => {
        const date = new Date(transaction.created_at);
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        let label: string;
        if (date.toDateString() === today.toDateString()) {
            label = t('common.dates.today');
        } else if (date.toDateString() === yesterday.toDateString()) {
            label = t('common.dates.yesterday');
        } else if (date.getFullYear() === today.getFullYear()) {
            label = date.toLocaleDateString(currentLocale, {
                month: 'long',
                day: 'numeric',
            });
        } else {
            label = date.toLocaleDateString(currentLocale, {
                month: 'long',
                year: 'numeric',
            });
        }

        if (!groups[label]) groups[label] = [];
        groups[label].push(transaction);
    });

    return groups;
});

const resetAndReload = () => {
    loyaltyPointTransactions.value = [];
    nextCursor.value = '';
    observer.value?.disconnect();
    loadLoyaltyPointTransactions();
};

onMounted(() => {
    loadLoyaltyPointTransactions();

    observer.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting && !isLoading.value) {
                    loadLoyaltyPointTransactions();
                }
            });
        },
        {
            root: null,
            rootMargin: '100px',
            threshold: 0.1,
        },
    );

    watch(sentinel, (element) => {
        if (element) observer.value?.observe(element);
    });
});

watch(filterPoint, resetAndReload);
</script>

<template>
    <Head :title="t('point.pageTitle')" />

    <ClientLayout>
        <section class="mx-auto max-w-6xl px-4 py-3 md:px-6 md:py-4">
            <div class="mb-4 md:mb-6">
                <p
                    class="text-xs tracking-widest text-primary uppercase md:text-sm"
                >
                    [ {{ t('point.eyebrow') }} ]
                </p>
                <h1 class="text-4xl font-semibold uppercase md:text-5xl">
                    {{ t('point.title') }}
                </h1>
                <p
                    class="mt-0.5 text-xs text-muted-foreground md:mt-1 md:text-sm"
                >
                    {{ t('point.description') }}
                </p>
            </div>

            <div
                class="mb-4 rounded-2xl bg-gradient-to-r from-primary to-primary/80 px-4 py-2 text-primary-foreground shadow-lg sm:mb-6 sm:py-4 md:px-6"
            >
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-primary-foreground/20 p-2">
                            <Award class="h-5 w-5 md:h-6 md:w-6" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium opacity-90 md:text-sm"
                            >
                                {{ t('point.availablePoints') }}
                            </p>
                            <p
                                class="text-2xl font-bold tracking-tight md:text-3xl"
                            >
                                {{ getTotalPoints(availablePoints) }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-3 border-t border-primary-foreground/20 pt-3 sm:border-t-0 sm:pt-0"
                    >
                        <div class="rounded-full bg-primary-foreground/20 p-2">
                            <ClockArrowUp class="h-5 w-5 md:h-6 md:w-6" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium opacity-90 md:text-sm"
                            >
                                {{ t('point.lastUpdate') }}
                            </p>
                            <p
                                v-if="latestPointTransaction"
                                class="text-xl font-bold tracking-tight md:text-2xl"
                            >
                                {{
                                    formatDate(
                                        latestPointTransaction.created_at,
                                    )
                                }}
                            </p>
                            <p
                                v-else
                                class="text-xl font-bold tracking-tight md:text-2xl"
                            >
                                {{ t('point.notUpdatedYet') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="mb-4 flex flex-wrap items-center gap-2 md:mb-6 md:gap-4"
            >
                <button
                    v-for="filter in filterOptions"
                    :key="filter.value"
                    @click="filterPoint = filter.value"
                    class="rounded-full border px-3 py-1 text-xs transition-all duration-500 md:px-4 md:py-1.5 md:text-sm"
                    :class="
                        filterPoint === filter.value
                            ? 'border-primary bg-primary text-primary-foreground hover:bg-primary/90'
                            : 'border-border bg-background text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                    "
                >
                    {{ filter.label }}
                </button>
            </div>

            <EmptyPoint v-if="!loyaltyPointTransactions.length && !isLoading" />

            <div v-else class="space-y-4 md:space-y-6">
                <div
                    v-for="(group, dateLabel) in groupedTransactions"
                    :key="dateLabel"
                    class="space-y-2 md:space-y-3"
                >
                    <h3
                        class="text-xs font-semibold text-muted-foreground md:text-sm"
                    >
                        {{ dateLabel }}
                    </h3>

                    <div class="space-y-1.5 md:space-y-2">
                        <PointItem
                            v-for="transaction in group"
                            :key="transaction.id"
                            :transaction="transaction"
                        />
                    </div>
                </div>

                <div
                    ref="sentinel"
                    v-if="nextCursor"
                    class="flex w-full justify-center py-2"
                ></div>
            </div>

            <div
                v-if="isLoading"
                class="flex h-16 items-center justify-center md:h-24"
            >
                <Loader
                    class="h-5 w-5 animate-spin text-primary md:h-6 md:w-6"
                />
            </div>
        </section>
    </ClientLayout>
</template>
