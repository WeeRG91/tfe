<script setup lang="ts">
import { useOrderStatusActionFlow } from '@/composables/useOrderStatusActionFlow';
import {
    formatAddress,
    formatDate,
    formatDateShort,
    formatTime,
    getOrderStatusIcon,
    getOrderStatusVariant,
    getOrderTypeIcon,
    getPaymentMethodIcon,
    getPaymentStatusIcon,
    getPaymentStatusVariant,
} from '@/lib/utils';
import { OrderStatusEnum, OrderType } from '@/types/order';
import {
    AlertCircle,
    MapPin,
    User,
    UserCircle,
    Utensils,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps<{
    order: OrderType | null;
    isOpen: boolean;
}>();

const emit = defineEmits<{
    close: [];
    'update-status': [orderId: number, newStatus: OrderStatusEnum];
}>();

const orderStatusActionId = ref<string>('');

const handleUpdateStatus = (newStatus: OrderStatusEnum, actionId: string) => {
    orderStatusActionId.value = actionId;
    if (props.order) {
        emit('update-status', props.order.id, newStatus);
    }
};

const { getOrderStatusActions } = useOrderStatusActionFlow(handleUpdateStatus);

const orderStatusActions = computed(
    () =>
        props.order &&
        getOrderStatusActions(props.order.status.value, props.order.type.value),
);

const handleClose = () => {
    emit('close');
};

const closeOnBackdrop = (e: MouseEvent) => {
    if (e.target === e.currentTarget) {
        handleClose();
    }
};

onMounted(() => {
    if (props.isOpen) {
        document.body.style.overflow = 'hidden';
        orderStatusActionId.value = '';
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
});

watch(
    () => props.isOpen,
    (isOpen) => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    },
);
</script>

<template>
    <div
        v-if="isOpen && order"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 backdrop-blur-sm"
        @click="closeOnBackdrop"
    >
        <div
            class="relative mx-2 w-full max-w-4xl rounded-lg bg-white shadow-xl dark:bg-gray-900"
        >
            <div
                class="sticky top-0 z-10 flex items-center justify-between rounded-t-lg border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-900"
            >
                <div class="flex items-center gap-3">
                    <component
                        :is="getOrderTypeIcon(order.type.value)"
                        :size="24"
                        class="text-gray-600 dark:text-gray-400"
                    />
                    <div>
                        <h2
                            class="text-xl font-semibold text-gray-900 dark:text-white"
                        >
                            Order #{{ order.order_number }}
                        </h2>
                        <p
                            class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
                        >
                            <span>
                                {{ order.type.label }} •
                                {{ formatDateShort(order.created_at) }}
                            </span>

                            <span
                                class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium"
                                :class="
                                    getOrderStatusVariant(order.status.value)
                                "
                            >
                                <component
                                    :is="getOrderStatusIcon(order.status.value)"
                                    :size="14"
                                />
                                {{ order.status.label }}
                            </span>
                        </p>
                    </div>
                </div>
                <button
                    @click="handleClose"
                    class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800"
                >
                    <X :size="20" />
                </button>
            </div>

            <div class="max-h-[calc(100vh-8rem)] overflow-y-auto p-6">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="space-y-6 lg:col-span-2">
                        <div
                            class="rounded-lg border border-gray-200 dark:border-gray-700"
                        >
                            <div
                                class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
                            >
                                <h3
                                    class="font-semibold text-gray-900 dark:text-white"
                                >
                                    Order Items
                                </h3>
                            </div>
                            <div
                                class="divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="p-4"
                                >
                                    <div class="flex justify-between">
                                        <div class="flex-1">
                                            <div
                                                class="flex items-start justify-between"
                                            >
                                                <div>
                                                    <span
                                                        class="font-medium text-gray-900 dark:text-white"
                                                    >
                                                        {{ item.quantity }}x
                                                        {{ item.item.name }}
                                                    </span>
                                                    <p
                                                        v-if="item.notes"
                                                        class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                                    >
                                                        Note: {{ item.notes }}
                                                    </p>
                                                </div>
                                                <span
                                                    class="font-medium text-gray-900 dark:text-white"
                                                >
                                                    €{{
                                                        item.total_inc_vat.toFixed(
                                                            2,
                                                        )
                                                    }}
                                                </span>
                                            </div>

                                            <div
                                                class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                Spicy level: {{ ['No spicy', 'Mild', 'Spicy', 'Hot'][item.spicy_level] }}
                                            </div>

                                            <div
                                                v-if="item.meat"
                                                class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                Meat: {{ item.meat.name }}
                                            </div>

                                            <div
                                                v-if="
                                                    item.removed_ingredients
                                                        ?.length
                                                "
                                                class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                Removed:
                                                {{
                                                    item.removed_ingredients
                                                        .map((i) => i.name)
                                                        .join(', ')
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-lg border border-gray-200 dark:border-gray-700"
                        >
                            <div
                                class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
                            >
                                <h3
                                    class="font-semibold text-gray-900 dark:text-white"
                                >
                                    Order Timeline
                                </h3>
                            </div>
                            <div class="space-y-3 p-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="mt-2 h-2 w-2 rounded-full bg-green-500"
                                        ></div>
                                    </div>
                                    <div class="flex-1">
                                        <p
                                            class="text-sm text-gray-900 dark:text-white"
                                        >
                                            Order Created
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ formatDate(order.created_at) }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="order.confirmed_at"
                                    class="flex items-start gap-3"
                                >
                                    <div class="flex-shrink-0">
                                        <div
                                            class="mt-2 h-2 w-2 rounded-full bg-blue-500"
                                        ></div>
                                    </div>
                                    <div class="flex-1">
                                        <p
                                            class="text-sm text-gray-900 dark:text-white"
                                        >
                                            Order Confirmed
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ formatDate(order.confirmed_at) }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="order.prepare_at"
                                    class="flex items-start gap-3"
                                >
                                    <div class="flex-shrink-0">
                                        <div
                                            class="mt-2 h-2 w-2 rounded-full bg-yellow-500"
                                        ></div>
                                    </div>
                                    <div class="flex-1">
                                        <p
                                            class="text-sm text-gray-900 dark:text-white"
                                        >
                                            Preparation Started
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ formatDate(order.prepare_at) }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="order.ready_at"
                                    class="flex items-start gap-3"
                                >
                                    <div class="flex-shrink-0">
                                        <div
                                            class="mt-2 h-2 w-2 rounded-full bg-purple-500"
                                        ></div>
                                    </div>
                                    <div class="flex-1">
                                        <p
                                            class="text-sm text-gray-900 dark:text-white"
                                        >
                                            Order Ready
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ formatDate(order.ready_at) }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="order.delivered_at"
                                    class="flex items-start gap-3"
                                >
                                    <div class="flex-shrink-0">
                                        <div
                                            class="mt-2 h-2 w-2 rounded-full bg-indigo-500"
                                        ></div>
                                    </div>
                                    <div class="flex-1">
                                        <p
                                            class="text-sm text-gray-900 dark:text-white"
                                        >
                                            Delivering
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ formatDate(order.delivered_at) }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="order.completed_at"
                                    class="flex items-start gap-3"
                                >
                                    <div class="flex-shrink-0">
                                        <div
                                            class="mt-2 h-2 w-2 rounded-full bg-green-600"
                                        ></div>
                                    </div>
                                    <div class="flex-1">
                                        <p
                                            class="text-sm text-gray-900 dark:text-white"
                                        >
                                            Order Completed
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ formatDate(order.completed_at) }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="order.cancelled_at"
                                    class="flex items-start gap-3"
                                >
                                    <div class="flex-shrink-0">
                                        <div
                                            class="mt-2 h-2 w-2 rounded-full bg-red-500"
                                        ></div>
                                    </div>
                                    <div class="flex-1">
                                        <p
                                            class="text-sm text-gray-900 dark:text-white"
                                        >
                                            Order Cancelled
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ formatDate(order.cancelled_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div
                            class="rounded-lg border border-gray-200 dark:border-gray-700"
                        >
                            <div
                                class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
                            >
                                <h3
                                    class="font-semibold text-gray-900 dark:text-white"
                                >
                                    Customer Information
                                </h3>
                            </div>
                            <div class="space-y-3 p-4">
                                <div class="flex items-start gap-2">
                                    <User
                                        :size="16"
                                        class="mt-0.5 text-gray-400"
                                    />
                                    <div>
                                        <p
                                            class="text-sm font-medium text-gray-900 dark:text-white"
                                        >
                                            {{ order.user.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ order.user.email }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="order.type.label === 'Delivery'"
                                    class="flex items-start gap-2"
                                >
                                    <MapPin
                                        :size="16"
                                        class="mt-0.5 text-gray-400"
                                    />
                                    <div>
                                        <p
                                            class="text-sm text-gray-900 dark:text-white"
                                        >
                                            Delivery Address
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{
                                                formatAddress(
                                                    order.delivery_address,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="order.type.label === 'Takeaway'"
                                    class="flex items-start gap-2"
                                >
                                    <UserCircle
                                        :size="16"
                                        class="mt-0.5 text-gray-400"
                                    />
                                    <div>
                                        <p
                                            class="text-sm text-gray-900 dark:text-white"
                                        >
                                            Pickup Details
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ order.pickup_name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ order.pickup_phone }}
                                        </p>
                                        <p
                                            v-if="order.pickup_time"
                                            class="text-xs text-gray-500"
                                        >
                                            Pickup by:
                                            {{ formatTime(order.pickup_time) }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="order.type.label === 'Dine-in'"
                                    class="flex items-start gap-2"
                                >
                                    <Utensils
                                        :size="16"
                                        class="mt-0.5 text-gray-400"
                                    />
                                    <div>
                                        <p
                                            class="text-sm text-gray-900 dark:text-white"
                                        >
                                            Table Number
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Table {{ order.table_number }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="order.notes"
                                    class="flex items-start gap-2"
                                >
                                    <AlertCircle
                                        :size="16"
                                        class="mt-0.5 text-gray-400"
                                    />
                                    <div>
                                        <p
                                            class="text-sm text-gray-900 dark:text-white"
                                        >
                                            Order Notes
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ order.notes }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-lg border border-gray-200 dark:border-gray-700"
                        >
                            <div
                                class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
                            >
                                <h3
                                    class="font-semibold text-gray-900 dark:text-white"
                                >
                                    Payment Information
                                </h3>
                            </div>
                            <div class="space-y-3 p-4">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                        >Payment Method</span
                                    >
                                    <span
                                        class="flex items-center gap-1 text-sm font-medium text-gray-900 dark:text-white"
                                    >
                                        <component
                                            :is="
                                                getPaymentMethodIcon(
                                                    order.payment_method.value,
                                                )
                                            "
                                            :size="14"
                                        />

                                        {{ order.payment_method.label }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                        >Payment Status</span
                                    >
                                    <span
                                        class="inline-flex gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="
                                            getPaymentStatusVariant(
                                                order.payment_status.value,
                                            )
                                        "
                                    >
                                        <component
                                            :is="
                                                getPaymentStatusIcon(
                                                    order.payment_method.value,
                                                )
                                            "
                                            :size="14"
                                        />
                                        {{
                                            order.payment_status.label.toUpperCase()
                                        }}
                                    </span>
                                </div>

                                <div
                                    v-if="order.paid_at"
                                    class="flex items-center justify-between"
                                >
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                        >Paid At</span
                                    >
                                    <span
                                        class="text-sm text-gray-900 dark:text-white"
                                        >{{ formatDate(order.paid_at) }}</span
                                    >
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-lg border border-gray-200 dark:border-gray-700"
                        >
                            <div
                                class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
                            >
                                <h3
                                    class="font-semibold text-gray-900 dark:text-white"
                                >
                                    Order Summary
                                </h3>
                            </div>
                            <div class="space-y-2 p-4">
                                <div class="flex justify-between text-sm">
                                    <span
                                        class="text-gray-600 dark:text-gray-400"
                                        >Subtotal</span
                                    >
                                    <span class="text-gray-900 dark:text-white"
                                        >€{{ order.subtotal.toFixed(2) }}</span
                                    >
                                </div>

                                <div
                                    v-if="order.delivery_fee > 0"
                                    class="flex justify-between text-sm"
                                >
                                    <span
                                        class="text-gray-600 dark:text-gray-400"
                                        >Delivery Fee</span
                                    >
                                    <span class="text-red-600 dark:text-white"
                                        >€{{
                                            order.delivery_fee.toFixed(2)
                                        }}</span
                                    >
                                </div>

                                <div
                                    v-if="order.discount_total > 0"
                                    class="flex justify-between text-sm"
                                >
                                    <span
                                        class="text-gray-600 dark:text-gray-400"
                                        >Discount</span
                                    >
                                    <span class="text-green-600"
                                        >-€{{
                                            order.discount_total.toFixed(2)
                                        }}</span
                                    >
                                </div>

                                <div class="flex justify-between text-sm">
                                    <span
                                        class="text-gray-600 dark:text-gray-400"
                                        >VAT</span
                                    >
                                    <span class="text-gray-900 dark:text-white"
                                        >€{{ order.vat_total.toFixed(2) }}</span
                                    >
                                </div>

                                <div
                                    v-if="order.vat_breakdown?.length"
                                    class="space-y-1 pl-4"
                                >
                                    <div
                                        v-for="vat in order.vat_breakdown"
                                        :key="vat.vat_rate"
                                        class="flex justify-between text-xs"
                                    >
                                        <span class="text-gray-500"
                                            >VAT {{ vat.vat_rate }}%</span
                                        >
                                        <span class="text-gray-600"
                                            >€{{
                                                vat.vat_total.toFixed(2)
                                            }}</span
                                        >
                                    </div>
                                </div>

                                <div
                                    class="border-t border-gray-200 pt-2 dark:border-gray-700"
                                >
                                    <div class="flex justify-between">
                                        <span
                                            class="font-semibold text-gray-900 dark:text-white"
                                            >Total</span
                                        >
                                        <span
                                            class="text-xl font-bold text-gray-900 dark:text-white"
                                        >
                                            €{{
                                                order.total_inc_vat.toFixed(2)
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <button
                                v-for="(btn, index) in orderStatusActions"
                                :key="index"
                                @click="btn.action"
                                class="w-full rounded-md px-4 py-2 text-sm font-medium text-white transition-colors"
                                :class="btn.class"
                            >
                                <span
                                    v-if="orderStatusActionId === btn.id"
                                    class="flex items-center justify-center"
                                >
                                    <svg
                                        class="h-5 w-5 animate-spin"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="white"
                                            stroke-width="3"
                                            fill="none"
                                            opacity="0.3"
                                        />
                                        <path
                                            d="M22 12a10 10 0 0 1-10 10"
                                            stroke="white"
                                            stroke-width="3"
                                            fill="none"
                                        />
                                    </svg>
                                </span>
                                <span v-else>{{ btn.label }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.max-h-\[calc\(100vh-8rem\)\] {
    scrollbar-width: thin;
}

.max-h-\[calc\(100vh-8rem\)\]::-webkit-scrollbar {
    width: 8px;
}

.max-h-\[calc\(100vh-8rem\)\]::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.max-h-\[calc\(100vh-8rem\)\]::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.max-h-\[calc\(100vh-8rem\)\]::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
