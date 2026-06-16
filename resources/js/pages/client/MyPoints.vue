<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import loyaltyPointTransaction from '@/routes/loyalty-point-transaction';
import { CursorPaginated } from '@/types';
import { FilterPointEnum, LoyaltyPointTransactionType } from '@/types/point';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { formatDate } from '@/lib/utils';
import EmptyPoint from '@/components/client/myPoints/EmptyPoint.vue';
import PointItem from '@/components/client/myPoints/PointItem.vue';
import { Loader, Award, ClockArrowUp } from 'lucide-vue-next';

const page = usePage();
const user = page.props.auth?.user;

const filterOptions: { label: string; value: FilterPointEnum }[] = [
    { label: 'All', value: FilterPointEnum.ALL },
    { label: 'Earned', value: FilterPointEnum.EARNED },
    { label: 'Redeemed', value: FilterPointEnum.REDEEMED },
    { label: 'Refunded', value: FilterPointEnum.REFUNDED },
];

const loyaltyPointTransactions = ref<LoyaltyPointTransactionType[]>([]);
const nextCursor = ref<string>('');
const filterPoint = ref<FilterPointEnum>(FilterPointEnum.ALL);
const isLoading = ref<boolean>(false);
const sentinel = ref<HTMLElement | null>(null);
const observer = ref<IntersectionObserver | null>(null);

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
        toast.error('Failed to load loyalty points');
    } finally {
        isLoading.value = false;
    }
};

const groupedTransactions = computed(() => {
    const groups: Record<string, LoyaltyPointTransactionType[]> = {};

    loyaltyPointTransactions.value.forEach((transaction) => {
        const date = new Date(transaction.created_at);
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        let label: string;
        if (date.toDateString() === today.toDateString()) {
            label = 'Today';
        } else if (date.toDateString() === yesterday.toDateString()) {
            label = 'Yesterday';
        } else if (date.getFullYear() === today.getFullYear()) {
            label = date.toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
            });
        } else {
            label = date.toLocaleDateString('en-US', {
                month: 'long',
                year: 'numeric',
            });
        }

        if (!groups[label]) groups[label] = [];
        groups[label].push(transaction);
    });

    return groups;
});

// Reset and reload when filter changes
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
    <Head title="My Points" />

    <ClientLayout>
        <section class="mx-auto max-w-6xl px-4 py-3 md:px-6 md:py-4">
            <div class="mb-4 md:mb-6">
                <p
                    class="text-xs tracking-widest text-red-500 uppercase md:text-sm"
                >
                    [ My Account ]
                </p>
                <h1 class="text-4xl font-semibold uppercase md:text-5xl">
                    My Points
                </h1>
                <p class="mt-0.5 text-xs text-gray-600 md:mt-1 md:text-sm">
                    Track your loyalty points and rewards
                </p>
            </div>

            <div
                class="mb-4 rounded-2xl bg-gradient-to-r from-red-500 to-red-600 px-4 py-2 text-white shadow-lg sm:mb-6 md:px-6 sm:py-4"
            >
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-white/20 p-2">
                            <Award class="h-5 w-5 md:h-6 md:w-6" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium opacity-90 md:text-sm"
                            >
                                Available Points
                            </p>
                            <p
                                class="text-2xl font-bold tracking-tight md:text-3xl"
                            >
                                {{
                                    user?.loyalty_points?.toLocaleString() || 0
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-3 border-t border-white/20 pt-3 sm:border-t-0 sm:pt-0"
                    >
                        <div class="rounded-full bg-white/20 p-2">
                            <ClockArrowUp class="h-5 w-5 md:h-6 md:w-6" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium opacity-90 md:text-sm"
                            >
                                Last Updated
                            </p>
                            <p
                                class="text-xl font-bold tracking-tight md:text-2xl"
                            >
                                {{ formatDate(user.created_at) }}
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
                            ? 'border-red-500 bg-red-500 text-white hover:bg-red-600'
                            : 'text-gray-600 hover:bg-gray-100'
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
                    <h3 class="text-xs font-semibold text-gray-400 md:text-sm">
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
                    class="h-5 w-5 animate-spin text-gray-400 md:h-6 md:w-6"
                />
            </div>
        </section>
    </ClientLayout>
</template>
