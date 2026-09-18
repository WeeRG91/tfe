<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import { getSpicyLevelLabels } from '@/lib/const';
import {
    formatPrice,
    getOrderStatusVariant,
    getOrderTypeIcon,
} from '@/lib/utils';
import paymentOrder from '@/routes/payment-order';
import { OrderType, OrderTypeEnum } from '@/types/order';
import { Head } from '@inertiajs/vue3';
import { loadStripe } from '@stripe/stripe-js';
import axios from 'axios';
import {
    CreditCard,
    HandCoins,
    MapPin,
    Soup,
    User,
    Building2,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useDateFormatter } from '@/composables/useDateFormatter';
import { DeliveryTypeEnum } from '@/types/delivery';

const props = defineProps<{
    order: OrderType;
}>();

const { t } = useI18n();
const spicyLevelLabels = computed(() => getSpicyLevelLabels(t));

const { formatDateOnly } = useDateFormatter();

const stripe = ref<any>(null);
const elements = ref<any>(null);
const isLoading = ref<boolean>(false);
const isInitializing = ref<boolean>(true);
const paymentError = ref<string | null>(null);

const initPayment = async () => {
    isInitializing.value = true;
    paymentError.value = null;

    try {
        const { data } = await axios.post(
            paymentOrder.createPaymentIntent(props.order.id).url,
        );

        if (!data.client_secret) {
            paymentError.value = t('payment.errors.initializationFailed');
            return;
        }

        stripe.value = await loadStripe(import.meta.env.VITE_STRIPE_PUBLIC_KEY);

        if (!stripe.value) {
            paymentError.value = t('payment.errors.stripeLoadFailed');
            return;
        }

        elements.value = stripe.value.elements({
            clientSecret: data.client_secret,
        });

        const paymentElement = elements.value.create('payment');
        paymentElement.mount('#stripe-payment-element');
    } catch (error: any) {
        paymentError.value =
            error.response?.data?.message ??
            error.message ??
            t('payment.errors.unableToInitialize');
    } finally {
        isInitializing.value = false;
    }
};

const pay = async () => {
    if (!stripe.value || !elements.value || isLoading.value) {
        return;
    }

    isLoading.value = true;
    paymentError.value = null;

    try {
        const { error } = await stripe.value.confirmPayment({
            elements: elements.value,
            confirmParams: {
                return_url:
                    window.location.origin +
                    `/payments/${props.order.id}/payment-success`,
            },
        });

        if (error) {
            paymentError.value =
                error.message ?? t('payment.errors.paymentFailed');

            isLoading.value = false;
        }
    } catch {
        paymentError.value = t('payment.errors.unexpected');

        isLoading.value = false;
    }
};

onMounted(() => {
    initPayment();
});
</script>

<template>
    <Head :title="t('payment.title')" />

    <ClientLayout>
        <section class="mx-auto max-w-6xl px-6 py-4">
            <div class="mb-4">
                <p class="text-sm tracking-widest text-red-500 uppercase">
                    [ {{ t('payment.secureCheckout') }} ]
                </p>
                <h1 class="text-4xl font-semibold uppercase md:text-5xl">
                    {{ t('payment.completePayment') }}
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    {{ t('payment.stripePowered') }}
                </p>
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
                                        {{ t('payment.order.orderNumber') }}
                                    </p>
                                    <p
                                        class="text-xl font-semibold text-gray-800"
                                    >
                                        #{{ props.order.order_number }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">
                                        {{ t('payment.order.status') }}
                                    </p>
                                    <span
                                        :class="
                                            getOrderStatusVariant(
                                                props.order.status.value,
                                            )
                                        "
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{
                                            t(
                                                `common.orderStatus.${props.order.status.key}`,
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border bg-white p-6">
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <component
                                    :is="
                                        getOrderTypeIcon(props.order.type.value)
                                    "
                                    class="h-5 w-5 text-red-500"
                                />
                                {{ t('payment.order.orderDetails') }}
                            </h2>

                            <div class="space-y-4">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex-shrink-0 rounded-lg bg-red-50 p-2"
                                    >
                                        <component
                                            :is="
                                                getOrderTypeIcon(
                                                    props.order.type.value,
                                                )
                                            "
                                            class="h-5 w-5 text-red-500"
                                        />
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            {{ t('payment.order.orderType') }}
                                        </p>
                                        <p
                                            class="font-medium text-gray-800 capitalize"
                                        >
                                            {{
                                                t(
                                                    `common.orderTypes.${props.order.type.key}`,
                                                )
                                            }}
                                        </p>
                                        <p
                                            v-if="
                                                props.order.type.value ===
                                                OrderTypeEnum.DINEIN
                                            "
                                            class="mt-1 text-sm text-gray-600"
                                        >
                                            {{ t('payment.order.table') }}:
                                            {{ props.order.table_number }}
                                        </p>
                                        <p
                                            v-if="
                                                props.order.type.value ===
                                                OrderTypeEnum.TAKEAWAY
                                            "
                                            class="mt-1 text-sm text-gray-600"
                                        >
                                            {{ t('payment.order.pickup') }}:
                                            {{ props.order.pickup_time }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        props.order.type.value ===
                                        OrderTypeEnum.TAKEAWAY
                                    "
                                    class="flex items-start gap-3"
                                >
                                    <div
                                        class="flex-shrink-0 rounded-lg bg-red-50 p-2"
                                    >
                                        <User class="h-5 w-5 text-red-500" />
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            {{ t('payment.order.customer') }}
                                        </p>
                                        <p class="font-medium text-gray-800">
                                            {{
                                                props.order.pickup_name || 'N/A'
                                            }}
                                        </p>
                                        <p
                                            v-if="props.order.pickup_phone"
                                            class="text-sm text-gray-600"
                                        >
                                            {{ props.order.pickup_phone }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        props.order.delivery_address &&
                                        props.order.delivery_type !==
                                            DeliveryTypeEnum.COMPANY
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
                                            {{
                                                t(
                                                    'payment.order.deliveryAddress',
                                                )
                                            }}
                                        </p>
                                        <p class="font-medium text-gray-800">
                                            {{
                                                props.order.delivery_address
                                                    .first_name
                                            }}
                                            {{
                                                props.order.delivery_address
                                                    .last_name
                                            }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{
                                                props.order.delivery_address
                                                    .phone
                                            }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{
                                                props.order.delivery_address
                                                    .street
                                            }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{
                                                props.order.delivery_address
                                                    .postal_code
                                            }}
                                            {{
                                                props.order.delivery_address
                                                    .city
                                            }},
                                            {{
                                                props.order.delivery_address
                                                    .country
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        props.order.delivery_type ===
                                            DeliveryTypeEnum.COMPANY &&
                                        props.order.delivery_company
                                    "
                                    class="flex items-start gap-3"
                                >
                                    <div
                                        class="flex-shrink-0 rounded-lg bg-red-50 p-2"
                                    >
                                        <Building2
                                            class="h-5 w-5 text-red-500"
                                        />
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">
                                            {{
                                                t(
                                                    'payment.order.deliveryCompany',
                                                )
                                            }}
                                        </p>

                                        <p class="font-medium text-gray-800">
                                            {{
                                                props.order.delivery_company
                                                    .name
                                            }}
                                        </p>

                                        <p
                                            v-if="props.order.delivery_date"
                                            class="mt-1 text-sm text-gray-600"
                                        >
                                            {{
                                                t('payment.order.deliveryDate')
                                            }}:
                                            {{
                                                formatDateOnly(
                                                    props.order.delivery_date,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border bg-white p-6">
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <Soup class="h-5 w-5 text-red-500" />
                                {{ t('payment.order.orderItems') }}
                            </h2>

                            <div class="space-y-3">
                                <div
                                    v-for="item in props.order.items"
                                    :key="item.id"
                                    class="border-b border-gray-100 pb-3 last:border-0"
                                >
                                    <div class="flex justify-between text-sm">
                                        <div class="flex-1">
                                            <div class="font-medium">
                                                {{ item.quantity }}x
                                                {{ item.item.name }}
                                            </div>
                                            <div
                                                class="mt-1 text-xs text-gray-500"
                                            >
                                                {{
                                                    t(
                                                        'payment.item.spicyLevel',
                                                    )
                                                }}:
                                                {{
                                                    spicyLevelLabels[
                                                        item.spicy_level
                                                    ]
                                                }}
                                            </div>
                                            <div
                                                v-if="item.meat"
                                                class="mt-1 text-xs text-gray-500"
                                            >
                                                {{ t('payment.item.meat') }}:
                                                {{ item.meat.name }}
                                                <span
                                                    v-if="
                                                        item.meat.extra_price >
                                                        0
                                                    "
                                                    class="text-gray-500"
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
                                                class="mt-1 text-xs text-gray-500"
                                            >
                                                {{ t('payment.item.without') }}:
                                                {{
                                                    item.removed_ingredients
                                                        .map((ing) => ing.name)
                                                        .join(', ')
                                                }}
                                            </div>
                                            <div
                                                v-if="item.notes"
                                                class="mt-1 text-xs text-gray-500"
                                            >
                                                {{ t('payment.item.notes') }}:
                                                {{ item.notes }}
                                            </div>
                                        </div>
                                        <div
                                            class="ml-4 font-medium whitespace-nowrap"
                                        >
                                            €{{
                                                formatPrice(item.total_inc_vat)
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="props.order.notes"
                                class="mt-4 rounded-md bg-yellow-50 p-3"
                            >
                                <p class="text-xs font-medium text-yellow-700">
                                    {{ t('payment.item.notes') }}
                                </p>
                                <p class="text-sm text-yellow-800">
                                    {{ props.order.notes }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:w-96">
                    <div class="sticky space-y-6 sm:top-20">
                        <div class="rounded-lg border bg-white p-6">
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <HandCoins class="h-5 w-5 text-red-500" />
                                {{ t('payment.summary.paymentSummary') }}
                            </h2>

                            <div
                                class="space-y-2 border-b border-gray-100 pb-4"
                            >
                                <div
                                    v-for="vat in props.order.vat_breakdown"
                                    :key="vat.vat_rate"
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>
                                        {{
                                            t('payment.summary.vat', {
                                                rate: vat.vat_rate,
                                            })
                                        }}
                                    </span>
                                    <span
                                        >€{{ formatPrice(vat.vat_total) }}</span
                                    >
                                </div>
                                <div
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>
                                        {{ t('payment.summary.totalVat') }}
                                    </span>
                                    <span>
                                        €{{
                                            formatPrice(props.order.vat_total)
                                        }}
                                    </span>
                                </div>
                                <div
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>
                                        {{ t('payment.summary.subtotal') }}
                                    </span>
                                    <span
                                        >€{{
                                            formatPrice(props.order.subtotal)
                                        }}</span
                                    >
                                </div>
                                <div
                                    v-if="
                                        props.order.type.value === 3 &&
                                        props.order.delivery_fee
                                    "
                                    class="flex justify-between text-sm text-red-600"
                                >
                                    <span>
                                        {{ t('payment.summary.deliveryFee') }}
                                    </span>
                                    <span
                                        >+€{{
                                            formatPrice(
                                                props.order.delivery_fee,
                                            )
                                        }}</span
                                    >
                                </div>
                                <div
                                    v-if="props.order.discount_total"
                                    class="flex justify-between text-sm text-green-600"
                                >
                                    <span>
                                        {{ t('payment.summary.discount') }}
                                    </span>
                                    <span
                                        >-€{{
                                            formatPrice(
                                                props.order.discount_total,
                                            )
                                        }}</span
                                    >
                                </div>
                            </div>

                            <div
                                class="mt-4 flex justify-between text-lg font-semibold"
                            >
                                <span>
                                    {{ t('payment.summary.totalAmount') }}
                                </span>
                                <span class="text-red-500"
                                    >€{{
                                        formatPrice(props.order.total_inc_vat)
                                    }}</span
                                >
                            </div>
                        </div>

                        <div class="rounded-lg border bg-white p-6">
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <CreditCard class="h-5 w-5 text-red-500" />
                                {{ t('payment.payment.paymentDetails') }}
                            </h2>

                            <div
                                id="stripe-payment-element"
                                class="mb-6 min-h-[120px]"
                            ></div>

                            <div
                                v-if="paymentError"
                                role="alert"
                                class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4"
                            >
                                <p class="font-medium text-red-800">
                                    {{
                                        t('payment.payment.paymentUnsuccessful')
                                    }}
                                </p>
                                <p class="mt-1 text-sm text-red-700">
                                    {{ paymentError }}
                                </p>
                                <p class="mt-2 text-xs text-red-600">
                                    {{ t('payment.payment.notCharged') }}
                                </p>
                            </div>

                            <button
                                type="button"
                                :disabled="
                                    isLoading || isInitializing || !stripe
                                "
                                @click="pay"
                                class="group relative w-full overflow-hidden rounded-lg bg-gradient-to-r from-red-500 to-red-600 py-3 text-sm text-white transition-all hover:shadow-md hover:shadow-red-200 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <span
                                    class="relative z-10 flex items-center justify-center gap-2 font-semibold"
                                >
                                    <span
                                        v-if="isLoading || isInitializing"
                                        class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                                    ></span>
                                    <CreditCard v-else class="h-4 w-4" />

                                    <span v-if="isInitializing">
                                        {{
                                            t(
                                                'payment.payment.loadingPaymentForm',
                                            )
                                        }}
                                    </span>
                                    <span v-else>
                                        {{
                                            t('payment.payment.pay', {
                                                amount: formatPrice(
                                                    props.order.total_inc_vat,
                                                ),
                                            })
                                        }}
                                    </span>
                                </span>
                                <div
                                    class="absolute inset-0 -translate-x-full transform bg-gradient-to-r from-red-600 to-red-700 transition-transform duration-300 group-hover:translate-x-0"
                                ></div>
                            </button>

                            <div class="mt-4 text-center text-xs text-gray-500">
                                <p>{{ t('payment.stripePowered') }}</p>
                                <p class="mt-1">
                                    {{ t('payment.payment.termsAgreement') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </ClientLayout>
</template>

<style scoped></style>
