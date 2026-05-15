<script setup lang="ts">
import { formatPrice } from '@/lib/utils';
import order from '@/routes/order';
import { OrderType } from '@/types/order';
import { router } from '@inertiajs/vue3';
import {
    AlertCircle,
    CheckCircle,
    ChevronDown,
    Clock,
    CreditCard,
    MessageCircle,
    NotebookText,
    PackageOpen,
    Phone,
    Repeat,
    ShoppingBag,
    Truck,
    User,
    Utensils,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import OrderItemsList from './OrderItemsList.vue';

const props = defineProps<{
    orderToShow: OrderType;
}>();

const expandedOrderId = ref<number | null>(null);
const cancelLoadingOrderId = ref<number | null>(null);
const reorderLoadingOrderId = ref<number | null>(null);

const isExpanded = computed(
    () => expandedOrderId.value === props.orderToShow.id,
);

const toggleDetails = () => {
    if (isExpanded.value) {
        expandedOrderId.value = null;
    } else {
        expandedOrderId.value = props.orderToShow.id;
    }
};

const cancelOrder = async () => {
    // Implement cancel logic
};

const reorder = async () => {
    // Implement reorder logic
};

const removeOrder = async () => {
    // Implement remove logic
};

const getStatusInfo = (status: string | undefined) => {
    const statusKey = status?.toLowerCase() || '';

    if (statusKey === 'pending') {
        return {
            color: 'bg-amber-50 text-amber-700 border-amber-200',
            icon: Clock,
            label: 'Pending',
            progress: 25,
        };
    }
    if (statusKey === 'confirmed') {
        return {
            color: 'bg-blue-50 text-blue-700 border-blue-200',
            icon: CheckCircle,
            label: 'Confirmed',
            progress: 40,
        };
    }
    if (statusKey === 'preparing') {
        return {
            color: 'bg-indigo-50 text-indigo-700 border-indigo-200',
            icon: Clock,
            label: 'Preparing',
            progress: 60,
        };
    }
    if (statusKey === 'ready') {
        return {
            color: 'bg-purple-50 text-purple-700 border-purple-200',
            icon: PackageOpen,
            label: 'Ready for Pickup',
            progress: 80,
        };
    }
    if (statusKey === 'delivering') {
        return {
            color: 'bg-orange-50 text-orange-700 border-orange-200',
            icon: Truck,
            label: 'Out for Delivery',
            progress: 85,
        };
    }
    if (statusKey === 'completed') {
        return {
            color: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            icon: CheckCircle,
            label: 'Completed',
            progress: 100,
        };
    }
    if (statusKey === 'cancelled') {
        return {
            color: 'bg-red-50 text-red-700 border-red-200',
            icon: XCircle,
            label: 'Cancelled',
            progress: 0,
        };
    }

    return {
        color: 'bg-gray-50 text-gray-700 border-gray-200',
        icon: ShoppingBag,
        label: status || 'Unknown',
        progress: 0,
    };
};

const getOrderTypeIcon = (type: string | undefined) => {
    const typeKey = type?.toLowerCase() || '';
    if (typeKey === 'dine-in') return Utensils;
    if (typeKey === 'takeaway') return ShoppingBag;
    if (typeKey === 'delivery') return Truck;
    return ShoppingBag;
};

const formatDate = (dateString: string | undefined) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now.getTime() - date.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) {
        return `Today at ${date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}`;
    } else if (diffDays === 1) {
        return `Yesterday at ${date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}`;
    } else if (diffDays < 7) {
        return date.toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
        });
    }

    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getOrderTypeLabel = (type: string | undefined) => {
    const typeKey = type?.toLowerCase() || '';
    if (typeKey === 'dine-in') return 'Dine In';
    if (typeKey === 'takeaway') return 'Takeaway';
    if (typeKey === 'delivery') return 'Delivery';
    return type || 'Standard';
};

const getPaymentStatusInfo = (paymentStatus: string | undefined) => {
    const status = paymentStatus?.toLowerCase() || '';
    if (status === 'pending') {
        return {
            color: 'text-amber-600 bg-amber-50',
            label: 'Pending',
            icon: AlertCircle,
        };
    }
    if (status === 'paid') {
        return {
            color: 'text-emerald-600 bg-emerald-50',
            label: 'Paid',
            icon: CheckCircle,
        };
    }
    if (status === 'failed') {
        return {
            color: 'text-red-600 bg-red-50',
            label: 'Failed',
            icon: XCircle,
        };
    }
    return {
        color: 'text-gray-600 bg-gray-50',
        label: paymentStatus || 'Unknown',
        icon: CreditCard,
    };
};
</script>

<template>
    <div
        class="rounded-lg border bg-white shadow-sm transition-all hover:shadow-md"
    >
        <!-- Order Header -->
        <div
            @click="toggleDetails"
            class="cursor-pointer p-4 transition-colors hover:bg-gray-50"
        >
            <div class="flex flex-col gap-3">
                <div class="flex items-start justify-between">
                    <div class="flex flex-1 items-center gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100"
                        >
                            <component
                                :is="getOrderTypeIcon(orderToShow.type.label)"
                                class="h-5 w-5 text-gray-600"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold text-gray-800">
                                    #{{ orderToShow.order_number }}
                                </p>
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium',
                                        getStatusInfo(orderToShow.status?.label)
                                            .color,
                                    ]"
                                >
                                    <component
                                        :is="
                                            getStatusInfo(
                                                orderToShow.status?.label,
                                            ).icon
                                        "
                                        class="h-3 w-3"
                                    />
                                    {{
                                        getStatusInfo(orderToShow.status?.label)
                                            .label
                                    }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500">
                                {{ formatDate(orderToShow.created_at) }}
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-xs text-gray-500">Total</p>
                        <p class="text-lg font-bold text-red-500">
                            €{{ formatPrice(orderToShow.total_inc_vat) }}
                        </p>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mt-2">
                    <div
                        class="mb-1 flex justify-between text-xs text-gray-600"
                    >
                        <span>Order Progress</span>
                        <span
                            >{{
                                getStatusInfo(orderToShow.status?.label)
                                    .progress
                            }}%</span
                        >
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-gray-100">
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-amber-500 to-red-500 transition-all duration-500"
                            :style="{
                                width: `${getStatusInfo(orderToShow.status?.label).progress}%`,
                            }"
                        ></div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <component
                            :is="getOrderTypeIcon(orderToShow.type.label)"
                            class="h-3 w-3"
                        />
                        <span>{{
                            getOrderTypeLabel(orderToShow.type?.label)
                        }}</span>
                    </div>
                    <ChevronDown
                        class="h-4 w-4 text-gray-400 transition-transform duration-200"
                        :class="{ 'rotate-180': isExpanded }"
                    />
                </div>
            </div>
        </div>

        <!-- Order Details -->
        <div v-show="isExpanded" class="border-t border-gray-100">
            <div class="space-y-4 bg-gray-50 p-4">
                <!-- Order Info Grid -->
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="flex items-start gap-2 rounded-lg bg-white p-3">
                        <User class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" />
                        <div>
                            <p
                                class="text-xs font-semibold text-gray-500 uppercase"
                            >
                                Customer
                            </p>
                            <p class="text-sm font-medium text-gray-800">
                                {{ orderToShow.user?.name || 'Guest' }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ orderToShow.user?.email }}
                            </p>
                            <p
                                v-if="orderToShow.pickup_phone"
                                class="flex items-center gap-1 text-xs text-gray-500"
                            >
                                <Phone class="h-3 w-3" />
                                {{ orderToShow.pickup_phone }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-2 rounded-lg bg-white p-3">
                        <CreditCard
                            class="mt-0.5 h-4 w-4 shrink-0 text-purple-500"
                        />
                        <div>
                            <p
                                class="text-xs font-semibold text-gray-500 uppercase"
                            >
                                Payment
                            </p>
                            <p class="text-sm text-gray-700">
                                {{ orderToShow.payment_method?.label }}
                            </p>
                            <span
                                :class="[
                                    'mt-1 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium',
                                    getPaymentStatusInfo(
                                        orderToShow.payment_status.label,
                                    ).color,
                                ]"
                            >
                                <component
                                    :is="
                                        getPaymentStatusInfo(
                                            orderToShow.payment_status.label,
                                        ).icon
                                    "
                                    class="h-3 w-3"
                                />
                                {{
                                    getPaymentStatusInfo(
                                        orderToShow.payment_status.label,
                                    ).label
                                }}
                            </span>
                        </div>
                    </div>

                    <div v-if="orderToShow.notes" class="sm:col-span-2">
                        <div
                            class="flex items-start gap-2 rounded-lg bg-amber-50 p-3"
                        >
                            <MessageCircle
                                class="mt-0.5 h-4 w-4 shrink-0 text-amber-600"
                            />
                            <div>
                                <p
                                    class="text-xs font-semibold text-amber-700 uppercase"
                                >
                                    Special Instructions
                                </p>
                                <p class="text-sm text-gray-600 italic">
                                    "{{ orderToShow.notes }}"
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <OrderItemsList :order="orderToShow" />

                <!-- Action Buttons -->
                <div
                    class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"
                >
                    <button
                        @click.stop="
                            router.visit(order.orderDetails(orderToShow.id).url)
                        "
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-all hover:bg-red-50 disabled:opacity-50 sm:w-40"
                    >
                        <NotebookText class="h-4 w-4" />
                        <span>See Details</span>
                    </button>

                    <button
                        v-if="
                            orderToShow.status?.label !== 'Cancelled' &&
                            orderToShow.status?.label !== 'Completed'
                        "
                        @click="cancelOrder"
                        :disabled="cancelLoadingOrderId === orderToShow.id"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-all hover:bg-red-50 disabled:opacity-50 sm:w-40"
                    >
                        <XCircle class="h-4 w-4" />
                        <span v-if="cancelLoadingOrderId === orderToShow.id"
                            >Cancelling...</span
                        >
                        <span v-else>Cancel Order</span>
                    </button>

                    <button
                        v-if="
                            orderToShow.status?.label === 'Completed' ||
                            orderToShow.status?.label === 'Cancelled'
                        "
                        @click="removeOrder"
                        :disabled="cancelLoadingOrderId === orderToShow.id"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-all hover:bg-red-50 disabled:opacity-50 sm:w-40"
                    >
                        <XCircle class="h-4 w-4" />
                        <span v-if="cancelLoadingOrderId === orderToShow.id"
                            >Removing...</span
                        >
                        <span v-else>Remove Order</span>
                    </button>

                    <button
                        @click="reorder"
                        :disabled="reorderLoadingOrderId === orderToShow.id"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-500 px-5 py-2 text-sm font-semibold text-white transition-all hover:bg-red-600 disabled:opacity-50 sm:w-40"
                    >
                        <Repeat class="h-4 w-4" />
                        <span v-if="reorderLoadingOrderId === orderToShow.id"
                            >Adding to Cart...</span
                        >
                        <span v-else>Reorder Now</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
