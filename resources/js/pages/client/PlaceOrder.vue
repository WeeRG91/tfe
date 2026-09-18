<script setup lang="ts">
import AddAddressModal from '@/components/client/address/AddAddressModal.vue';
import DeliveryForm from '@/components/client/placeOrder/DeliveryForm.vue';
import DineInForm from '@/components/client/placeOrder/DineInForm.vue';
import LoyaltyPointsSection from '@/components/client/placeOrder/LoyaltyPointsSection.vue';
import OrderNotes from '@/components/client/placeOrder/OrderNotes.vue';
import OrderSidebar from '@/components/client/placeOrder/OrderSidebar.vue';
import OrderSummary from '@/components/client/placeOrder/OrderSummary.vue';
import OrderTypeSelector from '@/components/client/placeOrder/OrderTypeSelector.vue';
import PaymentMethodSelector from '@/components/client/placeOrder/PaymentMethodSelector.vue';
import TakeawayForm from '@/components/client/placeOrder/TakeawayForm.vue';
import { useLoyaltyPoints } from '@/composables/useLoyaltyPoints';
import { usePickupAvailability } from '@/composables/usePickupAvailability';
import { usePickupErrorHandling } from '@/composables/usePickupErrorHandling';
import { useRestaurantOrdering } from '@/composables/useRestaurantOrdering';
import { useVatCalculator } from '@/composables/useVatCalculator';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { getTotalPoints } from '@/lib/utils';
import address from '@/routes/address';
import order from '@/routes/order';
import paymentOrder from '@/routes/payment-order';
import { useCartStore } from '@/stores/cart';
import { useOrderStore } from '@/stores/order';
import { AddressType } from '@/types/address';
import { DeliveryOptionsType, DeliveryTypeEnum } from '@/types/delivery';
import { OrderTypeEnum, OrderTypeType } from '@/types/order';
import { PaymentMethodType } from '@/types/payment';
import { LoyaltyPointTransactionType } from '@/types/point';
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    orderTypes: OrderTypeType[];
    paymentMethods: PaymentMethodType[];
    addresses: AddressType[];
    loyaltyPointTransactions: LoyaltyPointTransactionType[];
    deliveryOptions: DeliveryOptionsType;
}>();

const { t } = useI18n();
const {
    availability: pickupAvailability,
    currentStatus: restaurantStatus,
    isLoading: isPickupLoading,
    hasError: hasPickupError,
    refresh: refreshPickupAvailability,
    isAvailableSlot,
} = usePickupAvailability();
const page = usePage();

const cartStore = useCartStore();
const orderStore = useOrderStore();
const { cart, items } = storeToRefs(cartStore);
const { isLoading: isOrderLoading } = storeToRefs(orderStore);

const selectedOrderTypeValue = ref<OrderTypeEnum | null>(null);
const selectedAddressId = ref<number | null>(null);
const paymentMethod = ref<number | null>(null);
const pickupTime = ref<string>('');
const pickupName = ref<string>(page.props.auth.user?.name ?? '');
const pickupPhone = ref<string>('');
const tableNumber = ref<string>('');
const notes = ref<string>('');
const showAddressModal = ref(false);
const editingAddress = ref<AddressType | null>(null);
const existingAddresses = ref<AddressType[]>(props.addresses ?? []);
const deletedAddressId = ref<number | null>(null);
const selectedDeliveryType = ref<DeliveryTypeEnum | null>(null);
const selectedDeliveryCompanyId = ref<number | null>(null);
const selectedDeliveryDate = ref<string>('');

const deliveryFee = computed(() => {
    if (
        selectedOrderTypeValue.value !== OrderTypeEnum.DELIVERY ||
        selectedDeliveryType.value !== DeliveryTypeEnum.OWN_ADDRESS
    ) {
        return 0;
    }

    return Number(props.deliveryOptions.own_address.fee);
});

const loyaltyPoints = computed(
    () => getTotalPoints(props.loyaltyPointTransactions) ?? 0,
);

const selectedOrderType = computed(() => {
    return props.orderTypes.find(
        (type) => type.value === selectedOrderTypeValue.value,
    );
});

const eligibleAddresses = computed(() =>
    existingAddresses.value.filter((address) =>
        props.deliveryOptions.own_address.postal_codes.includes(
            address.postal_code,
        ),
    ),
);

const selectedCompany = computed(() =>
    props.deliveryOptions.company.companies.find(
        (company) => company.id === selectedDeliveryCompanyId.value,
    ),
);

const hasAvailableCompanyDelivery = computed(
    () =>
        props.deliveryOptions.company.enabled &&
        props.deliveryOptions.company.companies.some(
            (company) => company.dates.length > 0,
        ),
);

const disabledOrderTypes = computed<OrderTypeEnum[]>(() => {
    if (isPickupLoading.value || !restaurantStatus.value) {
        return [OrderTypeEnum.DINEIN, OrderTypeEnum.DELIVERY];
    }

    if (restaurantStatus.value.is_open) {
        return [];
    }

    const disabled = [OrderTypeEnum.DINEIN];

    if (!hasAvailableCompanyDelivery.value) {
        disabled.push(OrderTypeEnum.DELIVERY);
    }

    return disabled;
});

const {
    pointsOptions,
    selectedPoints,
    discountAmount,
    applyPoints,
    removePoints,
    canUseOption,
} = useLoyaltyPoints(loyaltyPoints);

const {
    vat12Total,
    vat21Total,
    totalVat,
    totalIncVat,
    subtotalBeforeDeliveryFee,
} = useVatCalculator({
    items,
    discountAmount,
    deliveryFee,
    selectedOrderTypeValue,
});

const { handleClosureError } = useRestaurantOrdering();
const { handlePickupError } = usePickupErrorHandling(
    pickupTime,
    refreshPickupAvailability,
);

const earnedPoints = computed(() => Math.floor(totalIncVat.value * 3));

const isFormValid = computed(() => {
    if (!selectedOrderType.value) return false;
    if (!paymentMethod.value) return false;

    if (selectedOrderType.value.value === OrderTypeEnum.DINEIN) {
        return tableNumber.value.trim().length > 0;
    }

    if (selectedOrderType.value.value === OrderTypeEnum.TAKEAWAY) {
        return (
            !!pickupTime.value &&
            isAvailableSlot(pickupTime.value) &&
            pickupName.value.trim().length > 0 &&
            pickupPhone.value.trim().length > 0
        );
    }

    if (selectedOrderType.value.value === OrderTypeEnum.DELIVERY) {
        if (selectedDeliveryType.value === DeliveryTypeEnum.OWN_ADDRESS) {
            return (
                restaurantStatus.value?.is_open === true &&
                eligibleAddresses.value.some(
                    (address) => address.id === selectedAddressId.value,
                )
            );
        }

        if (selectedDeliveryType.value === DeliveryTypeEnum.COMPANY) {
            return (
                props.deliveryOptions.company.enabled &&
                selectedCompany.value !== undefined &&
                selectedCompany.value.dates.includes(selectedDeliveryDate.value)
            );
        }
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
    const defaultAddress = eligibleAddresses.value.find(
        (address) => address.is_default,
    );

    selectedAddressId.value =
        defaultAddress?.id ?? eligibleAddresses.value[0]?.id ?? null;
};

const deleteAddress = async (addressId: number) => {
    deletedAddressId.value = addressId;

    try {
        const { data } = await axios.delete(address.destroy(addressId).url);

        existingAddresses.value = existingAddresses.value.filter(
            (a) => a.id !== addressId,
        );

        if (data.newDefaultAddressId) {
            existingAddresses.value = existingAddresses.value.map((a) => ({
                ...a,
                is_default: a.id === data.newDefaultAddressId,
            }));
        }

        selectDefaultAddress();
    } catch (error) {
        console.log(error);
        toast.error(t('cart.placeOrderPage.errors.deleteAddress'));
    } finally {
        deletedAddressId.value = null;
    }
};

const placeOrder = async () => {
    if (!isFormValid.value || !selectedOrderType.value) {
        toast.error(t('cart.placeOrderPage.errors.requiredFields'));
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
            address_id:
                selectedDeliveryType.value === DeliveryTypeEnum.OWN_ADDRESS
                    ? selectedAddressId.value
                    : null,
            delivery_type:
                selectedOrderTypeValue.value === OrderTypeEnum.DELIVERY
                    ? selectedDeliveryType.value
                    : null,
            delivery_company_id:
                selectedDeliveryType.value === DeliveryTypeEnum.COMPANY
                    ? selectedDeliveryCompanyId.value
                    : null,
            delivery_date:
                selectedDeliveryType.value === DeliveryTypeEnum.COMPANY
                    ? selectedDeliveryDate.value
                    : null,
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
        if (handleClosureError(error)) {
            return;
        }

        if (await handlePickupError(error)) {
            return;
        }

        toast.error(t('cart.placeOrderPage.errors.placeOrder'));
    }
};

const onOrderTypeChange = async (type: OrderTypeEnum) => {
    selectedOrderTypeValue.value = type;
    paymentMethod.value = null;
    selectedDeliveryType.value = null;
    selectedDeliveryCompanyId.value = null;
    selectedDeliveryDate.value = '';

    if (type === OrderTypeEnum.DINEIN) {
        pickupName.value = '';
        pickupPhone.value = '';
        pickupTime.value = '';
        selectedAddressId.value = null;
    } else if (type === OrderTypeEnum.TAKEAWAY) {
        pickupTime.value = '';
        tableNumber.value = '';
        selectedAddressId.value = null;
    } else if (type === OrderTypeEnum.DELIVERY) {
        tableNumber.value = '';
        pickupName.value = '';
        pickupPhone.value = '';
        pickupTime.value = '';
    }

    removePoints();
};

onMounted(() => {
    selectDefaultAddress();
});
</script>

<template>
    <Head :title="t('cart.placeOrderPage.pageTitle')" />

    <ClientLayout>
        <section class="mx-auto max-w-6xl px-6 py-4">
            <div class="mb-4">
                <p class="text-sm tracking-widest text-red-500 uppercase">
                    [ {{ t('cart.placeOrderPage.eyebrow') }} ]
                </p>
                <h1 class="text-4xl font-semibold uppercase md:text-5xl">
                    {{ t('cart.placeOrderPage.title') }}
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    {{ t('cart.placeOrderPage.description') }}
                </p>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row">
                <div class="flex-1">
                    <div class="space-y-6">
                        <OrderTypeSelector
                            v-model="selectedOrderTypeValue"
                            :order-types="props.orderTypes"
                            :disabled-order-types="disabledOrderTypes"
                            @update:model-value="onOrderTypeChange"
                        />

                        <DineInForm
                            v-if="
                                selectedOrderTypeValue === OrderTypeEnum.DINEIN
                            "
                            v-model="tableNumber"
                        />

                        <TakeawayForm
                            v-if="
                                selectedOrderTypeValue ===
                                OrderTypeEnum.TAKEAWAY
                            "
                            v-model:pickup-name="pickupName"
                            v-model:pickup-phone="pickupPhone"
                            v-model:pickup-time="pickupTime"
                            :availability="pickupAvailability"
                            :availability-loading="isPickupLoading"
                            :availability-error="hasPickupError"
                            @retry="refreshPickupAvailability"
                        />

                        <DeliveryForm
                            v-if="
                                selectedOrderTypeValue ===
                                OrderTypeEnum.DELIVERY
                            "
                            v-model:delivery-type="selectedDeliveryType"
                            v-model:selected-address-id="selectedAddressId"
                            v-model:selected-company-id="
                                selectedDeliveryCompanyId
                            "
                            v-model:delivery-date="selectedDeliveryDate"
                            :addresses="existingAddresses"
                            :delivery-options="props.deliveryOptions"
                            :deleting-address-id="deletedAddressId"
                            :restaurant-open="
                                restaurantStatus?.is_open ?? false
                            "
                            @add-address="addNewAddress"
                            @edit-address="editAddress"
                            @delete-address="deleteAddress"
                        />

                        <PaymentMethodSelector
                            v-if="selectedOrderTypeValue"
                            v-model="paymentMethod"
                            :payment-methods="paymentMethods"
                        />

                        <OrderNotes
                            v-model="notes"
                            :max-length="500"
                            :placeholder="
                                t('cart.placeOrderPage.notesPlaceholder')
                            "
                        />

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

                        <OrderSummary
                            :items="items"
                            :subtotal="subtotalBeforeDeliveryFee"
                            :delivery-fee="deliveryFee"
                            :discount-amount="discountAmount"
                            :vat12-total="vat12Total"
                            :vat21-total="vat21Total"
                            :total-vat="totalVat"
                            :total-inc-vat="totalIncVat"
                            :earned-points="Math.floor(totalIncVat * 3)"
                        />
                    </div>
                </div>

                <OrderSidebar
                    :items-count="items.length"
                    :total-inc-vat="totalIncVat"
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
