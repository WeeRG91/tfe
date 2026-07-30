<script setup lang="ts">
import EmptyOrder from '@/components/client/myOrders/EmptyOrder.vue';
import OrderList from '@/components/client/myOrders/OrderList.vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { useOrderStore } from '@/stores/order';
import {
    ActiveStatuses,
    FilterOrderEnum,
    OrderStatusEnum,
    OrderType,
} from '@/types/order';
import { Head, usePage } from '@inertiajs/vue3';
import { Clock, Loader, Star, XCircle } from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const page = usePage();
const user = page.props.auth?.user;

const orderStore = useOrderStore();
const { orders } = storeToRefs(orderStore);

const selectedStatusValue = ref<FilterOrderEnum>(FilterOrderEnum.ACTIVE);
const isLoading = ref<boolean>(false);

const filterOptions = computed<{ label: string; value: FilterOrderEnum }[]>(
    () => [
        {
            label: t('order.filters.active'),
            value: FilterOrderEnum.ACTIVE,
        },
        {
            label: t('order.filters.completed'),
            value: FilterOrderEnum.COMPLETED,
        },
        {
            label: t('order.filters.cancelled'),
            value: FilterOrderEnum.CANCELLED,
        },
    ],
);

const groupedOrders = computed(() => {
    const groups = {
        active: [] as OrderType[],
        completed: [] as OrderType[],
        cancelled: [] as OrderType[],
    };

    orders.value.forEach((order) => {
        const status = order.status?.value;
        if (ActiveStatuses.includes(status)) {
            groups.active.push(order);
        } else if (status === OrderStatusEnum.COMPLETED) {
            groups.completed.push(order);
        } else if (status === OrderStatusEnum.CANCELLED) {
            groups.cancelled.push(order);
        } else {
            groups.active.push(order);
        }
    });

    return groups;
});

const loadOrders = async () => {
    isLoading.value = true;

    try {
        await orderStore.getOrders(selectedStatusValue.value);
    } catch (error) {
        console.error(error);
    } finally {
        isLoading.value = false;
    }
};

type EchoChannel = {
    listen: (
        event: string,
        callback: (e: {
            id: number;
            order_number: string;
            status: number;
        }) => void,
    ) => EchoChannel;
};

const channel = ref<EchoChannel | null>(null);

onMounted(async () => {
    await loadOrders();

    if (user?.id) {
        channel.value = window.Echo.private(`user.${user?.id}`).listen(
            '.order.updated',
            async (e: { id: number; order_number: string; status: number }) => {
                console.log(e);
                if (e.status !== OrderStatusEnum.CANCELLED) {
                    await loadOrders();
                }
            },
        );
    }
});

onUnmounted(() => {
    if (channel.value) {
        window.Echo.leave(`private-user.${user?.id}`);
    }
});

watch(selectedStatusValue, async () => {
    await loadOrders();
});
</script>

<template>
    <Head :title="t('order.pageTitle')" />
    <ClientLayout>
        <section class="mx-auto max-w-6xl px-6 py-4">
            <div class="mb-6">
                <p class="text-sm tracking-widest text-red-500 uppercase">
                    [ {{ t('order.eyebrow') }} ]
                </p>
                <h1 class="text-4xl font-semibold uppercase md:text-5xl">
                    {{ t('order.title') }}
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    {{ t('order.description') }}
                </p>
            </div>

            <div class="mb-6 flex flex-wrap items-center gap-4">
                <button
                    v-for="filter in filterOptions"
                    :key="filter.value"
                    @click="selectedStatusValue = filter.value"
                    class="rounded-full border px-4 py-1 text-sm transition-all duration-500"
                    :class="
                        selectedStatusValue === filter.value
                            ? 'border-red-500 bg-red-500 text-white hover:bg-red-600'
                            : 'text-gray-600 hover:bg-gray-100'
                    "
                >
                    {{ filter.label }}
                </button>
            </div>

            <div
                v-if="isLoading"
                class="flex h-24 items-center justify-center sm:h-32"
            >
                <Loader class="mx-auto animate-spin text-muted-foreground" />
            </div>

            <EmptyOrder v-else-if="!orders.length && !isLoading" />

            <div v-else class="space-y-8">
                <template v-if="selectedStatusValue === FilterOrderEnum.ACTIVE">
                    <OrderList
                        v-if="groupedOrders.active.length"
                        :title="t('order.filters.active')"
                        :icon="Clock"
                        icon-color="amber"
                        :orders="groupedOrders.active"
                    />
                </template>

                <template
                    v-if="selectedStatusValue === FilterOrderEnum.COMPLETED"
                >
                    <OrderList
                        v-if="groupedOrders.completed.length"
                        :title="t('order.filters.completed')"
                        :icon="Star"
                        icon-color="emerald"
                        :orders="groupedOrders.completed"
                    />
                </template>

                <template
                    v-if="selectedStatusValue === FilterOrderEnum.CANCELLED"
                >
                    <OrderList
                        v-if="groupedOrders.cancelled.length"
                        :title="t('order.filters.cancelled')"
                        :icon="XCircle"
                        icon-color="red"
                        :orders="groupedOrders.cancelled"
                    />
                </template>
            </div>
        </section>
    </ClientLayout>
</template>

<style scoped></style>
