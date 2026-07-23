<script setup lang="ts">
import OrderCard from '@/components/admin/order/OrderCard.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { useOrderStore } from '@/stores/order';
import { BreadcrumbItem } from '@/types';
import { OrderStatusEnum } from '@/types/order';
import { Head } from '@inertiajs/vue3';
import {
    CheckCircle,
    Clock,
    Package,
    RefreshCw,
    Truck,
    X,
    CircleX,
    ChevronUp,
    ChevronDown,
} from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';
import confirmedOrder from '@/routes/admin/confirmed-order';

const orderStore = useOrderStore();
const { confirmedOrders } = storeToRefs(orderStore);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Confirmed Orders', href: confirmedOrder.index().url },
];

const updatedStatusOrderId = ref<number | null>(null);
const expandedColumns = ref<Set<string>>(new Set(['confirmed']));
const showCompletedModal = ref(false);
const showCancelledModal = ref(false);

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
                !o.completed_at &&
                !o.cancelled_at,
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
            (o) =>
                (o.ready_at || o.delivered_at) &&
                !o.completed_at &&
                !o.cancelled_at,
        ),
        emptyText: 'No orders ready for delivery',
    },
]);

const completedOrders = computed(() => {
    return (confirmedOrders.value ?? []).filter((o) => o.completed_at);
});

const cancelledOrders = computed(() => {
    return (confirmedOrders.value ?? []).filter((o) => o.cancelled_at);
});

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

const openCompletedModal = () => {
    showCompletedModal.value = true;
};

const openCancelledModal = () => {
    showCancelledModal.value = true;
};

const closeCompletedModal = () => {
    showCompletedModal.value = false;
};

const closeCancelledModal = () => {
    showCancelledModal.value = false;
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
                <div class="flex flex-wrap gap-2">
                    <div
                        class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row"
                    >
                        <button
                            @click="openCompletedModal"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-green-600 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-green-700 sm:w-auto"
                        >
                            <CheckCircle :size="16" />
                            <span class="whitespace-nowrap"
                                >Completed ({{ completedOrders.length }})</span
                            >
                        </button>
                        <button
                            @click="openCancelledModal"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 sm:w-auto"
                        >
                            <CircleX :size="16" />
                            <span class="whitespace-nowrap"
                                >Cancelled ({{ cancelledOrders.length }})</span
                            >
                        </button>
                        <button
                            @click="refreshOrders"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-sidebar-border/70 px-3 py-2 text-sm font-medium transition-colors hover:bg-sidebar-accent sm:w-auto"
                        >
                            <RefreshCw :size="16" />
                            Refresh
                        </button>
                    </div>
                </div>
            </div>

            <div class="block space-y-3 md:hidden">
                <div
                    v-for="column in columns"
                    :key="column.key"
                    class="overflow-hidden rounded-lg border border-sidebar-border/70 bg-card"
                >
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

            <div
                class="hidden grid-cols-1 gap-4 overflow-x-auto md:grid md:grid-cols-3 lg:gap-6"
                style="min-width: 600px"
            >
                <div
                    v-for="column in columns"
                    :key="column.key"
                    class="flex flex-col gap-3"
                >
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

        <div
            v-if="showCompletedModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
            @click.self="closeCompletedModal"
        >
            <div
                class="relative mx-2 w-full max-w-4xl rounded-lg bg-white shadow-xl dark:bg-gray-900"
            >
                <div
                    class="sticky top-0 z-10 flex items-center justify-between rounded-t-lg border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-900"
                >
                    <div class="flex items-center gap-3">
                        <CheckCircle :size="24" class="text-green-600" />
                        <div>
                            <h2
                                class="text-xl font-semibold text-gray-900 dark:text-white"
                            >
                                Completed Orders
                            </h2>
                            <p
                                class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
                            >
                                <span>
                                    {{ completedOrders.length }} order{{
                                        completedOrders.length !== 1 ? 's' : ''
                                    }}
                                </span>
                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800 dark:bg-green-900 dark:text-green-100"
                                >
                                    <CheckCircle :size="14" />
                                    Completed
                                </span>
                            </p>
                        </div>
                    </div>
                    <button
                        @click="closeCompletedModal"
                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800"
                    >
                        <X :size="20" />
                    </button>
                </div>

                <div
                    class="modal-scroll max-h-[calc(100vh-8rem)] overflow-y-auto p-6"
                >
                    <div class="flex flex-col gap-3">
                        <OrderCard
                            v-for="order in completedOrders"
                            :key="order.id"
                            :order="order"
                            :update-status-order-id="updatedStatusOrderId"
                            @update-status="handleStatusUpdate"
                        />
                        <div
                            v-if="completedOrders.length === 0"
                            class="rounded-lg border border-dashed border-sidebar-border/70 p-8 text-center"
                        >
                            <p class="text-sm text-muted-foreground">
                                No completed orders
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="showCancelledModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
            @click.self="closeCancelledModal"
        >
            <div
                class="relative mx-2 w-full max-w-4xl rounded-lg bg-white shadow-xl dark:bg-gray-900"
            >
                <div
                    class="sticky top-0 z-10 flex items-center justify-between rounded-t-lg border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-900"
                >
                    <div class="flex items-center gap-3">
                        <CheckCircle :size="24" class="text-green-600" />
                        <div>
                            <h2
                                class="text-xl font-semibold text-gray-900 dark:text-white"
                            >
                                Completed Orders
                            </h2>
                            <p
                                class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
                            >
                                <span>
                                    {{ cancelledOrders.length }} order{{
                                        cancelledOrders.length !== 1 ? 's' : ''
                                    }}
                                </span>
                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800 dark:bg-green-900 dark:text-green-100"
                                >
                                    <CheckCircle :size="14" />
                                    Completed
                                </span>
                            </p>
                        </div>
                    </div>
                    <button
                        @click="closeCancelledModal"
                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800"
                    >
                        <X :size="20" />
                    </button>
                </div>

                <div
                    class="modal-scroll max-h-[calc(100vh-8rem)] overflow-y-auto p-6"
                >
                    <div class="flex flex-col gap-3">
                        <OrderCard
                            v-for="order in cancelledOrders"
                            :key="order.id"
                            :order="order"
                            :update-status-order-id="updatedStatusOrderId"
                            @update-status="handleStatusUpdate"
                        />
                        <div
                            v-if="cancelledOrders.length === 0"
                            class="rounded-lg border border-dashed border-sidebar-border/70 p-8 text-center"
                        >
                            <p class="text-sm text-muted-foreground">
                                No completed orders
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.modal-scroll {
    overflow-y: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.modal-scroll::-webkit-scrollbar {
    display: none;
}
</style>
