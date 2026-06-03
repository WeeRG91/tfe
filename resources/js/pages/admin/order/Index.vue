<script setup lang="ts">
import OrderCard from '@/components/admin/order/OrderCard.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { useOrderStore } from '@/stores/order';
import { BreadcrumbItem } from '@/types';
import { OrderStatusEnum } from '@/types/order';
import { Head } from '@inertiajs/vue3';
import { CheckCircle, Clock, Package, RefreshCw, Truck } from 'lucide-vue-next';
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

window.Echo.channel('orders').listen('.order.placed', () => {
    orderStore.getConfirmedOrders();
});

onMounted(() => {
    orderStore.getConfirmedOrders();
});
</script>

<template>
    <Head title="Confirmed Orders" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
            <!-- Header with title and refresh button -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Confirmed Orders
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Manage and track orders by status
                    </p>
                </div>
                <button
                    @click="refreshOrders"
                    class="inline-flex items-center gap-2 rounded-lg border border-sidebar-border/70 px-4 py-2 text-sm font-medium transition-colors hover:bg-sidebar-accent"
                >
                    <RefreshCw :size="16" />
                    Refresh
                </button>
            </div>

            <!-- Kanban Board Columns -->
            <div
                class="grid grid-cols-1 gap-4 overflow-x-auto md:grid-cols-4"
                style="min-width: 800px"
            >
                <div
                    v-for="column in columns"
                    :key="column.key"
                    class="flex flex-col gap-3"
                >
                    <!-- Header -->
                    <div
                        :class="`flex items-center justify-between rounded-lg p-3 bg-${column.color}-50 dark:bg-${column.color}-950/30`"
                    >
                        <div class="flex items-center gap-2">
                            <component
                                :is="column.icon"
                                :size="20"
                                :class="`text-${column.color}-600 dark:text-${column.color}-400`"
                            />
                            <h3
                                :class="`font-semibold text-${column.color}-900 dark:text-${column.color}-100`"
                            >
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
