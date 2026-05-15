<script setup lang="ts">
import AddAddressModal from '@/components/client/address/AddAddressModal.vue';
import DineInForm from '@/components/client/placeOrder/DineInForm.vue';
import OrderTypeSelector from '@/components/client/placeOrder/OrderTypeSelector.vue';
import TakeawayForm from '@/components/client/placeOrder/TakeawayForm.vue';
import { useLoyaltyPoints } from '@/composables/useLoyaltyPoints';
import { useVatCalculator } from '@/composables/useVatCalculator';
import ClientLayout from '@/layouts/ClientLayout.vue';
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
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';
import DeliveryForm from '@/components/client/placeOrder/DeliveryForm.vue';
import PaymentMethodSelector from '@/components/client/placeOrder/PaymentMethodSelector.vue';
import OrderNotes from '@/components/client/placeOrder/OrderNotes.vue';
import LoyaltyPointsSection from '@/components/client/placeOrder/LoyaltyPointsSection.vue';
import OrderSummary from '@/components/client/placeOrder/OrderSummary.vue';
import OrderSidebar from '@/components/client/placeOrder/OrderSidebar.vue';

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

const loyaltyPoints = computed(() => cart.value?.user?.loyalty_points ?? 0);

const selectedOrderType = computed(() => {
    return props.orderTypes.find(
        (type) => type.value === selectedOrderTypeValue.value,
    );
});

const {
    pointsOptions,
    selectedPoints,
    discountAmount,
    applyPoints,
    removePoints,
    canUseOption,
} = useLoyaltyPoints(loyaltyPoints);

const { vat12Total, vat21Total, totalVat, totalIncVat, subtotal } =
    useVatCalculator({
        items,
        discountAmount,
        deliveryFee,
        selectedOrderTypeValue,
    });

const earnedPoints = computed(() => {
    return Math.floor(totalIncVat.value * 3);
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

const isFormValid = computed(() => {
    if (!selectedOrderType.value) return false;
    if (!paymentMethod.value) return false;

    if (selectedOrderType.value.value === 1) {
        return tableNumber.value.trim().length > 0;
    }

    if (selectedOrderType.value.value === 2) {
        return (
            !!pickupTime.value &&
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

const selectDefaultAddress = () => {
    const defaultAddress = existingAddresses.value.find(
        (a) => a.is_default,
    );
    selectedAddressId.value = defaultAddress ? defaultAddress.id : null;
}

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

        selectDefaultAddress();
    } catch (error) {
        console.log(error);
        toast.error('Failed to place order');
    } finally {
        deletedAddressId.value = null;
    }
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
            used_points: selectedPoints.value,
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
        selectDefaultAddress();
    }

    removePoints();
};

onMounted(() => {
    selectDefaultAddress();
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
                <h1 class="text-4xl font-semibold uppercase md:text-5xl">
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
                        <OrderTypeSelector
                            v-model="selectedOrderTypeValue"
                            :order-types="props.orderTypes"
                            @update:model-value="onOrderTypeChange"
                        />

                        <!-- Dine In Details -->
                        <DineInForm
                            v-if="selectedOrderTypeValue === 1"
                            v-model="tableNumber"
                        />

                        <!-- Takeaway Details -->
                        <TakeawayForm
                            v-if="selectedOrderTypeValue === 2"
                            v-model:pickup-name="pickupName"
                            v-model:pickup-phone="pickupPhone"
                            v-model:pickup-time="pickupTime"
                            :min-pickup-time="minPickupTime"
                            :max-pickup-time="maxPickupTime"
                        />

                        <!-- Delivery Details -->
                        <DeliveryForm
                            v-if="selectedOrderType?.value === 3"
                            v-model:selected-address-id="selectedAddressId"
                            :addresses="existingAddresses"
                            :deleting-address-id="deletedAddressId"
                            @add-address="addNewAddress"
                            @edit-address="editAddress"
                            @delete-address="deleteAddress"
                        />

                        <!-- Payment Method Selection -->
                        <PaymentMethodSelector
                            v-if="selectedOrderTypeValue"
                            v-model="paymentMethod"
                            :payment-methods="paymentMethods"
                        />

                        <!-- Order Notes -->
                        <OrderNotes
                            v-model="notes"
                            :max-length="500"
                            placeholder="Any special requests or dietary requirements?"
                        />

                        <!-- Loyalty Points Section -->
                        <LoyaltyPointsSection
                            :loyalty-points="loyaltyPoints"
                            :earned-points="earnedPoints"
                            :points-options="pointsOptions"
                            :selected-points="selectedPoints"
                            :discount-amount="discountAmount"
                            :can-use-option="canUseOption"
                            @apply="applyPoints"
                            @remove="removePoints"
                        />

                        <!-- Order Items Summary -->
                        <OrderSummary
                            :items="items"
                            :subtotal="subtotal"
                            :delivery-fee="
                                selectedOrderType?.value === 3 ? 2 : 0
                            "
                            :discount-amount="discountAmount"
                            :vat12-total="vat12Total"
                            :vat21-total="vat21Total"
                            :total-vat="totalVat"
                            :cart-total="totalIncVat"
                            :earned-points="Math.floor(totalIncVat * 3)"
                        />
                    </div>
                </div>

                <!-- Sidebar -->
                <OrderSidebar
                    :items-count="items.length"
                    :cart-total="totalIncVat"
                    :is-form-valid="isFormValid"
                    :is-loading="isOrderLoading"
                    @place-order="placeOrder"
                />
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
