<script setup lang="ts">
import { OrderType } from '@/types/order';
import {
    MapPin,
    User,
    Coins,
    CreditCard,
    FileText,
    Utensils,
    ShoppingBag,
    Truck,
} from 'lucide-vue-next';

defineProps<{
    orderToShow: OrderType;
}>();

const formatDateTime = (date: string) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const icons = {
    'Dine-in': Utensils,
    Takeaway: ShoppingBag,
    Delivery: Truck,
} as const;

const getOrderTypeIcon = (type: string) => {
    return icons[type as keyof typeof icons] ?? ShoppingBag;
};
</script>

<template>
    <div class="rounded-lg border bg-white p-6">
        <h2
            class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
        >
            <component
                :is="getOrderTypeIcon(orderToShow.type.label)"
                class="h-5 w-5 text-red-500"
            />
            Order Details
        </h2>

        <div class="space-y-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 rounded-lg bg-red-50 p-2">
                    <component
                        :is="getOrderTypeIcon(orderToShow.type.label)"
                        class="h-5 w-5 text-red-500"
                    />
                </div>
                <div>
                    <p class="text-sm text-gray-500">Order Type</p>
                    <p class="font-medium text-gray-800">
                        {{ orderToShow.type.label }}
                    </p>
                    <p
                        v-if="
                            orderToShow.type.label === 'Dine-in' &&
                            orderToShow.table_number
                        "
                        class="mt-1 text-sm text-gray-600"
                    >
                        Table: {{ orderToShow.table_number }}
                    </p>
                    <p
                        v-if="
                            orderToShow.type.label === 'Takeaway' &&
                            orderToShow.pickup_time
                        "
                        class="mt-1 text-sm text-gray-600"
                    >
                        Pickup Time:
                        {{ formatDateTime(orderToShow.pickup_time) }}
                    </p>
                </div>
            </div>

            <div
                v-if="orderToShow.type.label === 'Takeaway'"
                class="flex items-start gap-3"
            >
                <div class="flex-shrink-0 rounded-lg bg-red-50 p-2">
                    <User class="h-5 w-5 text-red-500" />
                </div>
                <div>
                    <p class="text-sm text-gray-500">Pickup Information</p>
                    <p class="font-medium text-gray-800">
                        {{ orderToShow.pickup_name || 'N/A' }}
                    </p>
                    <p
                        v-if="orderToShow.pickup_phone"
                        class="text-sm text-gray-600"
                    >
                        {{ orderToShow.pickup_phone }}
                    </p>
                </div>
            </div>

            <div
                v-if="
                    orderToShow.type.label === 'Delivery' &&
                    orderToShow.delivery_address
                "
                class="flex items-start gap-3"
            >
                <div class="flex-shrink-0 rounded-lg bg-red-50 p-2">
                    <MapPin class="h-5 w-5 text-red-500" />
                </div>
                <div>
                    <p class="text-sm text-gray-500">Delivery Address</p>
                    <p class="font-medium text-gray-800">
                        {{ orderToShow.delivery_address.first_name }}
                        {{ orderToShow.delivery_address.last_name }}
                    </p>
                    <p class="text-sm text-gray-600">
                        {{ orderToShow.delivery_address.phone }}
                    </p>
                    <p class="text-sm text-gray-600">
                        {{ orderToShow.delivery_address.street }}
                    </p>
                    <p class="text-sm text-gray-600">
                        {{ orderToShow.delivery_address.postal_code }}
                        {{ orderToShow.delivery_address.city }},
                        {{ orderToShow.delivery_address.country }}
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 rounded-lg bg-red-50 p-2">
                    <Coins
                        v-if="orderToShow.payment_method?.label === 'Cash'"
                        class="h-5 w-5 text-red-500"
                    />
                    <CreditCard v-else class="h-5 w-5 text-red-500" />
                </div>
                <div>
                    <p class="text-sm text-gray-500">Payment Method</p>
                    <p class="font-medium text-gray-800">
                        {{ orderToShow.payment_method?.label || 'N/A' }}
                    </p>
                    <p
                        v-if="orderToShow.paid_at"
                        class="text-sm text-green-600"
                    >
                        Paid on {{ formatDateTime(orderToShow.paid_at) }}
                    </p>
                </div>
            </div>

            <div v-if="orderToShow.notes" class="flex items-start gap-3">
                <div class="flex-shrink-0 rounded-lg bg-red-50 p-2">
                    <FileText class="h-5 w-5 text-red-500" />
                </div>
                <div>
                    <p class="text-sm text-gray-500">Order Notes</p>
                    <p class="text-sm text-gray-700">{{ orderToShow.notes }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
