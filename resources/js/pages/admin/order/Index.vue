<script setup lang="ts">
import OrderCard from '@/components/admin/order/OrderCard.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { useOrderStore } from '@/stores/order';
import { BreadcrumbItem } from '@/types';
import { OrderStatusEnum } from '@/types/order';
import { Head } from '@inertiajs/vue3';
import {
    CheckCircle,
    ChevronDown,
    ChevronUp,
    Clock,
    Package,
    RefreshCw,
    Truck,
} from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';

const orderStore = useOrderStore();
const { confirmedOrders } = storeToRefs(orderStore);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '#' },
    { title: 'Confirmed Orders', href: '#' },
];

const updatedStatusOrderId = ref<number | null>(null);
const expandedColumns = ref<Set<string>>(new Set(['confirmed']));

const columns = computed(() => [
    {
        key: 'confirmed',
        title: 'Confirmed (Waiting)',
        icon: Clock,
        color: 'blue',
        orders: (confirmedOrders.value ?? []).filter(
            (o) =>
                o.confirmed_at &&
                !o.prepare_at &&
                !o.ready_at &&
                !o.delivered_at &&
                !o.completed_at,
        ),
        emptyText: 'No confirmed orders',
    },
    {
        key: 'preparing',
        title: 'Preparing',
        icon: Package,
        color: 'yellow',
        orders: (confirmedOrders.value ?? []).filter(
            (o) => o.prepare_at && !o.ready_at,
        ),
        emptyText: 'No orders being prepared',
    },
    {
        key: 'ready',
        title: 'Ready',
        icon: Truck,
        color: 'purple',
        orders: (confirmedOrders.value ?? []).filter(
            (o) => (o.ready_at || o.delivered_at) && !o.completed_at,
        ),
        emptyText: 'No orders ready for delivery',
    },
    {
        key: 'completed',
        title: 'Completed',
        icon: CheckCircle,
        color: 'green',
        orders: (confirmedOrders.value ?? []).filter((o) => o.completed_at),
        emptyText: 'No completed orders',
    },
]);

const handleStatusUpdate = async (
    orderId: number,
    newStatus: OrderStatusEnum,
) => {
    updatedStatusOrderId.value = orderId;

    try {
        await orderStore.updateOrderStatus(orderId, newStatus);
        toast.success('Updated status order successfully!');
    } catch (error) {
        console.log(error);
        toast.error('Failed to update order status');
    } finally {
        updatedStatusOrderId.value = null;
    }
};

const refreshOrders = () => {
    orderStore.getConfirmedOrders();
};

const toggleColumn = (columnKey: string) => {
    if (expandedColumns.value.has(columnKey)) {
        expandedColumns.value.delete(columnKey);
    } else {
        expandedColumns.value.add(columnKey);
    }
};

const isColumnExpanded = (columnKey: string) => {
    return expandedColumns.value.has(columnKey);
};

onMounted(async () => {
    await orderStore.getConfirmedOrders();
});
</script>

<template>
    <Head title="Confirmed Orders" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-3 md:gap-6 md:p-4"
        >
            <!-- Header with title and refresh button -->
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1 class="text-xl font-bold tracking-tight md:text-2xl">
                        Confirmed Orders
                    </h1>
                    <p class="text-xs text-muted-foreground md:text-sm">
                        Manage and track orders by status
                    </p>
                </div>
                <button
                    @click="refreshOrders"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-sidebar-border/70 px-3 py-2 text-sm font-medium transition-colors hover:bg-sidebar-accent sm:w-auto"
                >
                    <RefreshCw :size="16" />
                    Refresh
                </button>
            </div>

            <!-- Mobile View (Accordion) -->
            <div class="block space-y-3 md:hidden">
                <div
                    v-for="column in columns"
                    :key="column.key"
                    class="overflow-hidden rounded-lg border border-sidebar-border/70 bg-card"
                >
                    <!-- Accordion Header -->
                    <button
                        @click="toggleColumn(column.key)"
                        class="w-full p-3 transition-colors hover:bg-sidebar-accent/50"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <component
                                    :is="column.icon"
                                    :size="18"
                                    :class="`text-${column.color}-600 dark:text-${column.color}-400`"
                                />
                                <h3
                                    :class="`font-semibold text-${column.color}-800 dark:text-${column.color}-800`"
                                >
                                    {{ column.title }}
                                </h3>
                                <span
                                    :class="`rounded-full text-xs font-medium text-${column.color}-800 dark:bg-${column.color}-800`"
                                >
                                    {{ column.orders.length }}
                                </span>
                            </div>
                            <component
                                :is="
                                    isColumnExpanded(column.key)
                                        ? ChevronUp
                                        : ChevronDown
                                "
                                :size="18"
                                class="text-muted-foreground"
                            />
                        </div>
                    </button>

                    <!-- Accordion Content -->
                    <div
                        v-show="isColumnExpanded(column.key)"
                        class="border-t border-sidebar-border/50 p-3"
                    >
                        <div class="flex flex-col gap-3">
                            <OrderCard
                                v-for="order in column.orders"
                                :key="order.id"
                                :order="order"
                                :update-status-order-id="updatedStatusOrderId"
                                @update-status="handleStatusUpdate"
                            />

                            <div
                                v-if="column.orders.length === 0"
                                class="rounded-lg border border-dashed border-sidebar-border/70 p-6 text-center"
                            >
                                <p class="text-sm text-muted-foreground">
                                    {{ column.emptyText }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desktop View (Kanban Board) -->
            <div
                class="hidden grid-cols-1 gap-4 overflow-x-auto md:grid md:grid-cols-4 lg:gap-6"
                style="min-width: 800px"
            >
                <div
                    v-for="column in columns"
                    :key="column.key"
                    class="flex flex-col gap-3"
                >
                    <!-- Header -->
                    <div
                        :class="`flex items-center justify-between rounded-lg p-3 bg-${column.color}-100 dark:bg-${column.color}-950/30`"
                    >
                        <div class="flex items-center gap-2">
                            <component
                                :is="column.icon"
                                :size="20"
                                :class="`text-${column.color}-600 dark:text-${column.color}-400`"
                            />
                            <h3 :class="`font-semibold text-gray-900`">
                                {{ column.title }}
                            </h3>
                            <span
                                :class="`rounded-full px-2 py-0.5 text-xs font-medium bg-${column.color}-200 text-${column.color}-800 dark:bg-${column.color}-800 dark:text-${column.color}-100`"
                            >
                                {{ column.orders.length }}
                            </span>
                        </div>
                    </div>

                    <!-- Orders -->
                    <div class="flex flex-col gap-3">
                        <OrderCard
                            v-for="order in column.orders"
                            :key="order.id"
                            :order="order"
                            :update-status-order-id="updatedStatusOrderId"
                            @update-status="handleStatusUpdate"
                        />

                        <div
                            v-if="column.orders.length === 0"
                            class="rounded-lg border border-dashed border-sidebar-border/70 p-8 text-center"
                        >
                            <p class="text-sm text-muted-foreground">
                                {{ column.emptyText }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped></style>
