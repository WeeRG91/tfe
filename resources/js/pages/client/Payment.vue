<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import { formatPrice } from '@/lib/utils';
import paymentOrder from '@/routes/payment-order';
import { OrderType } from '@/types/order';
import { Head } from '@inertiajs/vue3';
import { loadStripe } from '@stripe/stripe-js';
import axios from 'axios';
import {
    CreditCard,
    Lock,
    MapPin,
    Soup,
    User,
    Utensils,
    HandPlatter,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    order: OrderType;
}>();

console.log(props.order);

const stripe = ref<any>(null);
const elements = ref<any>(null);
const isLoading = ref<boolean>(false);

const getOrderTypeIcon = (typeLabel: string) => {
    const label = typeLabel?.toLowerCase();
    if (label === 'delivery') return MapPin;
    if (label === 'takeaway') return HandPlatter;
    return Utensils;
};

const initPayment = async () => {
    const { data } = await axios.get(
        paymentOrder.createPaymentIntent(props.order.id).url,
    );

    stripe.value = await loadStripe(import.meta.env.VITE_STRIPE_PUBLIC_KEY);

    elements.value = stripe.value.elements({
        clientSecret: data.client_secret,
    });

    const paymentElement = elements.value.create('payment');
    paymentElement.mount('#stripe-payment-element');
};

const pay = async () => {
    isLoading.value = true;

    const { error } = await stripe.value.confirmPayment({
        elements: elements.value,
        confirmParams: {
            return_url:
                window.location.origin +
                `/payments/${props.order.id}/payment-success`,
        },
    });

    if (error) {
        alert(error.message);
        isLoading.value = false;
    }
};

onMounted(() => {
    initPayment();
});
</script>

<template>
    <Head title="Payment" />

    <ClientLayout>
        <section class="mx-auto max-w-6xl px-6 py-4">
            <div class="mb-4">
                <p class="text-sm tracking-widest text-red-500 uppercase">
                    [ Secure Checkout ]
                </p>
                <h1 class="text-3xl font-semibold uppercase md:text-4xl">
                    Complete Payment
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Secure payment powered by Stripe
                </p>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row">
                <!-- Order Summary Section -->
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
                                    <p
                                        class="text-xl font-semibold text-gray-800"
                                    >
                                        #{{ props.order.order_number }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">Status</p>
                                    <span
                                        :class="props.order.status.color"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{ props.order.status.label }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Order Type & Details -->
                        <div class="rounded-lg border bg-white p-6">
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <component
                                    :is="
                                        getOrderTypeIcon(props.order.type.label)
                                    "
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
                                                    props.order.type.label,
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
                                            {{ props.order.type.label }}
                                        </p>
                                        <p
                                            v-if="
                                                props.order.type.label ===
                                                'Dine-in'
                                            "
                                            class="mt-1 text-sm text-gray-600"
                                        >
                                            Table:
                                            {{ props.order.table_number }}
                                        </p>
                                        <p
                                            v-if="
                                                props.order.type.label ===
                                                'Takeaway'
                                            "
                                            class="mt-1 text-sm text-gray-600"
                                        >
                                            Pickup:
                                            {{ props.order.pickup_time }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Customer Info for Takeaway -->
                                <div
                                    v-if="props.order.type.label === 'Takeaway'"
                                    class="flex items-start gap-3"
                                >
                                    <div
                                        class="flex-shrink-0 rounded-lg bg-red-50 p-2"
                                    >
                                        <User class="h-5 w-5 text-red-500" />
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Customer
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

                                <!-- Delivery Address -->
                                <div
                                    v-if="props.order.type.label === 'Delivery'"
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
                                            €{{ formatPrice(item.total) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div
                                v-if="props.order.notes"
                                class="mt-4 rounded-md bg-yellow-50 p-3"
                            >
                                <p class="text-xs font-medium text-yellow-700">
                                    Notes
                                </p>
                                <p class="text-sm text-yellow-800">
                                    {{ props.order.notes }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Sidebar -->
                <div class="lg:w-96">
                    <div class="sticky top-6 space-y-6">
                        <!-- Price Breakdown Card -->
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
                                    <span>Food</span>
                                    <span
                                        >€{{
                                            formatPrice(
                                                props.order.final_food_inc_vat,
                                            )
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>VAT ({{ props.order.vat_food_rate }}% - Food)</span>
                                    <span
                                        >€{{
                                            formatPrice(
                                                props.order.vat_food_amount,
                                            )
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>Drinks</span>
                                    <span
                                        >€{{
                                            formatPrice(
                                                props.order.final_drinks_inc_vat,
                                            )
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>VAT ({{ props.order.vat_drinks_rate }}% - Drinks)</span>
                                    <span
                                        >€{{
                                            formatPrice(
                                                props.order.vat_drinks_amount,
                                            )
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>Total VAT</span>
                                    <span
                                        >€{{
                                            formatPrice(
                                                props.order.vat_total,
                                            )
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>Subtotal</span>
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
                                    <span>Delivery fee</span>
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
                                    <span>Discount</span>
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
                                <span>Total Amount</span>
                                <span class="text-red-500"
                                    >€{{ formatPrice(props.order.total) }}</span
                                >
                            </div>
                        </div>

                        <!-- Payment Card -->
                        <div class="rounded-lg border bg-white p-6">
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <Lock class="h-5 w-5 text-red-500" />
                                Payment Details
                            </h2>

                            <div
                                id="stripe-payment-element"
                                class="mb-6 min-h-[120px]"
                            ></div>

                            <button
                                @click="pay"
                                class="group relative w-full overflow-hidden rounded-lg bg-gradient-to-r from-red-500 to-red-600 py-3 text-sm text-white transition-all hover:shadow-md hover:shadow-red-200"
                            >
                                <span
                                    class="relative z-10 flex items-center justify-center gap-2 font-semibold"
                                >
                                    <CreditCard class="h-4 w-4" />
                                    Pay €{{ formatPrice(props.order.total) }}
                                </span>
                                <div
                                    class="absolute inset-0 -translate-x-full transform bg-gradient-to-r from-red-600 to-red-700 transition-transform duration-300 group-hover:translate-x-0"
                                ></div>
                            </button>

                            <div class="mt-4 text-center text-xs text-gray-500">
                                <p>Secure payment powered by Stripe</p>
                                <p class="mt-1">
                                    By completing payment, you agree to our
                                    Terms of Service
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
