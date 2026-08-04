<script setup lang="ts">
import OrderDetailsCard from '@/components/client/orderDetails/OrderDetailsCard.vue';
import OrderItemsCard from '@/components/client/orderDetails/OrderItemsCard.vue';
import OrderStatusTimeline from '@/components/client/orderDetails/OrderStatusTimeline.vue';
import PaymentSummaryCard from '@/components/client/orderDetails/PaymentSummaryCard.vue';
import QuickActionsCard from '@/components/client/orderDetails/QuickActionsCard.vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { OrderType } from '@/types/order';
import { Head, usePage } from '@inertiajs/vue3';
import OrderReceipt from '@/components/client/orderDetails/OrderReceipt.vue';
import { getOrderStatusVariant } from '@/lib/utils';
import { useOrderStore } from '@/stores/order';
import { onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useDateFormatter } from '@/composables/useDateFormatter';

const props = defineProps<{
    orderToShow: OrderType;
}>();

const { t } = useI18n();

const { formatDate } = useDateFormatter();

const page = usePage();
const user = page.props.auth?.user;

const orderStore = useOrderStore();

const currentOrder = ref<OrderType | null>(props.orderToShow ?? null);

const removeOrder = (orderId: number) => {
    console.log(orderId);
};

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

type EchoChannel = {
    listen: (event: string, callback: (e: any) => void) => EchoChannel;
};

const channel = ref<EchoChannel | null>(null);

onMounted(async () => {
    if (user?.id) {
        channel.value = window.Echo.private(`user.${user?.id}`).listen(
            '.order.updated',
            async (e: any) => {
                if (currentOrder.value?.id === e.order.id) {
                    const response = await orderStore.getOrder(e.order.id);
                    currentOrder.value = response.order;
                }
            },
        );
    }
});

onUnmounted(() => {
    if (channel.value) {
        window.Echo.leave(`private-user.${user?.id}`);
    }
});
</script>

<template>
    <Head :title="t('order.orderDetailsPage.pageTitle')" />
    <ClientLayout>
        <section v-if="currentOrder" class="mx-auto max-w-6xl px-6 py-4">
            <div class="mb-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p
                            class="text-sm tracking-widest text-red-500 uppercase"
                        >
                            [ {{ t('order.orderDetailsPage.eyebrow') }} ]
                        </p>
                        <h1
                            class="text-[16px] font-semibold uppercase md:text-4xl"
                        >

                            {{ currentOrder.order_number }}
                        </h1>
                        <p class="mt-2 text-gray-600">
                            {{
                                t('order.orderDetailsPage.placedOn', {
                                    date: formatDate(currentOrder.created_at),
                                })
                            }}
                        </p>
                    </div>
                    <div
                        :class="[
                            'rounded-full px-4 py-2 text-sm font-semibold',
                            getOrderStatusVariant(currentOrder.status.value),
                        ]"
                    >
                        {{ t(`common.orderStatus.${currentOrder.status.key}`) }}
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row">
                <div class="flex-1">
                    <div class="space-y-6">
                        <OrderStatusTimeline :order-to-show="currentOrder" />

                        <OrderDetailsCard :order-to-show="currentOrder" />

                        <OrderItemsCard :order-to-show="currentOrder" />
                    </div>
                </div>

                <div class="lg:w-96">
                    <div class="sticky space-y-6 sm:top-20">
                        <PaymentSummaryCard :order-to-show="currentOrder" />

                        <QuickActionsCard
                            :order-to-show="currentOrder"
                            @print="printOrder"
                            @remove="removeOrder"
                        />
                    </div>
                </div>
            </div>
        </section>

        <div id="receipt" class="hidden">
            <OrderReceipt v-if="currentOrder" :order-to-show="currentOrder" />
        </div>
    </ClientLayout>
</template>
