<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import { Head } from '@inertiajs/vue3';
import { OrderType } from '@/types/order';
import {
    Clock,
    Check,
    User,
    MapPin,
    CreditCard,
    ShoppingBag,
    Smartphone,
    Printer,
    FileText,
    CheckCircle,
    Truck,
    Utensils,
    Soup,
    Coins
} from 'lucide-vue-next';
import { computed } from 'vue';
import { formatPrice } from '@/lib/utils';

const props = defineProps<{
    order: OrderType;
}>();

type OrderDateKey =
    | 'created_at'
    | 'confirmed_at'
    | 'delivered_at'
    | 'completed_at';

type Step = {
    value: number;
    label: string;
    description: string;
    dateKey?: OrderDateKey;
};

const orderSteps = computed<Step[]>(() => {
    const isDelivery = props.order.type?.label === 'Delivery';

    const steps: Step[] = [
        {
            value: 1,
            label: 'Order Placed',
            description: 'Your order has been received',
            dateKey: 'created_at',
        },
        {
            value: 2,
            label: 'Confirmed',
            description: 'Restaurant has confirmed your order',
            dateKey: 'confirmed_at',
        },
    ];

    if (isDelivery) {
        steps.push(
            {
                value: 3,
                label: 'Preparing',
                description: 'Your order is being prepared',
            },
            {
                value: 4,
                label: 'Ready',
                description: 'Your order is ready for pickup',
            },
            {
                value: 5,
                label: 'Out for Delivery',
                description: 'Your order is on the way',
                dateKey: 'delivered_at',
            },
            {
                value: 6,
                label: 'Delivered',
                description: 'Your order has been delivered',
                dateKey: 'delivered_at',
            },
        );
    } else {
        steps.push(
            {
                value: 3,
                label: 'Preparing',
                description: 'Your order is being prepared',
            },
            {
                value: 4,
                label: 'Ready',
                description: 'Your order is ready for pickup',
            },
            {
                value: 6,
                label: 'Completed',
                description: 'Order completed',
                dateKey: 'completed_at',
            },
        );
    }

    return steps;
});

// Check if a step is completed
const isStepCompleted = (stepValue: number) => {
    const statusOrder = orderSteps.value.map((s) => s.value);
    const currentStatusIndex = statusOrder.indexOf(props.order.status?.value);
    const stepIndex = statusOrder.indexOf(stepValue);
    return stepIndex < currentStatusIndex;
};

// Check if a step is the current step
const isCurrentStep = (stepValue: number) => {
    return props.order.status?.value === stepValue;
};

// Get the date for a specific step
const getStepDate = (stepValue: number) => {
    const step = orderSteps.value.find((s) => s.value === stepValue);

    if (step?.dateKey && props.order[step.dateKey]) {
        return props.order[step.dateKey];
    }

    return null;
};

const formatDate = (date: string) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatTime = (date: string) => {
    if (!date) return '';
    return new Date(date).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

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

const classes = {
    Pending: 'bg-yellow-100 text-yellow-800',
    Confirmed: 'bg-blue-100 text-blue-800',
    Preparing: 'bg-purple-100 text-purple-800',
    Ready: 'bg-indigo-100 text-indigo-800',
    Delivering: 'bg-orange-100 text-orange-800',
    Completed: 'bg-green-100 text-green-800',
    Cancelled: 'bg-red-100 text-red-800',
} as const;

const getStatusBadgeClass = (status: string) => {
    return (
        classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800'
    );
};

const printOrder = () => {
    const content = document.getElementById('receipt')?.innerHTML;

    const win = window.open('', '', 'width=300,height=600');

    if (!win || !content) return;

    win.document.write(`
        <html>
            <head>
                <title>Receipt</title>
                <style>
                    body {
                        font-family: "Courier New", monospace;
                        width: 280px;
                        margin: 0;
                        padding: 10px;
                    }
                    .divider {
                        border-top: 1px dashed black;
                        margin: 6px 0;
                    }
                    .flex {
                        display: flex;
                        justify-content: space-between;
                    }
                    .text-center {
                        text-align: center;
                    }
                </style>
            </head>
            <body>${content}</body>
        </html>
    `);

    win.document.close();
    win.print();
    win.close();
};
</script>

<template>
    <Head title="OrderDetails" />
    <ClientLayout>
        <section class="mx-auto max-w-6xl px-6 py-4">
            <!-- Order Header -->
            <div class="mb-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1
                            class="text-3xl font-semibold uppercase md:text-4xl"
                        >
                            Order #{{ order.order_number }}
                        </h1>
                        <p class="mt-2 text-gray-600">
                            Placed on {{ formatDate(order.created_at) }}
                        </p>
                    </div>
                    <div
                        :class="[
                            'rounded-full px-4 py-2 text-sm font-semibold',
                            getStatusBadgeClass(order.status.label),
                        ]"
                    >
                        {{ order.status.label }}
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row">
                <!-- Order Details Section -->
                <div class="flex-1">
                    <div class="space-y-6">
                        <!-- Order Status Timeline -->
                        <div class="rounded-lg border bg-white p-6">
                            <h2
                                class="mb-6 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <Clock class="h-5 w-5 text-red-500" />
                                Order Status
                            </h2>

                            <!-- Mobile: Vertical Timeline -->
                            <div class="md:hidden">
                                <div class="relative">
                                    <div
                                        class="absolute top-3 left-5 h-full w-0.5 bg-gray-200"
                                    ></div>

                                    <div
                                        v-for="step in orderSteps"
                                        :key="step.value"
                                        class="relative mb-8 flex items-start gap-4 last:mb-0"
                                    >
                                        <div class="relative z-10">
                                            <div
                                                :class="[
                                                    'flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border-2 shadow-sm transition-all',
                                                    isStepCompleted(step.value)
                                                        ? 'border-green-300 bg-green-300 text-white shadow-green-200'
                                                        : isCurrentStep(
                                                                step.value,
                                                            )
                                                          ? 'border-green-500 bg-green-500 text-white ring-4 shadow-green-200 ring-green-100'
                                                          : 'border-gray-300 bg-white text-gray-400',
                                                ]"
                                            >
                                                <Check
                                                    v-if="
                                                        isStepCompleted(
                                                            step.value,
                                                        ) ||
                                                        isCurrentStep(
                                                            step.value,
                                                        )
                                                    "
                                                    class="h-5 w-5"
                                                />
                                            </div>
                                        </div>

                                        <div class="flex-1">
                                            <div
                                                class="rounded-lg bg-gray-50 p-3 transition-all"
                                                :class="{
                                                    'border-l-4 border-green-500 bg-green-50':
                                                        isCurrentStep(
                                                            step.value,
                                                        ),
                                                }"
                                            >
                                                <div
                                                    class="flex items-center justify-between"
                                                >
                                                    <p
                                                        :class="[
                                                            'font-semibold',
                                                            isStepCompleted(
                                                                step.value,
                                                            ) ||
                                                            isCurrentStep(
                                                                step.value,
                                                            )
                                                                ? 'text-gray-900'
                                                                : 'text-gray-500',
                                                        ]"
                                                    >
                                                        {{ step.label }}
                                                    </p>
                                                    <p
                                                        v-if="
                                                            getStepDate(
                                                                step.value,
                                                            )
                                                        "
                                                        class="text-xs text-gray-400"
                                                    >
                                                        {{
                                                            getStepDate(
                                                                step.value,
                                                            )
                                                                ? formatTime(
                                                                      getStepDate(
                                                                          step.value,
                                                                      )!,
                                                                  )
                                                                : ''
                                                        }}
                                                    </p>
                                                </div>
                                                <p
                                                    v-if="
                                                        isCurrentStep(
                                                            step.value,
                                                        ) && step.description
                                                    "
                                                    class="mt-1 text-xs text-gray-500"
                                                >
                                                    {{ step.description }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Desktop: Horizontal Timeline -->
                            <div class="hidden md:block">
                                <div class="relative">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div
                                            v-for="(step, index) in orderSteps"
                                            :key="step.value"
                                            class="relative flex flex-1 flex-col items-center"
                                        >
                                            <div
                                                :class="[
                                                    'z-10 flex h-12 w-12 items-center justify-center rounded-full border-2 shadow-sm transition-all',
                                                    isStepCompleted(step.value)
                                                        ? 'border-green-300 bg-green-300 text-white'
                                                        : isCurrentStep(
                                                                step.value,
                                                            )
                                                          ? 'border-green-500 bg-green-500 text-white ring-4 ring-green-100'
                                                          : 'border-gray-300 bg-white text-gray-400',
                                                ]"
                                            >
                                                <Check
                                                    v-if="
                                                        isStepCompleted(
                                                            step.value,
                                                        ) ||
                                                        isCurrentStep(
                                                            step.value,
                                                        )
                                                    "
                                                    class="h-5 w-5"
                                                />
                                            </div>
                                            <div class="mt-3 text-center">
                                                <p
                                                    :class="[
                                                        'text-sm font-medium',
                                                        isStepCompleted(
                                                            step.value,
                                                        ) ||
                                                        isCurrentStep(
                                                            step.value,
                                                        )
                                                            ? 'text-gray-900'
                                                            : 'text-gray-400',
                                                    ]"
                                                >
                                                    {{ step.label }}
                                                </p>
                                                <p
                                                    v-if="
                                                        getStepDate(step.value)
                                                    "
                                                    class="mt-1 text-xs text-gray-400"
                                                >
                                                    {{
                                                        getStepDate(step.value)
                                                            ? formatTime(
                                                                  getStepDate(
                                                                      step.value,
                                                                  )!,
                                                              )
                                                            : ''
                                                    }}
                                                </p>
                                            </div>
                                            <div
                                                v-if="
                                                    index <
                                                    orderSteps.length - 1
                                                "
                                                :class="[
                                                    'absolute top-6 left-[calc(50%+1.5rem)] h-0.5 w-[calc(100%-3rem)]',
                                                    isStepCompleted(
                                                        orderSteps[index + 1]
                                                            ?.value,
                                                    )
                                                        ? 'bg-green-500'
                                                        : 'bg-gray-200',
                                                ]"
                                            ></div>
                                        </div>
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
                                    :is="getOrderTypeIcon(order.type.label)"
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
                                                    order.type.label,
                                                )
                                            "
                                            class="h-5 w-5 text-red-500"
                                        />
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Order Type
                                        </p>
                                        <p class="font-medium text-gray-800">
                                            {{ order.type.label }}
                                        </p>
                                        <p
                                            v-if="
                                                order.type.label ===
                                                    'Dine-in' &&
                                                order.table_number
                                            "
                                            class="mt-1 text-sm text-gray-600"
                                        >
                                            Table: {{ order.table_number }}
                                        </p>
                                        <p
                                            v-if="
                                                order.type.label ===
                                                    'Takeaway' &&
                                                order.pickup_time
                                            "
                                            class="mt-1 text-sm text-gray-600"
                                        >
                                            Pickup Time:
                                            {{
                                                formatDateTime(
                                                    order.pickup_time,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Customer Info for Takeaway -->
                                <div
                                    v-if="order.type.label === 'Takeaway'"
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
                                            {{ order.pickup_name || 'N/A' }}
                                        </p>
                                        <p
                                            v-if="order.pickup_phone"
                                            class="text-sm text-gray-600"
                                        >
                                            {{ order.pickup_phone }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Delivery Address -->
                                <div
                                    v-if="
                                        order.type.label === 'Delivery' &&
                                        order.delivery_address
                                    "
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
                                                order.delivery_address
                                                    .first_name
                                            }}
                                            {{
                                                order.delivery_address.last_name
                                            }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{ order.delivery_address.phone }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{ order.delivery_address.street }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{
                                                order.delivery_address
                                                    .postal_code
                                            }}
                                            {{ order.delivery_address.city }},
                                            {{ order.delivery_address.country }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Payment Info -->
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex-shrink-0 rounded-lg bg-red-50 p-2"
                                    >
                                        <Coins
                                            v-if="order.payment_method?.label === 'Cash'"
                                            class="h-5 w-5 text-red-500"
                                        />
                                        <CreditCard
                                            v-else
                                            class="h-5 w-5 text-red-500"
                                        />
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Payment Method
                                        </p>
                                        <p class="font-medium text-gray-800">
                                            {{
                                                order.payment_method?.label ||
                                                'N/A'
                                            }}
                                        </p>
                                        <p
                                            v-if="order.paid_at"
                                            class="text-sm text-green-600"
                                        >
                                            Paid on
                                            {{ formatDateTime(order.paid_at) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div
                                    v-if="order.notes"
                                    class="flex items-start gap-3"
                                >
                                    <div
                                        class="flex-shrink-0 rounded-lg bg-red-50 p-2"
                                    >
                                        <FileText
                                            class="h-5 w-5 text-red-500"
                                        />
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Order Notes
                                        </p>
                                        <p class="text-sm text-gray-700">
                                            {{ order.notes }}
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
                                    v-for="item in order.items"
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
                                            <div
                                                v-if="item.notes"
                                                class="mt-1 text-xs text-gray-400"
                                            >
                                                Note: {{ item.notes }}
                                            </div>
                                        </div>
                                        <div
                                            class="ml-4 font-medium whitespace-nowrap"
                                        >
                                            €{{ formatPrice(item.total) }}
                                        </div>
                                    </div>
                                </div>
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
                                    v-if="order.final_food_inc_vat"
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>Food (inc. VAT)</span>
                                    <span
                                        >€{{
                                            formatPrice(
                                                order.final_food_inc_vat,
                                            )
                                        }}</span
                                    >
                                </div>
                                <div
                                    v-if="order.final_drinks_inc_vat"
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>Drinks (inc. VAT)</span>
                                    <span
                                        >€{{
                                            formatPrice(
                                                order.final_drinks_inc_vat,
                                            )
                                        }}</span
                                    >
                                </div>
                                <div
                                    v-if="order.vat_food_amount"
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span
                                        >VAT ({{ order.vat_food_rate }}% -
                                        Food)</span
                                    >
                                    <span
                                        >€{{
                                            formatPrice(order.vat_food_amount)
                                        }}</span
                                    >
                                </div>
                                <div
                                    v-if="order.vat_drinks_amount"
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span
                                        >VAT ({{ order.vat_drinks_rate }}% -
                                        Drinks)</span
                                    >
                                    <span
                                        >€{{
                                            formatPrice(order.vat_drinks_amount)
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>Total VAT</span>
                                    <span
                                        >€{{
                                            formatPrice(order.vat_total)
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>Subtotal</span>
                                    <span
                                        >€{{
                                            formatPrice(order.subtotal)
                                        }}</span
                                    >
                                </div>
                                <div
                                    v-if="
                                        order.type.label === 'Delivery' &&
                                        order.delivery_fee
                                    "
                                    class="flex justify-between text-sm text-red-600"
                                >
                                    <span>Delivery Fee</span>
                                    <span
                                        >+€{{
                                            formatPrice(order.delivery_fee)
                                        }}</span
                                    >
                                </div>
                                <div
                                    v-if="order.discount_total"
                                    class="flex justify-between text-sm text-green-600"
                                >
                                    <span
                                        >Discount ({{
                                            order.discount_rate
                                        }}%)</span
                                    >
                                    <span
                                        >-€{{
                                            formatPrice(order.discount_total)
                                        }}</span
                                    >
                                </div>
                            </div>
                            <div
                                class="mt-4 flex justify-between text-lg font-semibold"
                            >
                                <span>Total Paid</span>
                                <span class="text-green-600"
                                    >€{{ formatPrice(order.total) }}</span
                                >
                            </div>

                            <div
                                v-if="order.payment_status"
                                class="mt-4 rounded-md p-3"
                                :class="
                                    order.payment_status.value === 2
                                        ? 'bg-green-50'
                                        : 'bg-yellow-50'
                                "
                            >
                                <div class="flex items-center gap-2">
                                    <CheckCircle
                                        v-if="order.payment_status.value === 2"
                                        class="h-4 w-4 text-green-600"
                                    />
                                    <CheckCircle
                                        v-else
                                        class="h-4 w-4 text-yellow-600"
                                    />
                                    <p
                                        :class="
                                            order.payment_status.value === 2
                                                ? 'text-green-700'
                                                : 'text-yellow-700'
                                        "
                                        class="text-sm"
                                    >
                                        Payment
                                        {{
                                            order.payment_status.label.toLowerCase()
                                        }}
                                        <span
                                            v-if="order.payment_method?.label"
                                        >
                                            via {{ order.payment_method.label }}
                                        </span>
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
                                    class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-all hover:border-red-500 hover:bg-red-50"
                                >
                                    <Printer class="h-4 w-4" />
                                    Print Order Summary
                                </button>

                                <a
                                    href="#"
                                    class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-all hover:border-red-500 hover:bg-red-50"
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

        <!-- Print receipt -->
        <div id="receipt" class="receipt hidden">
            <!-- Header -->
            <div class="mb-2 text-center">
                <h2 class="text-lg font-bold">WEE RESTAURANT</h2>
                <p>Rue de Wee 123</p>
                <p>6000 Arlon, Belgium</p>
                <p>TVA: BE0123.456.789</p>
                <p>Tel: +32 123 45 67 89</p>
            </div>

            <div class="divider"></div>

            <!-- Order Info -->
            <div class="text-xs">
                <p>Order: #{{ order.order_number }}</p>
                <p>Date: {{ formatDateTime(order.created_at) }}</p>
                <p>Type: {{ order.type.label }}</p>
                <p v-if="order.table_number">Table: {{ order.table_number }}</p>
            </div>

            <div class="divider"></div>

            <!-- Items -->
            <div class="text-xs">
                <div v-for="item in order.items" :key="item.id" class="mb-1">
                    <div class="flex justify-between">
                        <span>{{ item.quantity }} x {{ item.item?.name }}</span>
                        <span>€{{ formatPrice(item.total) }}</span>
                    </div>

                    <div v-if="item.meat">
                        &nbsp;+ {{ item.meat.name }} (€{{
                            formatPrice(item.meat.extra_price)
                        }})
                    </div>

                    <div v-for="ing in item.removed_ingredients" :key="ing.id">
                        &nbsp;- {{ ing.name }}
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Totals -->
            <div class="text-xs">
                <div
                    v-if="order.final_food_inc_vat"
                    class="flex justify-between"
                >
                    <span>Food</span>
                    <span>€{{ formatPrice(order.final_food_inc_vat) }}</span>
                </div>
                <div
                    v-if="order.final_drinks_inc_vat"
                    class="flex justify-between"
                >
                    <span>Drinks</span>
                    <span>€{{ formatPrice(order.final_drinks_inc_vat) }}</span>
                </div>

                <div v-if="order.vat_food_amount" class="flex justify-between">
                    <span>VAT 6%</span>
                    <span>€{{ formatPrice(order.vat_food_amount) }}</span>
                </div>
                <div
                    v-if="order.vat_drinks_amount"
                    class="flex justify-between"
                >
                    <span>VAT 21%</span>
                    <span>€{{ formatPrice(order.vat_drinks_amount) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Total VAT</span>
                    <span>€{{ formatPrice(order.vat_total) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span>€{{ formatPrice(order.subtotal) }}</span>
                </div>
                <div v-if="order.delivery_fee" class="flex justify-between">
                    <span>Delivery</span>
                    <span>+€{{ formatPrice(order.delivery_fee) }}</span>
                </div>
                <div v-if="order.discount_total" class="flex justify-between">
                    <span>Discount</span>
                    <span>-€{{ formatPrice(order.discount_total) }}</span>
                </div>
            </div>

            <div class="divider"></div>

            <!-- TOTAL -->
            <div class="flex justify-between font-bold">
                <span>TOTAL</span>
                <span>€{{ formatPrice(order.total) }}</span>
            </div>

            <div class="divider"></div>

            <!-- Footer -->
            <div class="mt-2 text-center text-xs">
                <p>Paid via Stripe</p>
                <p>Thank you for your visit</p>
            </div>
        </div>
    </ClientLayout>
</template>
