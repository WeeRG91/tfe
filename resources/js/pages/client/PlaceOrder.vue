<script setup lang="ts">
import AddAddressModal from '@/components/client/address/AddAddressModal.vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { formatPrice } from '@/lib/utils';
import address from '@/routes/address';
import order from '@/routes/order';
import paymentOrder from '@/routes/payment-order';
import { useCartStore } from '@/stores/cart';
import { useOrderStore } from '@/stores/order';
import { AddressType } from '@/types/address';
import { OrderTypeType } from '@/types/order';
import { PaymentMethodType } from '@/types/payment';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    Check,
    CreditCard,
    Edit,
    Gift,
    Home,
    MapPin,
    Phone,
    Plus,
    Sparkles,
    Star,
    Store,
    Table,
    Trash,
    Truck,
    User,
    NotebookPen,
    Soup,
    CalendarDays,
    HandCoins
} from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    orderTypes: OrderTypeType[];
    paymentMethods: PaymentMethodType[];
    addresses: AddressType[];
}>();

const cartStore = useCartStore();
const orderStore = useOrderStore();
const { cart, items } = storeToRefs(cartStore);
const { isLoading: isOrderLoading } = storeToRefs(orderStore);

const selectedOrderTypeValue = ref<number | null>(null);
const selectedAddressId = ref<number | null>(null);
const paymentMethod = ref<number | null>(null);
const pickupTime = ref<string>('');
const pickupName = ref<string>('');
const pickupPhone = ref<string>('');
const tableNumber = ref<string>('');
const notes = ref<string>('');
const showAddressModal = ref(false);
const editingAddress = ref<AddressType | null>(null);
const existingAddresses = ref<AddressType[]>(props.addresses ?? []);
const deletedAddressId = ref<number | null>(null);
const deliveryFee = ref<number>(2);
const selectedDiscountType = ref<'points' | null>(null);
const selectedPointsOption = ref<number>(0);

const pointsOptions = [
    { points: 300, discount: 5, label: '300 points → €5 off' },
    { points: 550, discount: 10, label: '550 points → €10 off' },
];

const loyaltyPoints = computed(() => cart.value?.user?.loyalty_points ?? 0);

const selectedOrderType = computed(() => {
    return props.orderTypes.find(
        (type) => type.value === selectedOrderTypeValue.value,
    );
});

// Calculate discount amount based on selected points
const discountAmount = computed(() => {
    if (!selectedDiscountType.value || !selectedPointsOption.value) return 0;

    const selectedOption = pointsOptions.find(
        (opt) => opt.points === selectedPointsOption.value,
    );
    return selectedOption ? selectedOption.discount : 0;
});

// Check if user has enough points for each option
const isPointsOptionAvailable = (pointsRequired: number) => {
    return loyaltyPoints.value >= pointsRequired;
};

const earnedPoints = computed(() => {
    return Math.floor(cartTotal.value * 3);
});

// Minimum pickup time (current time + 30 minutes)
const minPickupTime = computed(() => {
    const date = new Date();
    date.setMinutes(date.getMinutes() + 30);
    date.setSeconds(0);
    date.setMilliseconds(0);
    return date.toISOString().slice(0, 16);
});

// Maximum pickup time (7 days from now)
const maxPickupTime = computed(() => {
    const date = new Date();
    date.setDate(date.getDate() + 7);
    return date.toISOString().slice(0, 16);
});

const dishItems = computed(() =>
    items.value.filter((item) => item.item_type === 'dish'),
);

const drinkItems = computed(() =>
    items.value.filter((item) => item.item_type === 'drink'),
);

const subtotalDishes = computed(() => {
    return dishItems.value.reduce((sum, item) => sum + (item.total || 0), 0);
});

const subtotalDrinks = computed(() => {
    return drinkItems.value.reduce((sum, item) => sum + (item.total || 0), 0);
});

const subtotal = computed(() => {
    return subtotalDishes.value + subtotalDrinks.value;
});

const totalBeforeDiscount = computed(() => {
    return selectedOrderTypeValue.value === 3
        ? subtotal.value + deliveryFee.value
        : subtotal.value;
});

const foodRatio = computed(() => {
    return totalBeforeDiscount.value > 0
        ? subtotalDishes.value / totalBeforeDiscount.value
        : 0;
});

const drinksRatio = computed(() => {
    return totalBeforeDiscount.value > 0
        ? subtotalDrinks.value / totalBeforeDiscount.value
        : 0;
});

const cartTotal = computed(() => {
    return Math.max(totalBeforeDiscount.value - discountAmount.value, 0);
});

const finalFoodInclVat = computed(() => cartTotal.value * foodRatio.value);
const finalDrinksInclVat = computed(() => cartTotal.value * drinksRatio.value);

const vatFood = computed(() => {
    return finalFoodInclVat.value - finalFoodInclVat.value / 1.12;
});

const vatDrinks = computed(() => {
    return finalDrinksInclVat.value - finalDrinksInclVat.value / 1.21;
});

const totalVat = computed(() => vatFood.value + vatDrinks.value);

const pickupTimeFormatted = computed(() => {
    if (!pickupTime.value) return '';
    const date = new Date(pickupTime.value);
    return date.toLocaleString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
});

const isFormValid = computed(() => {
    if (!selectedOrderType.value) return false;
    if (!paymentMethod.value) return false;

    if (selectedOrderType.value.value === 1) {
        return tableNumber.value.trim().length > 0;
    }

    if (selectedOrderType.value.value === 2) {
        return (
            pickupTime.value &&
            pickupTime.value >= minPickupTime.value &&
            pickupName.value.trim().length > 0 &&
            pickupPhone.value.trim().length > 0
        );
    }

    if (selectedOrderType.value.value === 3) {
        return selectedAddressId.value !== null;
    }

    return false;
});

const selectAddress = (addressId: number) => {
    selectedAddressId.value = addressId;
};

const addNewAddress = () => {
    editingAddress.value = null;
    showAddressModal.value = true;
};

const editAddress = (address: AddressType) => {
    editingAddress.value = address;
    showAddressModal.value = true;
};

const onAddressSaved = async (savedAddress: AddressType) => {
    const index = existingAddresses.value.findIndex(
        (a) => a.id === savedAddress.id,
    );

    if (index !== -1) {
        existingAddresses.value[index] = savedAddress;
    } else {
        existingAddresses.value = [...existingAddresses.value, savedAddress];
    }

    if (savedAddress.is_default) {
        existingAddresses.value = existingAddresses.value.map((a) => ({
            ...a,
            is_default: a.id === savedAddress.id,
        }));
    }
};

const deleteAddress = async (addressId: number) => {
    deletedAddressId.value = addressId;

    try {
        const { data } = await axios.delete(address.destroy(addressId).url);

        existingAddresses.value = existingAddresses.value.filter(
            (a) => a.id !== addressId,
        );

        console.log(data);
        console.log(data.newDefaultAddressId);

        if (data.newDefaultAddressId) {
            existingAddresses.value = existingAddresses.value.map((a) => ({
                ...a,
                is_default: a.id === data.newDefaultAddressId,
            }));
        }

        const defaultAddress = existingAddresses.value.find(
            (a) => a.is_default,
        );
        selectedAddressId.value = defaultAddress ? defaultAddress.id : null;
    } catch (error) {
        console.log(error);
        toast.error('Failed to place order');
    } finally {
        deletedAddressId.value = null;
    }
};

const applyPointsDiscount = (points: number) => {
    selectedDiscountType.value = 'points';
    selectedPointsOption.value = points;
};

const removeDiscount = () => {
    selectedDiscountType.value = null;
    selectedPointsOption.value = 0;
};

const placeOrder = async () => {
    if (!isFormValid.value || !selectedOrderType.value) {
        toast.error('Please complete all required fields');
        return;
    }

    try {
        const response = await orderStore.placeOrder({
            cart_id: cart.value!.id,
            type: selectedOrderTypeValue.value!,
            table_number: tableNumber.value,
            pickup_time: pickupTime.value,
            pickup_name: pickupName.value,
            pickup_phone: pickupPhone.value,
            address_id: selectedAddressId.value,
            payment_method: paymentMethod.value!,
            notes: notes.value,
            used_points: selectedPointsOption.value,
        });

        toast.success(response.message);

        const placedOrder = response.order;

        if (paymentMethod.value === 1) {
            router.visit(order.orderDetails(placedOrder.id).url);
        } else {
            router.visit(paymentOrder.payment(placedOrder.id).url);
        }
    } catch (error) {
        console.log(error);
        toast.error('Failed to place order');
    }
};

const onOrderTypeChange = async (typeValue: number) => {
    selectedOrderTypeValue.value = typeValue;
    paymentMethod.value = null;

    if (typeValue === 1) {
        pickupName.value = '';
        pickupPhone.value = '';
        pickupTime.value = '';
        selectedAddressId.value = null;
    } else if (typeValue === 2) {
        pickupTime.value = minPickupTime.value;
        tableNumber.value = '';
        selectedAddressId.value = null;
    } else if (typeValue === 3) {
        tableNumber.value = '';
        pickupName.value = '';
        pickupPhone.value = '';
        pickupTime.value = '';
        const defaultAddress = existingAddresses.value.find(
            (a) => a.is_default,
        );
        selectedAddressId.value = defaultAddress ? defaultAddress.id : null;
    }

    // Reset discount when order type changes
    removeDiscount();
};

const getOrderTypeIcon = (orderType: OrderTypeType) => {
    switch (orderType.value) {
        case 1:
            return Table;
        case 2:
            return Store;
        case 3:
            return Truck;
        default:
            return Store;
    }
};

const getPaymentIcon = (method: PaymentMethodType) => {
    switch (method.value) {
        case 1:
            return '💵';
        case 2:
            return '💳';
        default:
            return '💰';
    }
};

onMounted(() => {
    const defaultAddress = existingAddresses.value.find((a) => a.is_default);
    selectedAddressId.value = defaultAddress ? defaultAddress.id : null;
});
</script>

<template>
    <Head title="Place Order" />

    <ClientLayout>
        <section class="mx-auto max-w-6xl px-6 py-4">
            <div class="mb-4">
                <p class="text-sm tracking-widest text-red-500 uppercase">
                    [ Secure Checkout ]
                </p>
                <h1 class="text-3xl font-semibold uppercase md:text-4xl">
                    Place Your Order
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Complete your order details below
                </p>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row">
                <!-- Order Form Section -->
                <div class="flex-1">
                    <div class="space-y-6">
                        <!-- Order Type Selection -->
                        <div class="rounded-lg border bg-white p-6">
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <Store class="h-5 w-5" />
                                Order Type
                            </h2>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <button
                                    v-for="orderType in orderTypes"
                                    :key="orderType.value"
                                    @click="onOrderTypeChange(orderType.value)"
                                    :class="[
                                        'flex items-center justify-center gap-2 rounded-lg border-2 p-4 transition-all',
                                        selectedOrderTypeValue ===
                                        orderType.value
                                            ? 'border-red-500 bg-red-50'
                                            : 'border-gray-200 hover:border-red-200',
                                    ]"
                                >
                                    <component
                                        :is="getOrderTypeIcon(orderType)"
                                        class="h-5 w-5"
                                        :class="
                                            selectedOrderTypeValue ===
                                            orderType.value
                                                ? 'text-red-500'
                                                : 'text-gray-400'
                                        "
                                    />
                                    <span class="font-medium">{{
                                        orderType.label
                                    }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Dine In Details -->
                        <div
                            v-if="selectedOrderType?.value === 1"
                            class="rounded-lg border bg-white p-6"
                        >
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <Table class="h-5 w-5" />
                                Table Details
                            </h2>

                            <div>
                                <label
                                    class="mb-1 block text-sm font-medium text-gray-700"
                                >
                                    Table Number *
                                </label>
                                <input
                                    v-model="tableNumber"
                                    type="text"
                                    placeholder="e.g., 12, A3, 5B"
                                    class="w-full rounded-md border border-gray-300 p-2.5 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    Enter your table number for dine-in service
                                </p>
                            </div>
                        </div>

                        <!-- Takeaway Details -->
                        <div
                            v-if="selectedOrderType?.value === 2"
                            class="rounded-lg border bg-white p-6"
                        >
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <CalendarDays class="h-5 w-5" />
                                Pickup Information
                            </h2>

                            <div class="space-y-4">
                                <!-- Pickup Name -->
                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-gray-700"
                                    >
                                        Pickup Name *
                                    </label>
                                    <div class="relative">
                                        <User
                                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                                        />
                                        <input
                                            v-model="pickupName"
                                            type="text"
                                            placeholder="Full name for pickup"
                                            class="w-full rounded-md border border-gray-300 py-2.5 pr-3 pl-10 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                                        />
                                    </div>
                                </div>

                                <!-- Pickup Phone -->
                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-gray-700"
                                    >
                                        Pickup Phone *
                                    </label>
                                    <div class="relative">
                                        <Phone
                                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                                        />
                                        <input
                                            v-model="pickupPhone"
                                            type="tel"
                                            placeholder="Phone number for contact"
                                            class="w-full rounded-md border border-gray-300 py-2.5 pr-3 pl-10 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                                        />
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        We'll send order updates to this number
                                    </p>
                                </div>

                                <!-- Pickup Time -->
                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-gray-700"
                                    >
                                        Preferred Pickup Time *
                                    </label>
                                    <input
                                        v-model="pickupTime"
                                        type="datetime-local"
                                        :min="minPickupTime"
                                        :max="maxPickupTime"
                                        class="w-full rounded-md border border-gray-300 p-2.5 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">
                                        Minimum 30 minutes from now, up to 7
                                        days in advance
                                    </p>
                                    <div
                                        v-if="pickupTime"
                                        class="mt-2 rounded-md bg-blue-50 p-2 text-sm text-blue-700"
                                    >
                                        📅 Your order will be ready at:
                                        {{ pickupTimeFormatted }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Details -->
                        <div
                            v-if="selectedOrderType?.value === 3"
                            class="rounded-lg border bg-white p-6"
                        >
                            <div class="mb-4 flex items-center justify-between">
                                <h2
                                    class="flex items-center gap-2 text-lg font-semibold uppercase"
                                >
                                    <MapPin class="h-5 w-5" />
                                    Delivery Address
                                </h2>
                                <button
                                    v-if="existingAddresses.length > 0"
                                    @click="addNewAddress"
                                    class="inline-flex items-center gap-2 rounded-full bg-red-500 p-1 text-sm text-white transition hover:bg-red-600"
                                >
                                    <Plus class="h-4 w-4" />
                                </button>
                            </div>

                            <div
                                v-if="existingAddresses.length > 0"
                                class="space-y-4"
                            >
                                <div
                                    v-for="addr in existingAddresses"
                                    :key="addr.id"
                                    :class="[
                                        'overflow-hidden rounded-xl transition-all duration-200',
                                        selectedAddressId === addr.id
                                            ? 'shadow-lg ring-2 ring-red-500'
                                            : 'border border-gray-200 hover:shadow-md',
                                    ]"
                                >
                                    <div
                                        :class="[
                                            'p-5',
                                            selectedAddressId === addr.id
                                                ? 'bg-gradient-to-r from-red-50 to-red-50/30'
                                                : 'bg-white hover:bg-gray-50/50',
                                        ]"
                                    >
                                        <div
                                            class="flex items-start justify-between"
                                        >
                                            <!-- Main content area (clickable) -->
                                            <div
                                                class="flex-1 cursor-pointer"
                                                @click="selectAddress(addr.id)"
                                            >
                                                <div
                                                    class="flex flex-wrap items-center gap-2"
                                                >
                                                    <div
                                                        class="flex items-center gap-2"
                                                    >
                                                        <div
                                                            :class="[
                                                                'rounded-full p-1',
                                                                selectedAddressId ===
                                                                addr.id
                                                                    ? 'bg-red-100'
                                                                    : 'bg-gray-100',
                                                            ]"
                                                        >
                                                            <User
                                                                :class="[
                                                                    'h-4 w-4',
                                                                    selectedAddressId ===
                                                                    addr.id
                                                                        ? 'text-red-600'
                                                                        : 'text-gray-500',
                                                                ]"
                                                            />
                                                        </div>
                                                        <span
                                                            class="font-semibold text-gray-800"
                                                            >{{
                                                                addr.first_name
                                                            }}
                                                            {{
                                                                addr.last_name
                                                            }}</span
                                                        >
                                                    </div>
                                                    <span
                                                        v-if="addr.is_default"
                                                        class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700"
                                                    >
                                                        <Star class="h-3 w-3" />
                                                        Default
                                                    </span>
                                                </div>

                                                <div
                                                    class="mt-3 space-y-1.5 text-sm text-gray-600"
                                                >
                                                    <div
                                                        class="flex items-center gap-2"
                                                    >
                                                        <Phone
                                                            class="h-3.5 w-3.5 text-gray-400"
                                                        />
                                                        <span>{{
                                                            addr.phone
                                                        }}</span>
                                                    </div>
                                                    <div
                                                        class="flex items-start gap-2"
                                                    >
                                                        <MapPin
                                                            class="mt-0.5 h-3.5 w-3.5 text-gray-400"
                                                        />
                                                        <div
                                                            class="flex flex-col"
                                                        >
                                                            <span>{{
                                                                addr.street
                                                            }}</span>
                                                            <span
                                                                >{{
                                                                    addr.city
                                                                }},
                                                                {{
                                                                    addr.postal_code
                                                                }}</span
                                                            >
                                                            <span>{{
                                                                addr.country
                                                            }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Action buttons -->
                                            <div
                                                class="ml-4 flex items-center gap-1"
                                            >
                                                <button
                                                    @click.stop="
                                                        editAddress(addr)
                                                    "
                                                    class="rounded-lg p-2 text-gray-400 transition-all hover:bg-gray-100 hover:text-blue-600"
                                                    title="Edit address"
                                                >
                                                    <Edit class="h-4 w-4" />
                                                </button>
                                                <span
                                                    v-if="
                                                        deletedAddressId ===
                                                        addr.id
                                                    "
                                                    class="m-2 h-4 w-4 animate-spin rounded-full border-2 border-red-500 border-t-transparent"
                                                ></span>
                                                <button
                                                    v-else
                                                    @click="
                                                        deleteAddress(addr.id)
                                                    "
                                                    class="rounded-lg p-2 text-gray-400 transition-all hover:bg-red-50 hover:text-red-600"
                                                    title="Delete address"
                                                >
                                                    <Trash class="h-4 w-4" />
                                                </button>
                                                <div
                                                    v-if="
                                                        selectedAddressId ===
                                                        addr.id
                                                    "
                                                    class="ml-1 rounded-full bg-red-500 p-1"
                                                >
                                                    <Check
                                                        class="h-4 w-4 text-white"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-else
                                class="rounded-lg border-2 border-dashed border-gray-300 p-8 text-center"
                            >
                                <MapPin
                                    class="mx-auto h-12 w-12 text-gray-400"
                                />
                                <p class="mt-2 text-sm text-gray-600">
                                    No saved addresses found
                                </p>
                                <button
                                    @click="addNewAddress"
                                    class="mt-3 inline-flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-sm text-white transition hover:bg-red-600"
                                >
                                    <Home class="h-4 w-4" />
                                    Add New Address
                                </button>
                            </div>
                        </div>

                        <!-- Payment Method Selection -->
                        <div
                            v-if="selectedOrderTypeValue"
                            class="rounded-lg border bg-white p-6"
                        >
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <HandCoins class="h-5 w-5" />
                                Payment Method
                            </h2>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <button
                                    v-for="method in paymentMethods"
                                    :key="method.value"
                                    @click="paymentMethod = method.value"
                                    :class="[
                                        'flex items-center justify-center gap-3 rounded-lg border-2 p-4 transition-all',
                                        paymentMethod === method.value
                                            ? 'border-red-500 bg-red-50'
                                            : 'border-gray-200 hover:border-red-200',
                                    ]"
                                >
                                    <span class="text-2xl">
                                        {{ getPaymentIcon(method) }}
                                    </span>
                                    <div class="text-left">
                                        <div class="font-medium">
                                            {{ method.label }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{
                                                method.value === 1
                                                    ? 'Pay after receiving your order or your meal'
                                                    : 'Pay securely with card'
                                            }}
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Order Notes -->
                        <div class="rounded-lg border bg-white p-6">
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <NotebookPen class="h-5 w-5" />
                                Order Notes (Optional)
                            </h2>

                            <textarea
                                v-model="notes"
                                rows="3"
                                placeholder="Any special requests or dietary requirements?"
                                class="w-full rounded-md border border-gray-300 p-2.5 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                We'll do our best to accommodate your requests
                            </p>
                        </div>

                        <!-- Loyalty Points Section -->
                        <div
                            class="rounded-lg border bg-gradient-to-r from-amber-50 to-yellow-50 p-6"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="mb-3 flex items-center gap-2">
                                        <Sparkles
                                            class="h-5 w-5 text-amber-500"
                                        />
                                        <h2
                                            class="text-lg font-semibold text-amber-800"
                                        >
                                            Loyalty Rewards
                                        </h2>
                                    </div>

                                    <div class="mb-4">
                                        <p class="text-sm text-amber-700">
                                            You have
                                            <span
                                                class="font-bold text-amber-900"
                                                >{{ loyaltyPoints }}</span
                                            >
                                            loyalty points
                                        </p>
                                        <p class="mt-1 text-xs text-amber-600">
                                            ✨ Earn 5 points per €1 spent on
                                            this order
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <p
                                            class="text-sm font-medium text-amber-800"
                                        >
                                            Redeem your points:
                                        </p>
                                        <div class="flex flex-wrap gap-3">
                                            <button
                                                v-for="option in pointsOptions"
                                                :key="option.points"
                                                @click="
                                                    applyPointsDiscount(
                                                        option.points,
                                                    )
                                                "
                                                :disabled="
                                                    !isPointsOptionAvailable(
                                                        option.points,
                                                    )
                                                "
                                                :class="[
                                                    'rounded-lg px-4 py-2 text-sm font-medium transition-all',
                                                    selectedPointsOption ===
                                                    option.points
                                                        ? 'bg-amber-600 text-white ring-2 ring-amber-400'
                                                        : 'border border-amber-300 bg-white text-amber-700 hover:bg-amber-100',
                                                    !isPointsOptionAvailable(
                                                        option.points,
                                                    ) &&
                                                    selectedPointsOption !==
                                                        option.points
                                                        ? 'cursor-not-allowed opacity-50 hover:bg-white'
                                                        : '',
                                                ]"
                                                :title="
                                                    !isPointsOptionAvailable(
                                                        option.points,
                                                    )
                                                        ? `Need ${option.points} points, you have ${loyaltyPoints}`
                                                        : ''
                                                "
                                            >
                                                {{ option.label }}
                                                <span
                                                    v-if="
                                                        !isPointsOptionAvailable(
                                                            option.points,
                                                        )
                                                    "
                                                    class="ml-1 text-xs"
                                                >
                                                    (Need
                                                    {{
                                                        option.points -
                                                        loyaltyPoints
                                                    }}
                                                    more)
                                                </span>
                                            </button>
                                        </div>

                                        <!-- Show message when user has points but not enough for any option -->
                                        <div
                                            v-if="
                                                loyaltyPoints > 0 &&
                                                loyaltyPoints < 300
                                            "
                                            class="mt-2 rounded-md bg-amber-100/50 p-2"
                                        >
                                            <p class="text-xs text-amber-700">
                                                💡 You need
                                                {{ 300 - loyaltyPoints }} more
                                                points to unlock your first
                                                discount! Complete this order to
                                                earn {{ earnedPoints }} points.
                                            </p>
                                        </div>

                                        <!-- Show info when user has 0 points -->
                                        <div
                                            v-if="loyaltyPoints === 0"
                                            class="mt-2 rounded-md bg-amber-100/50 p-2"
                                        >
                                            <p class="text-xs text-amber-700">
                                                💡 Start earning points with
                                                every order! You'll earn
                                                {{ earnedPoints }} points from
                                                this order.
                                            </p>
                                        </div>

                                        <div
                                            v-if="selectedPointsOption"
                                            class="mt-3 flex items-center justify-between rounded-md bg-amber-100 p-3"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <Gift
                                                    class="h-4 w-4 text-amber-600"
                                                />
                                                <span
                                                    class="text-sm text-amber-800"
                                                >
                                                    €{{ discountAmount }}
                                                    discount applied
                                                </span>
                                            </div>
                                            <button
                                                @click="removeDiscount"
                                                class="text-xs text-amber-600 underline hover:text-amber-800"
                                            >
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items Summary -->
                        <div class="rounded-lg border bg-white p-6">
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
                            >
                                <Soup class="h-5 w-5" />
                                Order Summary
                            </h2>

                            <div class="space-y-3">
                                <div
                                    v-for="item in items"
                                    :key="item.id"
                                    class="border-b border-gray-100 pb-2 last:border-0"
                                >
                                    <div class="flex justify-between text-sm">
                                        <div class="flex-1">
                                            <div class="font-medium">
                                                {{ item.quantity }}x
                                                {{ item.item.name }}
                                            </div>

                                            <!-- Selected Meat -->
                                            <div
                                                v-if="item.meat"
                                                class="mt-1 text-xs text-gray-600"
                                            >
                                                <span class="font-medium"
                                                    >Meat:</span
                                                >
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

                                            <!-- Removed Ingredients -->
                                            <div
                                                v-if="
                                                    item.removed_ingredients &&
                                                    item.removed_ingredients
                                                        .length > 0
                                                "
                                                class="mt-1 text-xs text-gray-500"
                                            >
                                                <span
                                                    class="font-medium text-gray-600"
                                                    >Without:</span
                                                >
                                                <span class="ml-1">
                                                    {{
                                                        item.removed_ingredients
                                                            .map(
                                                                (ing) =>
                                                                    ing.name,
                                                            )
                                                            .join(', ')
                                                    }}
                                                </span>
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

                            <div class="mt-3 space-y-1 border-t pt-3">
                                <div
                                    v-if="finalFoodInclVat"
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>Food</span>
                                    <span
                                        >€{{
                                            formatPrice(
                                                finalFoodInclVat - vatFood,
                                            )
                                        }}</span
                                    >
                                </div>
                                <div
                                    v-if="finalDrinksInclVat"
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>Drink</span>
                                    <span
                                        >€{{
                                            formatPrice(
                                                finalDrinksInclVat - vatDrinks,
                                            )
                                        }}</span
                                    >
                                </div>
                                <div
                                    v-if="vatFood"
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>VAT (12% - Food)</span>
                                    <span>€{{ formatPrice(vatFood) }}</span>
                                </div>
                                <div
                                    v-if="vatDrinks"
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>VAT (21% - Drinks)</span>
                                    <span>€{{ formatPrice(vatDrinks) }}</span>
                                </div>
                                <div
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>Total VAT</span>
                                    <span>€{{ formatPrice(totalVat) }}</span>
                                </div>
                                <div
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <span>Subtotal</span>
                                    <span>€{{ formatPrice(subtotal) }}</span>
                                </div>
                                <div
                                    v-if="selectedOrderType?.value === 3"
                                    class="flex justify-between border-t border-red-100 pt-2 text-sm text-red-600"
                                >
                                    <span>Delivery Fee</span>
                                    <span
                                        >+€{{ formatPrice(deliveryFee) }}</span
                                    >
                                </div>
                                <div
                                    v-if="discountAmount > 0"
                                    class="flex justify-between border-t border-green-100 pt-2 text-sm text-green-600"
                                >
                                    <span>Discount (Loyalty Points)</span>
                                    <span
                                        >-€{{
                                            formatPrice(discountAmount)
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex justify-between pt-2 text-base font-semibold"
                                >
                                    <span>Total</span>
                                    <span class="text-red-500"
                                        >€{{ formatPrice(cartTotal) }}</span
                                    >
                                </div>

                                <!-- Points to earn -->
                                <div
                                    class="mt-2 flex justify-between border-t border-gray-100 pt-2 text-xs text-amber-600"
                                >
                                    <span>🌟 Points to earn on this order</span>
                                    <span class="font-medium"
                                        >{{ earnedPoints }} points</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:w-80">
                    <div class="sticky top-6 rounded-lg border bg-white p-6">
                        <h2 class="mb-4 text-lg font-semibold uppercase">
                            Ready to Order?
                        </h2>

                        <div class="space-y-3">
                            <div class="rounded-md bg-gray-50 p-3">
                                <div class="flex justify-between text-sm">
                                    <span>Items:</span>
                                    <span>{{ items.length }} item(s)</span>
                                </div>
                                <div
                                    class="mt-1 flex justify-between text-sm font-semibold"
                                >
                                    <span>Total:</span>
                                    <span class="text-red-500"
                                        >€{{ formatPrice(cartTotal) }}</span
                                    >
                                </div>
                            </div>

                            <!-- Form validation summary -->
                            <div
                                v-if="selectedOrderTypeValue && !isFormValid"
                                class="rounded-md bg-yellow-50 p-2 text-xs text-yellow-800"
                            >
                                ⚠️ Please complete all required fields before
                                placing your order
                            </div>

                            <button
                                @click="placeOrder"
                                :disabled="!isFormValid || isOrderLoading"
                                class="group relative w-full overflow-hidden rounded-lg bg-gradient-to-r from-red-500 to-red-600 py-3 text-sm text-white transition-all hover:shadow-md hover:shadow-red-200 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span
                                    class="relative z-10 flex items-center justify-center gap-2 font-semibold"
                                >
                                    <span
                                        v-if="isOrderLoading"
                                        class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                                    ></span>
                                    <CreditCard v-else class="h-4 w-4" />
                                    {{
                                        isOrderLoading
                                            ? 'Placing Order...'
                                            : 'Confirm Order'
                                    }}
                                </span>
                                <div
                                    class="absolute inset-0 -translate-x-full transform bg-gradient-to-r from-red-600 to-red-700 transition-transform duration-300 group-hover:translate-x-0"
                                ></div>
                            </button>

                            <div class="text-center text-xs text-gray-500">
                                <p>By placing your order, you agree to our</p>
                                <p>Terms of Service and Privacy Policy</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <AddAddressModal
            :is-open="showAddressModal"
            :address-to-edit="editingAddress"
            @close="showAddressModal = false"
            @save="onAddressSaved"
        />
    </ClientLayout>
</template>

<style scoped></style>
