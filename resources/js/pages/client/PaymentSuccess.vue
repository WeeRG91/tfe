<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import order from '@/routes/order';
import { OrderType } from '@/types/order';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import OrderStatusTimeline from '@/components/client/orderDetails/OrderStatusTimeline.vue';
import OrderDetailsCard from '@/components/client/orderDetails/OrderDetailsCard.vue';
import OrderItemsCard from '@/components/client/orderDetails/OrderItemsCard.vue';
import PaymentSummaryCard from '@/components/client/orderDetails/PaymentSummaryCard.vue';
import QuickActionsCard from '@/components/client/orderDetails/QuickActionsCard.vue';
import OrderReceipt from '@/components/client/orderDetails/OrderReceipt.vue';

const props = defineProps<{
    orderToPay: OrderType;
}>();

const paidOrder = ref<OrderType>(props.orderToPay);
const isPaid = computed(() => paidOrder.value?.payment_status.label === 'Paid');

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
                <p class="text-sm tracking-widest text-red-500 uppercase">
                    [ Payment Details ]
                </p>
                <h1
                    v-if="!isPaid"
                    class="text-4xl font-semibold uppercase md:text-5xl"
                >
                    ⏳ Waiting for payment confirmation...
                </h1>
                <h1
                    v-else
                    class="text-4xl font-semibold text-green-600 uppercase md:text-5xl"
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
                        <OrderStatusTimeline :order-to-show="paidOrder" />

                        <!-- Order Type & Details -->
                        <OrderDetailsCard :order-to-show="paidOrder" />

                        <!-- Order Items Summary -->
                        <OrderItemsCard :order-to-show="paidOrder" />
                    </div>
                </div>

                <!-- Summary Sidebar -->
                <div class="lg:w-96">
                    <div class="sticky top-6 space-y-6">
                        <!-- Payment Summary Card -->
                        <PaymentSummaryCard :order-to-show="paidOrder" />

                        <!-- Quick Action Buttons -->
                        <QuickActionsCard
                            :order-to-show="paidOrder"
                            @print="printOrder"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Print receipt -->
        <div id="receipt" class="hidden">
            <OrderReceipt :order-to-show="paidOrder" />
        </div>
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
