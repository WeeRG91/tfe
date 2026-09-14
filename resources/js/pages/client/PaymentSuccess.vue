<script setup lang="ts">
import OrderDetailsCard from '@/components/client/orderDetails/OrderDetailsCard.vue';
import OrderItemsCard from '@/components/client/orderDetails/OrderItemsCard.vue';
import OrderReceipt from '@/components/client/orderDetails/OrderReceipt.vue';
import OrderStatusTimeline from '@/components/client/orderDetails/OrderStatusTimeline.vue';
import PaymentSummaryCard from '@/components/client/orderDetails/PaymentSummaryCard.vue';
import QuickActionsCard from '@/components/client/orderDetails/QuickActionsCard.vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import order from '@/routes/order';
import paymentOrder from '@/routes/payment-order';
import { OrderType } from '@/types/order';
import { PaymentStatusEnum } from '@/types/payment';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{
    orderToPay: OrderType;
}>();

const paidOrder = ref<OrderType>(props.orderToPay);
const isPaid = computed(
    () => paidOrder.value.payment_status.value === PaymentStatusEnum.PAID,
);
const hasFailed = computed(
    () => paidOrder.value.payment_status.value === PaymentStatusEnum.FAILED,
);
const isPending = computed(
    () => paidOrder.value.payment_status.value === PaymentStatusEnum.PENDING,
);

const printOrder = () => {
    const receipt = document.getElementById('receipt');

    if (!receipt) return;

    const win = window.open('', '_blank', 'width=300,height=600');

    if (!win) return;

    win.document.documentElement.lang = 'en';

    const style = win.document.createElement('style');
    style.textContent = `
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
    `;

    win.document.head.appendChild(style);

    win.document.body.innerHTML = receipt.innerHTML;

    win.document.close();

    win.focus();

    win.print();

    win.close();
};

let paymentTimer: ReturnType<typeof setTimeout> | null = null;

const checkPayment = async () => {
    if (isPaid.value || hasFailed.value) {
        return;
    }

    try {
        const { data } = await axios.get(
            order.getOrder(props.orderToPay.id).url,
        );

        paidOrder.value = data.order as OrderType;

        if (data.order.payment_status.value === PaymentStatusEnum.PENDING) {
            paymentTimer = setTimeout(checkPayment, 2000);
        }
    } catch {
        paymentTimer = setTimeout(checkPayment, 5000);
    }
};

onMounted(() => {
    checkPayment();
});

onUnmounted(() => {
    if (paymentTimer) {
        clearTimeout(paymentTimer);
    }
});
</script>

<template>
    <Head
        :title="
            isPaid
                ? 'Payment Successful'
                : hasFailed
                  ? 'Payment Failed'
                  : 'Confirming Payment'
        "
    />

    <ClientLayout>
        <section class="mx-auto max-w-6xl px-6 py-4">
            <div class="mb-8">
                <p class="mb-1 text-sm tracking-widest text-red-500 uppercase">
                    [ Payment Details ]
                </p>
                <div
                    v-if="isPending"
                    class="rounded-lg border border-yellow-200 bg-yellow-50 p-5"
                >
                    <h1 class="text-2xl font-semibold text-yellow-800">
                        Confirming your payment…
                    </h1>
                    <p class="mt-2 text-sm text-yellow-700">
                        Please wait while we receive confirmation from Stripe.
                        Do not close this page.
                    </p>
                </div>
                <div
                    v-else-if="isPaid"
                    class="rounded-lg border border-green-200 bg-green-50 p-5"
                >
                    <h1 class="text-2xl font-semibold text-green-800">
                        Payment successful
                    </h1>
                    <p class="mt-2 text-green-700">
                        Thank you for your order. Your payment has been
                        confirmed.
                    </p>
                    <p class="mt-1 text-sm text-green-700">
                        A confirmation email has been sent to your registered
                        email address.
                    </p>
                </div>
                <div
                    v-else-if="hasFailed"
                    class="rounded-lg border border-red-200 bg-red-50 p-5"
                >
                    <h1 class="text-2xl font-semibold text-red-800">
                        Payment unsuccessful
                    </h1>

                    <p class="mt-2 text-sm text-red-700">
                        We could not complete your payment. Your order has not
                        been confirmed.
                    </p>

                    <Link
                        :href="paymentOrder.payment(paidOrder.id).url"
                        class="mt-4 inline-flex rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                    >
                        Try payment again
                    </Link>
                </div>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row">
                <div class="flex-1">
                    <div class="space-y-6">
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

                        <OrderStatusTimeline :order-to-show="paidOrder" />

                        <OrderDetailsCard :order-to-show="paidOrder" />

                        <OrderItemsCard :order-to-show="paidOrder" />
                    </div>
                </div>

                <div class="lg:w-96">
                    <div class="sticky space-y-6 sm:top-20">
                        <PaymentSummaryCard :order-to-show="paidOrder" />

                        <QuickActionsCard
                            :order-to-show="paidOrder"
                            @print="printOrder"
                        />
                    </div>
                </div>
            </div>
        </section>

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
