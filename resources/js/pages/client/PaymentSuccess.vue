<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Check,
    Clock,
    CreditCard,
    Soup,
    MapPin,
    User,
    Info,
    Printer,
    Truck,
    ShoppingBag,
    Smartphone,
    HandPlatter,
    Utensils,
} from 'lucide-vue-next';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { OrderType } from '@/types/order';
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import order from '@/routes/order';

const props = defineProps<{
    orderToPay: OrderType;
}>();

const paidOrder = ref<OrderType>(props.orderToPay);
const isPaid = computed(() => paidOrder.value?.payment_status.label === 'Paid');

// Order steps based on OrderStatusEnum
const orderSteps = [
    { value: 1, label: 'Order Placed', icon: 'Clock' },
    { value: 2, label: 'Confirmed', icon: 'Check' },
    { value: 3, label: 'Preparing', icon: 'Package' },
    { value: 4, label: 'Ready', icon: 'Check' },
    { value: 5, label: 'Out for Delivery', icon: 'Truck' },
    { value: 6, label: 'Completed', icon: 'CheckCircle' },
];

const isStepCompleted = (stepValue: number) => {
    return paidOrder.value?.status?.value > stepValue;
};

const isCurrentStep = (stepValue: number) => {
    return paidOrder.value?.status?.value === stepValue;
};

const getOrderTypeIcon = (typeLabel: string) => {
    const label = typeLabel?.toLowerCase();
    if (label === 'delivery') return MapPin;
    if (label === 'takeaway') return HandPlatter;
    return Utensils;
};

const formatPrice = (price: number) => {
    return Number(price || 0).toFixed(2);
};

const formatDateTime = (dateString: string) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const printOrder = () => {
    window.print();
};

const trackOrder = () => {
    //
};

const checkPayment = async () => {
    if (isPaid.value) return;

    const { data } = await axios.get(order.getOrder(props.orderToPay.id).url);

    if (data.order.payment_status.label === 'Paid') {
        paidOrder.value = data.order as OrderType;
    } else {
        setTimeout(checkPayment, 2000);
    }
};

onMounted(() => {
    checkPayment();
});
</script>

<template>
    <Head title="Payment Successful" />

    <ClientLayout>
        <section class="mx-auto max-w-6xl px-6 py-4">
            <!-- Success Header -->
            <div class="mb-8">
                <h1
                    v-if="!isPaid"
                    class="text-3xl font-semibold uppercase md:text-4xl"
                >
                    ⏳ Waiting for payment confirmation...
                </h1>
                <h1
                    v-else
                    class="text-3xl font-semibold text-green-600 uppercase md:text-4xl"
                >
                    ✅ Payment successful!
                </h1>
                <p class="mt-2 text-gray-600">
                    Thank you for your order. Your payment has been confirmed.
                </p>
                <p class="mt-1 text-sm text-gray-500">
                    A confirmation email has been sent to your registered email
                    address.
                </p>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row">
                <!-- Order Details Section -->
                <div class="flex-1">
                    <div class="space-y-6">
                        <!-- Order Header Card -->
                        <div class="rounded-lg border bg-white p-6">
                            <div
                                class="flex flex-wrap items-center justify-between gap-3"
                            >
                                <div>
                                    <p class="text-sm text-gray-500">
                                        Order Number
                                    </p>
                                    <p class="text-2xl font-bold text-gray-800">
                                        #{{ paidOrder.order_number }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">
                                        Order at
                                    </p>
                                    <p class="font-medium text-gray-700">
                                        {{ paidOrder.created_at }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Status Timeline -->
                        <div class="rounded-lg border bg-white p-6">
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <Clock class="h-5 w-5 text-red-500" />
                                Order Status
                            </h2>

                            <div class="relative">
                                <div class="flex items-center justify-between">
                                    <div
                                        v-for="(step, index) in orderSteps"
                                        :key="step.value"
                                        class="relative flex flex-1 flex-col items-center"
                                    >
                                        <div
                                            :class="[
                                                'z-10 flex h-10 w-10 items-center justify-center rounded-full border-2 transition-all',
                                                isStepCompleted(step.value)
                                                    ? 'border-green-500 bg-green-500 text-white'
                                                    : isCurrentStep(step.value)
                                                      ? 'border-red-500 bg-red-500 text-white'
                                                      : 'border-gray-300 bg-white text-gray-400',
                                            ]"
                                        >
                                            <Check
                                                v-if="
                                                    isStepCompleted(step.value)
                                                "
                                                class="h-5 w-5"
                                            />
                                            <component
                                                v-else
                                                :is="step.icon"
                                                class="h-5 w-5"
                                            />
                                        </div>
                                        <div class="mt-2 text-center">
                                            <p
                                                :class="[
                                                    'text-xs font-medium',
                                                    isStepCompleted(
                                                        step.value,
                                                    ) ||
                                                    isCurrentStep(step.value)
                                                        ? 'text-gray-800'
                                                        : 'text-gray-400',
                                                ]"
                                            >
                                                {{ step.label }}
                                            </p>
                                        </div>
                                        <!-- Connecting Line -->
                                        <div
                                            v-if="index < orderSteps.length - 1"
                                            :class="[
                                                'absolute top-5 left-[calc(50%+1.25rem)] h-0.5 w-[calc(100%-2.5rem)]',
                                                isStepCompleted(
                                                    orderSteps[index + 1].value,
                                                ) || isStepCompleted(step.value)
                                                    ? 'bg-green-500'
                                                    : 'bg-gray-300',
                                            ]"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 rounded-md bg-blue-50 p-4">
                                <div class="flex items-start gap-3">
                                    <Info
                                        class="mt-0.5 h-4 w-4 text-blue-600"
                                    />
                                    <div class="text-sm text-blue-800">
                                        <p class="font-medium">What's next?</p>
                                        <p class="mt-1">
                                            We're now processing your order.
                                            You'll receive updates via email and
                                            SMS. Track your order status from
                                            your account dashboard.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Type & Details -->
                        <div class="rounded-lg border bg-white p-6">
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <component
                                    :is="getOrderTypeIcon(paidOrder.type.label)"
                                    class="h-5 w-5 text-red-500"
                                />
                                Order Details
                            </h2>

                            <div class="space-y-4">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex-shrink-0 rounded-lg bg-red-50 p-2"
                                    >
                                        <component
                                            :is="
                                                getOrderTypeIcon(
                                                    paidOrder.type.label,
                                                )
                                            "
                                            class="h-5 w-5 text-red-500"
                                        />
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Order Type
                                        </p>
                                        <p
                                            class="font-medium text-gray-800 capitalize"
                                        >
                                            {{ paidOrder.type.label }}
                                        </p>
                                        <p
                                            v-if="
                                                paidOrder.type.label ===
                                                'Dine-in'
                                            "
                                            class="mt-1 text-sm text-gray-600"
                                        >
                                            Table: {{ paidOrder.table_number }}
                                        </p>
                                        <p
                                            v-if="
                                                paidOrder.type.label ===
                                                'Takeaway'
                                            "
                                            class="mt-1 text-sm text-gray-600"
                                        >
                                            Pickup Time:
                                            {{
                                                formatDateTime(
                                                    paidOrder.pickup_time,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Customer Info for Takeaway -->
                                <div
                                    v-if="paidOrder.type.label === 'Takeaway'"
                                    class="flex items-start gap-3"
                                >
                                    <div
                                        class="flex-shrink-0 rounded-lg bg-red-50 p-2"
                                    >
                                        <User class="h-5 w-5 text-red-500" />
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Pickup Information
                                        </p>
                                        <p class="font-medium text-gray-800">
                                            {{ paidOrder.pickup_name || 'N/A' }}
                                        </p>
                                        <p
                                            v-if="paidOrder.pickup_phone"
                                            class="text-sm text-gray-600"
                                        >
                                            {{ paidOrder.pickup_phone }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Delivery Address -->
                                <div
                                    v-if="paidOrder.type.label === 'Delivery'"
                                    class="flex items-start gap-3"
                                >
                                    <div
                                        class="flex-shrink-0 rounded-lg bg-red-50 p-2"
                                    >
                                        <MapPin class="h-5 w-5 text-red-500" />
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Delivery Address
                                        </p>
                                        <p class="font-medium text-gray-800">
                                            {{
                                                paidOrder.delivery_address
                                                    ?.first_name
                                            }}
                                            {{
                                                paidOrder.delivery_address
                                                    ?.last_name
                                            }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{
                                                paidOrder.delivery_address
                                                    ?.phone
                                            }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{
                                                paidOrder.delivery_address
                                                    ?.street
                                            }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{
                                                paidOrder.delivery_address
                                                    ?.postal_code
                                            }}
                                            {{
                                                paidOrder.delivery_address
                                                    ?.city
                                            }},
                                            {{
                                                paidOrder.delivery_address
                                                    ?.country
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items Summary -->
                        <div class="rounded-lg border bg-white p-6">
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <Soup class="h-5 w-5 text-red-500" />
                                Order Items
                            </h2>

                            <div class="space-y-3">
                                <div
                                    v-for="item in paidOrder.items"
                                    :key="item.id"
                                    class="border-b border-gray-100 pb-3 last:border-0"
                                >
                                    <div class="flex justify-between text-sm">
                                        <div class="flex-1">
                                            <div class="font-medium">
                                                {{ item.quantity }}x
                                                {{ item.item?.name }}
                                            </div>
                                            <div
                                                v-if="item.meat"
                                                class="mt-1 text-xs text-gray-500"
                                            >
                                                Meat: {{ item.meat.name }}
                                                <span
                                                    v-if="
                                                        item.meat.extra_price >
                                                        0
                                                    "
                                                    class="text-gray-400"
                                                >
                                                    (+€{{
                                                        formatPrice(
                                                            item.meat
                                                                .extra_price,
                                                        )
                                                    }})
                                                </span>
                                            </div>
                                            <div
                                                v-if="
                                                    item.removed_ingredients &&
                                                    item.removed_ingredients
                                                        .length > 0
                                                "
                                                class="mt-1 text-xs text-gray-400"
                                            >
                                                Without:
                                                {{
                                                    item.removed_ingredients
                                                        .map((ing) => ing.name)
                                                        .join(', ')
                                                }}
                                            </div>
                                        </div>
                                        <div
                                            class="ml-4 font-medium whitespace-nowrap"
                                        >
                                            €{{ formatPrice(item.total_price) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div
                                v-if="paidOrder.notes"
                                class="mt-4 rounded-md bg-yellow-50 p-3"
                            >
                                <p class="text-xs font-medium text-yellow-700">
                                    Special Instructions
                                </p>
                                <p class="text-sm text-yellow-800">
                                    {{ paidOrder.notes }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Sidebar -->
                <div class="lg:w-96">
                    <div class="sticky top-6 space-y-6">
                        <!-- Payment Summary Card -->
                        <div class="rounded-lg border bg-white p-6">
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <CreditCard class="h-5 w-5 text-red-500" />
                                Payment Summary
                            </h2>

                            <div
                                class="space-y-2 border-b border-gray-100 pb-4"
                            >
                                <div
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>Subtotal</span>
                                    <span
                                        >€{{
                                            formatPrice(paidOrder.total_price)
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>Tax & Fees</span>
                                    <span>Included</span>
                                </div>
                                <div
                                    v-if="paidOrder.type.label === 'Delivery'"
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>Delivery Fee</span>
                                    <span>€{{ formatPrice(0) }}</span>
                                </div>
                            </div>

                            <div
                                class="mt-4 flex justify-between text-lg font-semibold"
                            >
                                <span>Total Paid</span>
                                <span class="text-green-600"
                                    >€{{
                                        formatPrice(paidOrder.total_price)
                                    }}</span
                                >
                            </div>

                            <div class="mt-4 rounded-md bg-green-50 p-3">
                                <div class="flex items-center gap-2">
                                    <CreditCard
                                        class="h-4 w-4 text-green-600"
                                    />
                                    <p class="text-sm text-green-700">
                                        Payment successful via Stripe
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="rounded-lg border bg-white p-6">
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <Smartphone class="h-5 w-5 text-red-500" />
                                Quick Actions
                            </h2>

                            <div class="space-y-3">
                                <button
                                    @click="printOrder"
                                    class="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-all hover:border-red-500 hover:bg-red-50"
                                >
                                    <Printer class="h-4 w-4" />
                                    Print Order Summary
                                </button>

                                <button
                                    @click="trackOrder"
                                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-red-500 px-4 py-2.5 text-sm font-semibold text-white transition-all hover:bg-red-600"
                                >
                                    <Truck class="h-4 w-4" />
                                    Track Your Order
                                </button>

                                <a
                                    href="#"
                                    class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-all hover:bg-gray-50"
                                >
                                    <ShoppingBag class="h-4 w-4" />
                                    Continue Shopping
                                </a>
                            </div>
                        </div>

                        <!-- Help Section -->
                        <div class="rounded-lg border bg-white p-6">
                            <div class="text-center">
                                <h3 class="font-medium text-gray-800">
                                    Need Help?
                                </h3>
                                <p class="mt-1 text-xs text-gray-500">
                                    Contact our customer support
                                </p>
                                <p
                                    class="mt-2 text-sm font-medium text-red-600"
                                >
                                    📞 +1 (555) 123-4567
                                </p>
                                <p class="text-xs text-gray-500">
                                    support@yourrestaurant.com
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </ClientLayout>
</template>

<style scoped>
@media print {
    button,
    .sticky,
    .quick-actions {
        display: none;
    }

    body {
        print-color-adjust: exact;
    }
}
</style>
