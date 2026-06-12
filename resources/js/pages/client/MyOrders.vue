<script setup lang="ts">
import EmptyOrder from '@/components/client/myOrders/EmptyOrder.vue';
import LoadingSkeleton from '@/components/client/myOrders/LoadingSkeleton.vue';
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
import { Clock, Star, XCircle } from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const page = usePage();
const user = page.props.auth?.user;

const orderStore = useOrderStore();
const { orders } = storeToRefs(orderStore);

const selectedStatusValue = ref<FilterOrderEnum>(FilterOrderEnum.ACTIVE);
const isLoading = ref<boolean>(false);

const filterOptions: { label: string; value: FilterOrderEnum }[] = [
    { label: 'Active Orders', value: FilterOrderEnum.ACTIVE },
    { label: 'Completed Orders', value: FilterOrderEnum.COMPLETED },
    { label: 'Cancelled Orders', value: FilterOrderEnum.CANCELLED },
];

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

type EchoChannel = {
    listen: (event: string, callback: () => void) => EchoChannel;
};

const channel = ref<EchoChannel | null>(null);

onMounted(async () => {
    isLoading.value = true;

    try {
        await orderStore.getOrders(selectedStatusValue.value);
    } catch (error) {
        console.error(error);
    } finally {
        isLoading.value = false;
    }

    if (user?.id) {
        channel.value = window.Echo.private(`user.${user?.id}`).listen(
            '.order.updated',
            async () => {
                await orderStore.getOrders(selectedStatusValue.value);
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
    await orderStore.getOrders(selectedStatusValue.value);
});
</script>

<template>
    <Head title="My Orders" />
    <ClientLayout>
        <section class="mx-auto max-w-6xl px-6 py-4">
            <!-- Header -->
            <div class="mb-6">
                <p class="text-sm tracking-widest text-red-500 uppercase">
                    [ My Account ]
                </p>
                <h1 class="text-4xl font-semibold uppercase md:text-5xl">
                    My Orders
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Track, manage, and reorder your favorite meals
                </p>
            </div>

            <!-- Status Filters -->
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

            <LoadingSkeleton v-if="isLoading" />

            <EmptyOrder v-else-if="!orders.length && !isLoading" />

            <div v-else class="space-y-8">
                <template
                    v-if="
                        selectedStatusValue === FilterOrderEnum.ACTIVE
                    "
                >
                    <OrderList
                        v-if="groupedOrders.active.length"
                        title="Active Orders"
                        :icon="Clock"
                        icon-color="amber"
                        :orders="groupedOrders.active"
                    />
                </template>

                <template
                    v-if="
                        selectedStatusValue === FilterOrderEnum.COMPLETED
                    "
                >
                    <OrderList
                        v-if="groupedOrders.completed.length"
                        title="Completed Orders"
                        :icon="Star"
                        icon-color="emerald"
                        :orders="groupedOrders.completed"
                    />
                </template>

                <template
                    v-if="
                        selectedStatusValue === FilterOrderEnum.CANCELLED
                    "
                >
                    <OrderList
                        v-if="groupedOrders.cancelled.length"
                        title="Cancelled Orders"
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
