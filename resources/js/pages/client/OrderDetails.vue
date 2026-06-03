<script setup lang="ts">
import OrderDetailsCard from '@/components/client/orderDetails/OrderDetailsCard.vue';
import OrderItemsCard from '@/components/client/orderDetails/OrderItemsCard.vue';
import OrderStatusTimeline from '@/components/client/orderDetails/OrderStatusTimeline.vue';
import PaymentSummaryCard from '@/components/client/orderDetails/PaymentSummaryCard.vue';
import QuickActionsCard from '@/components/client/orderDetails/QuickActionsCard.vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { OrderType } from '@/types/order';
import { Head } from '@inertiajs/vue3';
import OrderReceipt from '@/components/client/orderDetails/OrderReceipt.vue';
import { formatDate, getOrderStatusVariant } from '@/lib/utils';

const props = defineProps<{
    orderToShow: OrderType;
}>();

const removeOrder = (orderId: number) => {
    console.log(orderId);
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
                        <p
                            class="text-sm tracking-widest text-red-500 uppercase"
                        >
                            [ My Order ]
                        </p>
                        <h1
                            class="text-4xl font-semibold uppercase md:text-5xl"
                        >
                            Order #{{ orderToShow.order_number }}
                        </h1>
                        <p class="mt-2 text-gray-600">
                            Placed on {{ formatDate(orderToShow.created_at) }}
                        </p>
                    </div>
                    <div
                        :class="[
                            'rounded-full px-4 py-2 text-sm font-semibold',
                            getOrderStatusVariant(orderToShow.status.value),
                        ]"
                    >
                        {{ orderToShow.status.label }}
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row">
                <!-- Order Details Section -->
                <div class="flex-1">
                    <div class="space-y-6">
                        <!-- Order Status Timeline -->
                        <OrderStatusTimeline
                            :order-to-show="props.orderToShow"
                        />

                        <!-- Order Type & Details -->
                        <OrderDetailsCard :order-to-show="props.orderToShow" />

                        <!-- Order Items Summary -->
                        <OrderItemsCard :order-to-show="props.orderToShow" />
                    </div>
                </div>

                <!-- Summary Sidebar -->
                <div class="lg:w-96">
                    <div class="sticky top-6 space-y-6">
                        <!-- Payment Summary Card -->
                        <PaymentSummaryCard
                            :order-to-show="props.orderToShow"
                        />

                        <!-- Quick Action Buttons -->
                        <QuickActionsCard
                            :order-to-show="props.orderToShow"
                            @print="printOrder"
                            @remove="removeOrder"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Print receipt -->
        <div id="receipt" class="hidden">
            <OrderReceipt :order-to-show="props.orderToShow" />
        </div>
    </ClientLayout>
</template>
