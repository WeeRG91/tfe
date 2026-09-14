<script setup lang="ts">
import type { AddressType } from '@/types/address';
import {
    Check,
    Edit,
    Home,
    MapPin,
    Phone,
    Plus,
    Star,
    Trash,
    User,
} from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    selectedAddressId: number | null;
    addresses: AddressType[];
    deletingAddressId: number | null;
}>();

const emit = defineEmits<{
    'update:selectedAddressId': [value: number | null];
    addAddress: [];
    editAddress: [address: AddressType];
    deleteAddress: [id: number];
}>();

const selectAddress = (id: number) => {
    emit('update:selectedAddressId', id);
};

const { t } = useI18n();
</script>

<template>
    <div class="rounded-lg border bg-white p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="flex items-center gap-2 text-lg font-semibold uppercase">
                <MapPin class="h-5 w-5" />
                {{ t('cart.orderType.deliveryForm.title') }}
            </h2>
            <button
                v-if="props.addresses.length > 0"
                @click="emit('addAddress')"
                class="inline-flex items-center gap-2 rounded-full bg-red-500 p-1 text-sm text-white transition hover:bg-red-600"
                title="Add new address"
            >
                <Plus class="h-4 w-4" />
            </button>
        </div>

        <div v-if="props.addresses.length > 0" class="space-y-4">
            <div
                v-for="addr in addresses"
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
                                            props.selectedAddressId === addr.id
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
                                    <span class="font-semibold text-gray-800"
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
                                        t('cart.orderType.deliveryForm.default')
                                    }}
                                </span>
                            </div>

                            <div class="mt-3 space-y-1.5 text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <Phone class="h-3.5 w-3.5 text-gray-400" />
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
    </div>
</template>

<style scoped></style>
