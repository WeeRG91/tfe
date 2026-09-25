<script setup lang="ts">
import OrderCard from '@/components/admin/order/OrderCard.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import confirmedOrder from '@/routes/admin/confirmed-order';
import { useOrderStore } from '@/stores/order';
import { BreadcrumbItem } from '@/types';
import { OrderStatusEnum } from '@/types/order';
import { Head } from '@inertiajs/vue3';
import {
    CheckCircle,
    ChevronDown,
    ChevronUp,
    CircleX,
    Clock,
    Maximize2,
    Package,
    RefreshCw,
    Truck,
    X,
} from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';
import NotificationSoundToggle from '@/components/admin/NotificationSoundToggle.vue';

const orderStore = useOrderStore();
const { confirmedOrders } = storeToRefs(orderStore);

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('confirmedOrder.title'),
        href: confirmedOrder.index().url,
    },
]);

const updatedStatusOrderId = ref<number | null>(null);
const expandedColumns = ref<Set<string>>(new Set(['confirmed']));
const showCompletedModal = ref<boolean>(false);
const showCancelledModal = ref<boolean>(false);
const isCancelling = ref<boolean>(false);
const isDisplayMode = ref<boolean>(false);
const previousBodyOverFlow = ref<string>('');

const columns = computed(() => [
    {
        key: 'confirmed',
        title: t('confirmedOrder.columns.confirmed'),
        icon: Clock,
        headerClass: 'bg-info text-info-foreground',
        orders: (confirmedOrders.value ?? []).filter(
            (o) =>
                o.confirmed_at &&
                !o.prepare_at &&
                !o.ready_at &&
                !o.delivered_at &&
                !o.completed_at &&
                !o.cancelled_at,
        ),
        emptyText: t('confirmedOrder.emptyStates.confirmed'),
    },
    {
        key: 'preparing',
        title: t('confirmedOrder.columns.preparing'),
        icon: Package,
        headerClass: 'bg-warning text-warning-foreground',
        orders: (confirmedOrders.value ?? []).filter(
            (o) => o.prepare_at && !o.ready_at,
        ),
        emptyText: t('confirmedOrder.emptyStates.preparing'),
    },
    {
        key: 'ready',
        title: t('confirmedOrder.columns.ready'),
        icon: Truck,
        headerClass: 'bg-success text-success-foreground',
        orders: (confirmedOrders.value ?? []).filter(
            (o) =>
                (o.ready_at || o.delivered_at) &&
                !o.completed_at &&
                !o.cancelled_at,
        ),
        emptyText: t('confirmedOrder.emptyStates.ready'),
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
        toast.success(t('confirmedOrder.messages.updated'));
    } catch (error) {
        console.log(error);
        toast.error(t('confirmedOrder.errors.updateFailed'));
    } finally {
        updatedStatusOrderId.value = null;
    }
};

const handleCancelOrder = async (orderId: number) => {
    isCancelling.value = true;

    try {
        const response = await orderStore.cancelConfirmedOrder(orderId);

        if (response.success) {
            await orderStore.getConfirmedOrders();
        }

        toast.success(t('confirmedOrder.messages.cancelled'));
    } catch (error) {
        console.error(error);
        toast.error(t('confirmedOrder.errors.cancelFailed'));
    } finally {
        isCancelling.value = false;
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

const enterDisplayMode = async () => {
    if (isDisplayMode.value) {
        return;
    }

    previousBodyOverFlow.value = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    isDisplayMode.value = true;

    try {
        if (!document.fullscreenElement) {
            await document.documentElement.requestFullscreen();
        }
    } catch (error) {
        console.error('Unable to enter fullscreen mode:', error);
    }
};

const exitDisplayMode = async () => {
    if (!isDisplayMode.value && !document.fullscreenElement) {
        return;
    }

    document.body.style.overflow = previousBodyOverFlow.value;
    isDisplayMode.value = false;

    try {
        if (document.fullscreenElement) {
            await document.exitFullscreen();
        }
    } catch (error) {
        console.error('Unable to exit fullscreen mode:', error);
    }
};

const handleFullscreenChange = () => {
    if (!document.fullscreenElement && isDisplayMode.value) {
        document.body.style.overflow = previousBodyOverFlow.value;
        isDisplayMode.value = false;
    }
};

const handleDisplayModeKeydown = (event: KeyboardEvent) => {
    if (event.key !== 'Escape') {
        return;
    }

    if (showCompletedModal.value) {
        closeCompletedModal();
        return;
    }

    if (showCancelledModal.value) {
        closeCancelledModal();
        return;
    }

    exitDisplayMode();
};

onMounted(async () => {
    window.addEventListener('keydown', handleDisplayModeKeydown);
    document.addEventListener('fullscreenchange', handleFullscreenChange);

    await orderStore.getConfirmedOrders();
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleDisplayModeKeydown);
    document.removeEventListener('fullscreenchange', handleFullscreenChange);

    if (isDisplayMode.value) {
        document.body.style.overflow = previousBodyOverFlow.value;
    }
});
</script>

<template>
    <Head :title="t('confirmedOrder.title')" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-y-auto p-3 md:gap-6 md:p-4"
        >
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1 class="text-xl font-bold tracking-tight md:text-2xl">
                        {{ t('confirmedOrder.title') }}
                    </h1>
                    <p class="text-xs text-muted-foreground md:text-sm">
                        {{ t('confirmedOrder.subtitle') }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <div
                        class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row"
                    >
                        <button
                            @click="openCompletedModal"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-success px-3 py-2 text-sm font-medium text-success-foreground transition-colors hover:bg-success/90 sm:w-auto"
                        >
                            <CheckCircle :size="16" />
                            <span class="whitespace-nowrap">
                                {{
                                    t('confirmedOrder.buttons.completed', {
                                        count: completedOrders.length,
                                    })
                                }}
                            </span>
                        </button>
                        <button
                            @click="openCancelledModal"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-destructive px-3 py-2 text-sm font-medium text-destructive-foreground transition-colors hover:bg-destructive/90 sm:w-auto"
                        >
                            <CircleX :size="16" />
                            <span class="whitespace-nowrap">
                                {{
                                    t('confirmedOrder.buttons.cancelled', {
                                        count: cancelledOrders.length,
                                    })
                                }}
                            </span>
                        </button>
                        <button
                            @click="refreshOrders"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-border bg-card px-3 py-2 text-sm font-medium text-card-foreground transition-colors hover:bg-accent hover:text-accent-foreground sm:w-auto"
                        >
                            <RefreshCw :size="16" />
                            {{ t('confirmedOrder.buttons.refresh') }}
                        </button>
                        <NotificationSoundToggle />
                    </div>
                </div>
            </div>

            <div class="block space-y-3 md:hidden">
                <div
                    v-for="column in columns"
                    :key="column.key"
                    class="overflow-hidden rounded-lg border border-border bg-card"
                >
                    <button
                        type="button"
                        @click="toggleColumn(column.key)"
                        class="w-full p-3 text-left focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        :class="column.headerClass"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <component
                                    :is="column.icon"
                                    :size="18"
                                    class="shrink-0"
                                />
                                <h3 class="font-semibold">
                                    {{ column.title }}
                                </h3>
                                <span
                                    class="rounded-full border border-current px-2 py-0.5 text-xs font-medium"
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
                                class="text-current"
                            />
                        </div>
                    </button>

                    <div
                        v-show="isColumnExpanded(column.key)"
                        class="border-t border-border p-3"
                    >
                        <div class="flex flex-col gap-3">
                            <OrderCard
                                v-for="order in column.orders"
                                :key="order.id"
                                :order="order"
                                :update-status-order-id="updatedStatusOrderId"
                                :is-cancelling="isCancelling"
                                @cancel-order="handleCancelOrder"
                                @update-status="handleStatusUpdate"
                            />

                            <div
                                v-if="column.orders.length === 0"
                                class="rounded-lg border border-dashed border-border bg-muted p-6 text-center"
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
                :class="[
                    'group/order-board',
                    isDisplayMode
                        ? 'fixed inset-0 z-40 flex flex-col gap-4 bg-background p-4'
                        : 'relative',
                ]"
            >
                <div
                    :class="[
                        'min-h-0 flex-1 grid-cols-1 gap-4 overflow-y-auto md:grid-cols-3 lg:gap-6',
                        isDisplayMode ? 'grid' : 'hidden md:grid',
                    ]"
                    :style="isDisplayMode ? undefined : { minWidth: '600px' }"
                >
                    <div
                        v-for="column in columns"
                        :key="column.key"
                        class="flex flex-col gap-3"
                    >
                        <div
                            class="flex items-center justify-between rounded-lg p-3"
                            :class="column.headerClass"
                        >
                            <div class="flex items-center gap-2">
                                <component
                                    :is="column.icon"
                                    :size="20"
                                    class="shrink-0"
                                />
                                <h3 class="font-semibold">
                                    {{ column.title }}
                                </h3>
                                <span
                                    class="rounded-full border border-current px-2 py-0.5 text-xs font-medium"
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
                                :is-cancelling="isCancelling"
                                @cancel-order="handleCancelOrder"
                                @update-status="handleStatusUpdate"
                            />

                            <div
                                v-if="column.orders.length === 0"
                                class="rounded-lg border border-dashed border-border bg-muted p-8 text-center"
                            >
                                <p class="text-sm text-muted-foreground">
                                    {{ column.emptyText }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <button
                    v-if="!isDisplayMode"
                    type="button"
                    @click="enterDisplayMode"
                    :aria-label="t('confirmedOrder.buttons.enterDisplayMode')"
                    :title="t('confirmedOrder.buttons.enterDisplayMode')"
                    class="pointer-events-none fixed right-15 bottom-15 z-20 hidden translate-y-2 items-center justify-center gap-2 rounded-full bg-primary p-4 font-medium text-primary-foreground opacity-0 shadow-xl transition-all duration-200 group-hover/order-board:pointer-events-auto group-hover/order-board:translate-y-0 group-hover/order-board:opacity-100 focus-visible:pointer-events-auto focus-visible:translate-y-0 focus-visible:opacity-100 md:inline-flex"
                >
                    <Maximize2 :size="20" />
                </button>
            </div>
        </div>

        <div
            v-if="showCompletedModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
            @click.self="closeCompletedModal"
        >
            <div
                class="relative mx-2 w-full max-w-4xl overflow-hidden rounded-lg border border-border bg-card text-card-foreground shadow-xl"
            >
                <div
                    class="sticky top-0 z-10 flex items-center justify-between rounded-t-lg border-b border-border bg-card px-6 py-4"
                >
                    <div class="flex items-center gap-3">
                        <CheckCircle :size="24" class="text-success" />
                        <div>
                            <h2
                                class="text-xl font-semibold text-card-foreground"
                            >
                                {{ t('confirmedOrder.modals.completedTitle') }}
                            </h2>
                            <p
                                class="flex items-center gap-2 text-sm text-muted-foreground"
                            >
                                <span>
                                    {{
                                        t('confirmedOrder.modals.orderCount', {
                                            count: completedOrders.length,
                                        })
                                    }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <button
                        @click="closeCompletedModal"
                        class="rounded-lg p-1 text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                    >
                        <X :size="20" />
                    </button>
                </div>

                <div class="max-h-[calc(100vh-8rem)] overflow-y-auto p-6">
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
                            class="rounded-lg border border-dashed border-border bg-muted p-8 text-center"
                        >
                            <p class="text-sm text-muted-foreground">
                                {{ t('confirmedOrder.emptyStates.completed') }}
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
                class="relative mx-2 w-full max-w-4xl overflow-hidden rounded-lg border border-border bg-card text-card-foreground shadow-xl"
            >
                <div
                    class="sticky top-0 z-10 flex items-center justify-between rounded-t-lg border-b border-border bg-card px-6 py-4"
                >
                    <div class="flex items-center gap-3">
                        <CircleX :size="24" class="text-destructive" />
                        <div>
                            <h2
                                class="text-xl font-semibold text-card-foreground"
                            >
                                {{ t('confirmedOrder.modals.cancelledTitle') }}
                            </h2>
                            <p
                                class="flex items-center gap-2 text-sm text-muted-foreground"
                            >
                                <span>
                                    {{
                                        t('confirmedOrder.modals.orderCount', {
                                            count: cancelledOrders.length,
                                        })
                                    }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <button
                        @click="closeCancelledModal"
                        class="rounded-lg p-1 text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                    >
                        <X :size="20" />
                    </button>
                </div>

                <div class="max-h-[calc(100vh-8rem)] overflow-y-auto p-6">
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
                                {{ t('confirmedOrder.emptyStates.cancelled') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: var(--border);
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: var(--muted-foreground);
}
</style>
