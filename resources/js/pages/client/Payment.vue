<script setup lang="ts">
import { useDateFormatter } from '@/composables/useDateFormatter';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { getSpicyLevelLabels } from '@/lib/const';
import {
    formatPrice,
    getOrderStatusVariant,
    getOrderTypeIcon,
} from '@/lib/utils';
import paymentOrder from '@/routes/payment-order';
import { DeliveryTypeEnum } from '@/types/delivery';
import { OrderType, OrderTypeEnum } from '@/types/order';
import { Head } from '@inertiajs/vue3';
import { loadStripe } from '@stripe/stripe-js';
import axios from 'axios';
import {
    Building2,
    CreditCard,
    HandCoins,
    MapPin,
    Soup,
    User,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

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
let themeObserver: MutationObserver | null = null;

const getThemeColor = (variable: string): string => {
    return getComputedStyle(document.documentElement)
        .getPropertyValue(variable)
        .trim();
};

const getStripeAppearance = () => ({
    theme: 'stripe' as const,
    variables: {
        colorPrimary: getThemeColor('--primary'),
        colorBackground: getThemeColor('--card'),
        colorText: getThemeColor('--card-foreground'),
        colorDanger: getThemeColor('--destructive'),
        fontFamily: getComputedStyle(document.body).fontFamily,
        borderRadius: getThemeColor('--radius'),
        spacingUnit: '4px',
    },
    rules: {
        '.Input': {
            borderColor: getThemeColor('--input'),
            backgroundColor: getThemeColor('--background'),
            color: getThemeColor('--foreground'),
            boxShadow: 'none',
        },
        '.Input:focus': {
            borderColor: getThemeColor('--ring'),
            boxShadow: `0 0 0 1px ${getThemeColor('--ring')}`,
        },
        '.Label': {
            color: getThemeColor('--foreground'),
        },
        '.Tab': {
            borderColor: getThemeColor('--border'),
            backgroundColor: getThemeColor('--background'),
            color: getThemeColor('--foreground'),
        },
        '.Tab:hover': {
            borderColor: getThemeColor('--primary'),
        },
        '.Tab--selected': {
            borderColor: getThemeColor('--primary'),
            color: getThemeColor('--primary'),
        },
    },
});

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
            appearance: getStripeAppearance(),
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

    themeObserver = new MutationObserver(() => {
        elements.value?.update({
            appearance: getStripeAppearance(),
        });
    });

    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-theme', 'data-theme-mode', 'class', 'style'],
    });
});

onUnmounted(() => {
    themeObserver?.disconnect();
});
</script>

<template>
    <Head :title="t('payment.title')" />

    <ClientLayout>
        <section class="mx-auto max-w-6xl px-6 py-10 sm:py-14">
            <div class="mb-4">
                <p class="text-sm tracking-widest text-primary uppercase">
                    [ {{ t('payment.secureCheckout') }} ]
                </p>
                <h1 class="text-4xl font-semibold uppercase md:text-5xl">
                    {{ t('payment.completePayment') }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ t('payment.stripePowered') }}
                </p>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row">
                <div class="flex-1">
                    <div class="space-y-6">
                        <div
                            class="rounded-lg border border-border bg-card p-6 text-card-foreground"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between gap-3"
                            >
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        {{ t('payment.order.orderNumber') }}
                                    </p>
                                    <p
                                        class="text-xl font-semibold text-card-foreground"
                                    >
                                        #{{ props.order.order_number }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-muted-foreground">
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

                        <div
                            class="rounded-lg border border-border bg-card p-6 text-card-foreground"
                        >
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <component
                                    :is="
                                        getOrderTypeIcon(props.order.type.value)
                                    "
                                    class="h-5 w-5 text-primary"
                                />
                                {{ t('payment.order.orderDetails') }}
                            </h2>

                            <div class="space-y-4">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex-shrink-0 rounded-lg bg-primary/10 p-2"
                                    >
                                        <component
                                            :is="
                                                getOrderTypeIcon(
                                                    props.order.type.value,
                                                )
                                            "
                                            class="h-5 w-5 text-primary"
                                        />
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ t('payment.order.orderType') }}
                                        </p>
                                        <p
                                            class="font-medium text-card-foreground capitalize"
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
                                            class="mt-1 text-sm text-muted-foreground"
                                        >
                                            {{ t('payment.order.table') }}:
                                            {{ props.order.table_number }}
                                        </p>
                                        <p
                                            v-if="
                                                props.order.type.value ===
                                                OrderTypeEnum.TAKEAWAY
                                            "
                                            class="mt-1 text-sm text-muted-foreground"
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
                                        class="flex-shrink-0 rounded-lg bg-primary/10 p-2"
                                    >
                                        <User class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ t('payment.order.customer') }}
                                        </p>
                                        <p
                                            class="font-medium text-card-foreground"
                                        >
                                            {{
                                                props.order.pickup_name || 'N/A'
                                            }}
                                        </p>
                                        <p
                                            v-if="props.order.pickup_phone"
                                            class="text-sm text-muted-foreground"
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
                                        class="flex-shrink-0 rounded-lg bg-primary/10 p-2"
                                    >
                                        <MapPin class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{
                                                t(
                                                    'payment.order.deliveryAddress',
                                                )
                                            }}
                                        </p>
                                        <p
                                            class="font-medium text-card-foreground"
                                        >
                                            {{
                                                props.order.delivery_address
                                                    .first_name
                                            }}
                                            {{
                                                props.order.delivery_address
                                                    .last_name
                                            }}
                                        </p>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{
                                                props.order.delivery_address
                                                    .phone
                                            }}
                                        </p>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{
                                                props.order.delivery_address
                                                    .street
                                            }}
                                        </p>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
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
                                        class="flex-shrink-0 rounded-lg bg-primary/10 p-2"
                                    >
                                        <Building2
                                            class="h-5 w-5 text-primary"
                                        />
                                    </div>

                                    <div>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{
                                                t(
                                                    'payment.order.deliveryCompany',
                                                )
                                            }}
                                        </p>

                                        <p
                                            class="font-medium text-card-foreground"
                                        >
                                            {{
                                                props.order.delivery_company
                                                    .name
                                            }}
                                        </p>

                                        <p
                                            v-if="props.order.delivery_date"
                                            class="mt-1 text-sm text-muted-foreground"
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

                        <div
                            class="rounded-lg border border-border bg-card p-6 text-card-foreground"
                        >
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <Soup class="h-5 w-5 text-primary" />
                                {{ t('payment.order.orderItems') }}
                            </h2>

                            <div class="space-y-3">
                                <div
                                    v-for="item in props.order.items"
                                    :key="item.id"
                                    class="border-b border-border pb-3 last:border-0"
                                >
                                    <div class="flex justify-between text-sm">
                                        <div class="flex-1">
                                            <div class="font-medium">
                                                {{ item.quantity }}x
                                                {{ item.item.name }}
                                            </div>
                                            <div
                                                class="mt-1 text-xs text-muted-foreground"
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
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                {{ t('payment.item.meat') }}:
                                                {{ item.meat.name }}
                                                <span
                                                    v-if="
                                                        item.meat.extra_price >
                                                        0
                                                    "
                                                    class="text-muted-foreground"
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
                                                class="mt-1 text-xs text-muted-foreground"
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
                                                class="mt-1 text-xs text-muted-foreground"
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
                                class="mt-4 rounded-md border border-warning/20 bg-warning/10 p-3"
                            >
                                <p class="text-xs font-medium text-warning">
                                    {{ t('payment.item.notes') }}
                                </p>
                                <p class="text-sm text-card-foreground">
                                    {{ props.order.notes }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:w-96">
                    <div class="sticky space-y-6 sm:top-20">
                        <div
                            class="rounded-lg border border-border bg-card p-6 text-card-foreground"
                        >
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <HandCoins class="h-5 w-5 text-primary" />
                                {{ t('payment.summary.paymentSummary') }}
                            </h2>

                            <div class="space-y-2 border-b border-border pb-4">
                                <div
                                    v-for="vat in props.order.vat_breakdown"
                                    :key="vat.vat_rate"
                                    class="flex justify-between text-sm text-muted-foreground"
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
                                    class="flex justify-between text-sm text-muted-foreground"
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
                                    class="flex justify-between text-sm text-muted-foreground"
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
                                    class="flex justify-between text-sm text-primary"
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
                                    class="flex justify-between text-sm text-success"
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
                                <span class="text-primary"
                                    >€{{
                                        formatPrice(props.order.total_inc_vat)
                                    }}</span
                                >
                            </div>
                        </div>

                        <div
                            class="rounded-lg border border-border bg-card p-6 text-card-foreground"
                        >
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <CreditCard class="h-5 w-5 text-primary" />
                                {{ t('payment.payment.paymentDetails') }}
                            </h2>

                            <div
                                id="stripe-payment-element"
                                class="mb-6 min-h-[120px]"
                            ></div>

                            <div
                                v-if="paymentError"
                                role="alert"
                                class="mb-4 rounded-lg border border-destructive/30 bg-destructive/10 p-4"
                            >
                                <p class="font-medium text-destructive">
                                    {{
                                        t('payment.payment.paymentUnsuccessful')
                                    }}
                                </p>
                                <p class="mt-1 text-sm text-destructive">
                                    {{ paymentError }}
                                </p>
                                <p class="mt-2 text-xs text-destructive">
                                    {{ t('payment.payment.notCharged') }}
                                </p>
                            </div>

                            <button
                                type="button"
                                :disabled="
                                    isLoading || isInitializing || !stripe
                                "
                                @click="pay"
                                class="group relative w-full overflow-hidden rounded-lg bg-gradient-to-r from-primary to-primary/80 py-3 text-sm text-primary-foreground transition-all hover:shadow-md hover:shadow-primary/20 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <span
                                    class="relative z-10 flex items-center justify-center gap-2 font-semibold"
                                >
                                    <span
                                        v-if="isLoading || isInitializing"
                                        class="h-4 w-4 animate-spin rounded-full border-2 border-primary-foreground border-t-transparent"
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
                                    class="absolute inset-0 -translate-x-full transform bg-gradient-to-r from-primary/90 to-primary transition-transform duration-300 group-hover:translate-x-0"
                                ></div>
                            </button>

                            <div
                                class="mt-4 text-center text-xs text-muted-foreground"
                            >
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
