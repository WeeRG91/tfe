<script setup lang="ts">
import type { AddressType } from '@/types/address';
import {
    CompanyDeliveryOptionType,
    DeliveryOptionsType,
    DeliveryTypeEnum,
} from '@/types/delivery';
import {
    Building2,
    Check,
    Edit,
    Home,
    MapPin,
    Phone,
    Plus,
    Star,
    Trash,
    Truck,
    User,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    restaurantOpen: boolean;
    deliveryType: DeliveryTypeEnum | null;
    selectedAddressId: number | null;
    selectedCompanyId: number | null;
    deliveryDate: string;
    addresses: AddressType[];
    deliveryOptions: DeliveryOptionsType;
    deletingAddressId: number | null;
}>();

const emit = defineEmits<{
    'update:deliveryType': [value: DeliveryTypeEnum];
    'update:selectedAddressId': [value: number | null];
    'update:selectedCompanyId': [value: number | null];
    'update:deliveryDate': [value: string];
    addAddress: [];
    editAddress: [address: AddressType];
    deleteAddress: [id: number];
}>();

const selectAddress = (id: number) => {
    emit('update:selectedAddressId', id);
};

const { t } = useI18n();

const eligibleAddresses = computed(() =>
    props.addresses.filter((address) =>
        props.deliveryOptions.own_address.postal_codes.includes(
            address.postal_code,
        ),
    ),
);

const selectedCompany = computed<CompanyDeliveryOptionType | undefined>(() =>
    props.deliveryOptions.company.companies.find(
        (company) => company.id === props.selectedCompanyId,
    ),
);

const availableCompanies = computed(() =>
    props.deliveryOptions.company.companies.filter(
        (company) => company.dates.length > 0,
    ),
);

const companyDeliveryAvailable = computed(
    () =>
        props.deliveryOptions.company.enabled &&
        availableCompanies.value.length > 0,
);

const selectOwnAddressDelivery = () => {
    if (!props.restaurantOpen) {
        return;
    }

    emit('update:deliveryType', DeliveryTypeEnum.OWN_ADDRESS);
    emit('update:selectedCompanyId', null);
    emit('update:deliveryDate', '');

    if (
        !eligibleAddresses.value.some(
            (address) => address.id === props.selectedAddressId,
        )
    ) {
        const defaultAddress = eligibleAddresses.value.find(
            (address) => address.is_default,
        );

        emit(
            'update:selectedAddressId',
            defaultAddress?.id ?? eligibleAddresses.value[0]?.id ?? null,
        );
    }
};

const selectCompanyDelivery = () => {
    if (!companyDeliveryAvailable.value) {
        return;
    }

    emit('update:deliveryType', DeliveryTypeEnum.COMPANY);
    emit('update:selectedAddressId', null);
    emit('update:selectedCompanyId', null);
    emit('update:deliveryDate', '');
};

const selectCompany = (companyId: number) => {
    emit('update:selectedCompanyId', companyId);
    emit('update:deliveryDate', '');
};

const onCompanyChange = (event: Event) => {
    const value = (event.target as HTMLSelectElement).value;

    if (!value) {
        emit('update:selectedCompanyId', null);
        emit('update:deliveryDate', '');
        return;
    }

    selectCompany(Number(value));
};

const onDeliveryDateChange = (event: Event) => {
    const value = (event.target as HTMLSelectElement).value;
    emit('update:deliveryDate', value);
};
</script>

<template>
    <div class="rounded-lg border bg-white p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="flex items-center gap-2 text-lg font-semibold uppercase">
                <MapPin class="h-5 w-5" />
                {{ t('cart.orderType.deliveryForm.title') }}
            </h2>
            <button
                v-if="
                    props.deliveryType === DeliveryTypeEnum.OWN_ADDRESS &&
                    props.addresses.length > 0
                "
                @click="emit('addAddress')"
                class="inline-flex items-center gap-2 rounded-full bg-red-500 p-1 text-sm text-white transition hover:bg-red-600"
                title="Add new address"
            >
                <Plus class="h-4 w-4" />
            </button>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2">
            <button
                type="button"
                :disabled="!props.restaurantOpen"
                :aria-pressed="
                    props.deliveryType === DeliveryTypeEnum.OWN_ADDRESS
                "
                :class="[
                    'group relative flex min-h-52 flex-col overflow-hidden rounded-2xl border-2 p-5 text-left transition-all duration-200',
                    props.deliveryType === DeliveryTypeEnum.OWN_ADDRESS
                        ? 'border-red-500 bg-red-50/70 shadow-md'
                        : 'border-gray-200 bg-white hover:-translate-y-0.5 hover:border-red-300 hover:shadow-md',
                    !props.restaurantOpen &&
                        'cursor-not-allowed opacity-50 hover:translate-y-0 hover:border-gray-200 hover:shadow-none',
                ]"
                @click="selectOwnAddressDelivery"
            >
                <div class="flex w-full items-start justify-between">
                    <div
                        :class="[
                            'flex h-11 w-11 items-center justify-center rounded-xl transition-colors',
                            props.deliveryType === DeliveryTypeEnum.OWN_ADDRESS
                                ? 'bg-red-500 text-white'
                                : 'bg-red-50 text-red-500 group-hover:bg-red-100',
                        ]"
                    >
                        <Truck class="h-5 w-5" />
                    </div>

                    <div
                        :class="[
                            'flex h-6 w-6 items-center justify-center rounded-full border-2 transition-colors',
                            props.deliveryType === DeliveryTypeEnum.OWN_ADDRESS
                                ? 'border-red-500 bg-red-500'
                                : 'border-gray-300 bg-white',
                        ]"
                    >
                        <Check
                            v-if="
                                props.deliveryType ===
                                DeliveryTypeEnum.OWN_ADDRESS
                            "
                            class="h-3.5 w-3.5 text-white"
                        />
                    </div>
                </div>

                <div class="mt-5 flex-1">
                    <h3 class="font-semibold text-gray-900">
                        {{ t('cart.orderType.deliveryForm.ownAddress') }}
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-gray-600">
                        {{
                            t(
                                'cart.orderType.deliveryForm.ownAddressDescription',
                                {
                                    postalCodes:
                                        props.deliveryOptions.own_address.postal_codes.join(
                                            ', ',
                                        ),
                                },
                            )
                        }}
                    </p>
                </div>

                <div
                    class="mt-5 flex w-full items-center justify-between border-t border-gray-200 pt-4"
                >
                    <span
                        class="text-xs font-medium tracking-wide text-gray-500 uppercase"
                    >
                        {{ t('cart.orderType.deliveryForm.deliveryFee') }}
                    </span>

                    <span
                        :class="[
                            'font-semibold',
                            props.restaurantOpen
                                ? 'text-red-600'
                                : 'text-gray-500',
                        ]"
                    >
                        {{
                            props.restaurantOpen
                                ? t(
                                      'cart.orderType.deliveryForm.ownAddressFee',
                                      {
                                          fee: props.deliveryOptions.own_address
                                              .fee,
                                      },
                                  )
                                : t('cart.orderType.deliveryForm.unavailable')
                        }}
                    </span>
                </div>
            </button>

            <button
                v-if="props.deliveryOptions.company.enabled"
                type="button"
                :disabled="!companyDeliveryAvailable"
                :aria-pressed="props.deliveryType === DeliveryTypeEnum.COMPANY"
                :class="[
                    'group relative flex min-h-52 flex-col overflow-hidden rounded-2xl border-2 p-5 text-left transition-all duration-200',
                    props.deliveryType === DeliveryTypeEnum.COMPANY
                        ? 'border-red-500 bg-red-50/70 shadow-md'
                        : 'border-gray-200 bg-white hover:-translate-y-0.5 hover:border-red-300 hover:shadow-md',
                    !companyDeliveryAvailable &&
                        'cursor-not-allowed opacity-50 hover:translate-y-0 hover:border-gray-200 hover:shadow-none',
                ]"
                @click="selectCompanyDelivery"
            >
                <div class="flex w-full items-start justify-between">
                    <div
                        :class="[
                            'flex h-11 w-11 items-center justify-center rounded-xl transition-colors',
                            props.deliveryType === DeliveryTypeEnum.COMPANY
                                ? 'bg-red-500 text-white'
                                : 'bg-red-50 text-red-500 group-hover:bg-red-100',
                        ]"
                    >
                        <Building2 class="h-5 w-5" />
                    </div>

                    <div
                        :class="[
                            'flex h-6 w-6 items-center justify-center rounded-full border-2 transition-colors',
                            props.deliveryType === DeliveryTypeEnum.COMPANY
                                ? 'border-red-500 bg-red-500'
                                : 'border-gray-300 bg-white',
                        ]"
                    >
                        <Check
                            v-if="
                                props.deliveryType === DeliveryTypeEnum.COMPANY
                            "
                            class="h-3.5 w-3.5 text-white"
                        />
                    </div>
                </div>

                <div class="mt-5 flex-1">
                    <h3 class="font-semibold text-gray-900">
                        {{ t('cart.orderType.deliveryForm.company') }}
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-gray-600">
                        {{
                            t('cart.orderType.deliveryForm.companyDescription')
                        }}
                    </p>
                </div>

                <div
                    class="mt-5 flex w-full items-center justify-between border-t border-gray-200 pt-4"
                >
                    <span
                        class="text-xs font-medium tracking-wide text-gray-500 uppercase"
                    >
                        {{ t('cart.orderType.deliveryForm.deliveryFee') }}
                    </span>

                    <span
                        :class="[
                            'font-semibold',
                            companyDeliveryAvailable
                                ? 'text-green-600'
                                : 'text-gray-500',
                        ]"
                    >
                        {{
                            companyDeliveryAvailable
                                ? t('cart.orderType.deliveryForm.free')
                                : t('cart.orderType.deliveryForm.unavailable')
                        }}
                    </span>
                </div>
            </button>
        </div>

        <template v-if="props.deliveryType === DeliveryTypeEnum.OWN_ADDRESS">
            <div v-if="eligibleAddresses.length > 0" class="space-y-4">
                <div
                    v-for="addr in eligibleAddresses"
                    :key="addr.id"
                    :class="[
                        'overflow-hidden rounded-xl transition-all duration-200',
                        props.selectedAddressId === addr.id
                            ? 'shadow-lg ring-2 ring-red-500'
                            : 'border border-gray-200 hover:shadow-md',
                    ]"
                >
                    <div
                        :class="[
                            'p-5',
                            props.selectedAddressId === addr.id
                                ? 'bg-gradient-to-r from-red-50 to-red-50/30'
                                : 'bg-white hover:bg-gray-50/50',
                        ]"
                    >
                        <div class="flex items-start justify-between">
                            <div
                                class="flex-1 cursor-pointer"
                                @click="selectAddress(addr.id)"
                            >
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="flex items-center gap-2">
                                        <div
                                            :class="[
                                                'rounded-full p-1',
                                                props.selectedAddressId ===
                                                addr.id
                                                    ? 'bg-red-100'
                                                    : 'bg-gray-100',
                                            ]"
                                        >
                                            <User
                                                :class="[
                                                    'h-4 w-4',
                                                    props.selectedAddressId ===
                                                    addr.id
                                                        ? 'text-red-600'
                                                        : 'text-gray-500',
                                                ]"
                                            />
                                        </div>
                                        <span
                                            class="font-semibold text-gray-800"
                                            >{{ addr.first_name }}
                                            {{ addr.last_name }}</span
                                        >
                                    </div>
                                    <span
                                        v-if="addr.is_default"
                                        class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700"
                                    >
                                        <Star class="h-3 w-3" />
                                        {{
                                            t(
                                                'cart.orderType.deliveryForm.default',
                                            )
                                        }}
                                    </span>
                                </div>

                                <div
                                    class="mt-3 space-y-1.5 text-sm text-gray-600"
                                >
                                    <div class="flex items-center gap-2">
                                        <Phone
                                            class="h-3.5 w-3.5 text-gray-400"
                                        />
                                        <span>{{ addr.phone }}</span>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <MapPin
                                            class="mt-0.5 h-3.5 w-3.5 text-gray-400"
                                        />
                                        <div class="flex flex-col">
                                            <span>{{ addr.street }}</span>
                                            <span
                                                >{{ addr.city }},
                                                {{ addr.postal_code }}</span
                                            >
                                            <span>{{ addr.country }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="ml-4 flex items-center gap-1">
                                <button
                                    @click.stop="emit('editAddress', addr)"
                                    class="rounded-lg p-2 text-gray-400 transition-all hover:bg-gray-100 hover:text-blue-600"
                                    title="Edit address"
                                >
                                    <Edit class="h-4 w-4" />
                                </button>

                                <span
                                    v-if="props.deletingAddressId === addr.id"
                                    class="m-2 h-4 w-4 animate-spin rounded-full border-2 border-red-500 border-t-transparent"
                                ></span>

                                <button
                                    v-else
                                    @click.stop="emit('deleteAddress', addr.id)"
                                    class="rounded-lg p-2 text-gray-400 transition-all hover:bg-red-50 hover:text-red-600"
                                    title="Delete address"
                                >
                                    <Trash class="h-4 w-4" />
                                </button>

                                <div
                                    v-if="props.selectedAddressId === addr.id"
                                    class="ml-1 rounded-full bg-red-500 p-1"
                                >
                                    <Check class="h-4 w-4 text-white" />
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
                <MapPin class="mx-auto h-12 w-12 text-gray-400" />
                <p class="mt-2 text-sm text-gray-600">
                    {{ t('cart.orderType.deliveryForm.noAddresses') }}
                </p>
                <button
                    @click="emit('addAddress')"
                    class="mt-3 inline-flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-sm text-white transition hover:bg-red-600"
                >
                    <Home class="h-4 w-4" />
                    {{ t('cart.orderType.deliveryForm.addNewAddress') }}
                </button>
            </div>
        </template>

        <div
            v-if="props.deliveryType === DeliveryTypeEnum.COMPANY"
            class="space-y-5 rounded-xl border border-gray-200 bg-gray-50 p-5"
        >
            <div>
                <label
                    for="delivery-company"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    {{ t('cart.orderType.deliveryForm.selectCompany') }}
                </label>

                <select
                    id="delivery-company"
                    :value="props.selectedCompanyId ?? ''"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 focus:border-red-500 focus:ring-red-500"
                    @change="onCompanyChange"
                >
                    <option value="">
                        {{
                            t(
                                'cart.orderType.deliveryForm.selectCompanyPlaceholder',
                            )
                        }}
                    </option>

                    <option
                        v-for="company in availableCompanies"
                        :key="company.id"
                        :value="company.id"
                    >
                        {{ company.name }}
                    </option>
                </select>
            </div>

            <div v-if="selectedCompany">
                <label
                    for="company-delivery-date"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    {{ t('cart.orderType.deliveryForm.selectDate') }}
                </label>

                <select
                    id="company-delivery-date"
                    :value="props.deliveryDate"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 focus:border-red-500 focus:ring-red-500"
                    @change="onDeliveryDateChange"
                >
                    <option value="">
                        {{
                            t(
                                'cart.orderType.deliveryForm.selectDatePlaceholder',
                            )
                        }}
                    </option>

                    <option
                        v-for="date in selectedCompany.dates"
                        :key="date"
                        :value="date"
                    >
                        {{
                            new Intl.DateTimeFormat(undefined, {
                                dateStyle: 'full',
                                timeZone: 'UTC',
                            }).format(new Date(`${date}T00:00:00Z`))
                        }}
                    </option>
                </select>

                <p class="mt-2 text-sm text-gray-500">
                    {{
                        t('cart.orderType.deliveryForm.minimumAdvance', {
                            days: selectedCompany.minimum_advance_days,
                        })
                    }}
                </p>
            </div>

            <p
                v-if="availableCompanies.length === 0"
                class="text-sm text-gray-600"
            >
                {{ t('cart.orderType.deliveryForm.noCompanyDates') }}
            </p>
        </div>
    </div>
</template>

<style scoped></style>
