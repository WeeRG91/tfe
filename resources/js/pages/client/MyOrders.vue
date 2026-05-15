<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import { useOrderStore } from '@/stores/order';
import { OrderType } from '@/types/order';
import { Head, router } from '@inertiajs/vue3';
import {
    BookOpen,
    Clock,
    Star,
    TrendingUp,
    XCircle,
} from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref, watch } from 'vue';
import EmptyOrder from '@/components/client/myOrder/EmptyOrder.vue';
import LoadingSkeleton from '@/components/client/myOrder/LoadingSkeleton.vue';
import OrderList from '@/components/client/myOrder/OrderList.vue';

const orderStore = useOrderStore();
const { orders, isLoading } = storeToRefs(orderStore);

const selectedStatusValue = ref<number>(0)

const filterOptions = [
    { label: 'All', value: 0 },
    { label: 'Active Orders', value: 1 },
    { label: 'Completed Orders', value: 6 },
    { label: 'Cancelled Orders', value: 7 }
];

const filteredOrders = computed(() => {
    if (selectedStatusValue.value === 0) {
        return orders.value;
    }

    return orders.value.filter(order => {
        const status = order.status?.label || order.status;

        if (selectedStatusValue.value === 1) {
            // Active orders: Pending, Confirmed, Preparing, Ready, Delivering
            return status === 'Pending' ||
                status === 'Confirmed' ||
                status === 'Preparing' ||
                status === 'Ready' ||
                status === 'Delivering';
        } else if (selectedStatusValue.value === 6) {
            return status === 'Completed';
        } else if (selectedStatusValue.value === 7) {
            return status === 'Cancelled';
        }
        return true;
    });
});

const groupedOrders = computed(() => {
    const groups = {
        active: [] as OrderType[],
        completed: [] as OrderType[],
        cancelled: [] as OrderType[],
    };

    filteredOrders.value.forEach((order) => {
        const status = order.status?.label || order.status;
        if (
            status === 'Pending' ||
            status === 'Confirmed' ||
            status === 'Preparing' ||
            status === 'Ready' ||
            status === 'Delivering'
        ) {
            groups.active.push(order);
        } else if (status === 'Completed') {
            groups.completed.push(order);
        } else if (status === 'Cancelled') {
            groups.cancelled.push(order);
        } else {
            groups.active.push(order);
        }
    });

    return groups;
});

onMounted(async () => {
    await orderStore.getOrders(selectedStatusValue.value);
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

            <!-- Loading State -->
            <LoadingSkeleton v-if="isLoading" />

            <!-- No Orders State -->
            <EmptyOrder v-else-if="!filteredOrders.length && !isLoading" />

            <!-- Orders List -->
            <div v-else class="space-y-8">
                <!-- Show sections based on selected filter -->
                <template v-if="selectedStatusValue === 0 || selectedStatusValue === 1">
                    <!-- Active Orders Section -->
                    <OrderList
                        v-if="groupedOrders.active.length"
                        title="Active Orders"
                        :icon="Clock"
                        icon-color="amber"
                        :orders="groupedOrders.active"
                    />
                </template>

                <template v-if="selectedStatusValue === 0 || selectedStatusValue === 6">
                    <!-- Completed Orders Section -->
                    <OrderList
                        v-if="groupedOrders.completed.length"
                        title="Completed Orders"
                        :icon="Star"
                        icon-color="emerald"
                        :orders="groupedOrders.completed"
                    />
                </template>

                <template v-if="selectedStatusValue === 0 || selectedStatusValue === 7">
                    <!-- Cancelled Orders Section -->
                    <OrderList
                        v-if="groupedOrders.cancelled.length"
                        title="Cancelled Orders"
                        :icon="XCircle"
                        icon-color="red"
                        :orders="groupedOrders.cancelled"
                    />
                </template>

                <!-- Message when no orders in selected filter -->
                <div
                    v-if="filteredOrders.length &&
                          groupedOrders.active.length === 0 &&
                          groupedOrders.completed.length === 0 &&
                          groupedOrders.cancelled.length === 0"
                    class="py-12 text-center text-gray-500"
                >
                    No orders found in this category.
                </div>

                <!-- Browse Menu CTA -->
                <div class="pt-4 text-center">
                    <button
                        @click="router.visit('/menu/dishes')"
                        class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-6 py-2.5 text-sm font-medium text-red-500 transition-all hover:bg-red-50 hover:shadow-sm"
                    >
                        <BookOpen class="h-4 w-4" />
                        Browse our menu to place a new order
                        <TrendingUp class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </section>
    </ClientLayout>
</template>

<style scoped></style>
