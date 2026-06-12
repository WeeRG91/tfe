<script setup lang="ts">
import { AddressType } from '@/types/address';
import axios from 'axios';
import { Home, MapPin, Phone, User, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import address from '@/routes/address';

const props = defineProps<{
    isOpen: boolean;
    addressToEdit?: any | null;
}>();

const emit = defineEmits<{
    close: [];
    save: [address: AddressType];
}>();

const first_name = ref<string>('');
const last_name = ref<string>('');
const phone = ref<string>('');
const street = ref<string>('');
const city = ref<string>('');
const postal_code = ref<string>('');
const country = ref<string>('Belgium');
const is_default = ref<boolean>(false);
const is_loading = ref<boolean>(false);
const form = ref({
    errors: {} as Record<string, string>,
});

const resetForm = () => {
    first_name.value = '';
    last_name.value = '';
    phone.value = '';
    street.value = '';
    city.value = '';
    postal_code.value = '';
    country.value = 'Belgium';
    is_default.value = false;
    form.value.errors = {};
};

const closeModal = () => {
    resetForm();
    emit('close');
};

const handleSubmit = async () => {
    is_loading.value = true;

    try {
        if (props.addressToEdit) {
            const { data } = await axios.post(address.update(props.addressToEdit.id).url, {
                first_name: first_name.value,
                last_name: last_name.value,
                phone: phone.value,
                street: street.value,
                city: city.value,
                postal_code: postal_code.value,
                country: country.value,
                is_default: is_default.value,
            });

            const editedAddress = data.address as AddressType;

            emit('save', editedAddress);
        } else {
            const { data } = await axios.post(address.store().url, {
                first_name: first_name.value,
                last_name: last_name.value,
                phone: phone.value,
                street: street.value,
                city: city.value,
                postal_code: postal_code.value,
                country: country.value,
                is_default: is_default.value,
            });

            const newCreatedAddress = data.address as AddressType;

            emit('save', newCreatedAddress);
        }
    } catch (error) {
        console.log(error);
        toast.error('Failed to add address');

        if (axios.isAxiosError(error) && error.response?.status === 422) {
            form.value.errors = error.response.data.errors || {};
        }
    } finally {
        is_loading.value = false;
        closeModal();
    }
};

const handleBackdropClick = (e: MouseEvent) => {
    if (e.target === e.currentTarget) {
        closeModal();
    }
};

const handleEscapeKey = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        closeModal();
    }
};

watch(
    () => props.addressToEdit,
    (newAddress) => {
        if (newAddress) {
            first_name.value = newAddress.first_name || '';
            last_name.value = newAddress.last_name || '';
            phone.value = newAddress.phone || '';
            street.value = newAddress.street || '';
            city.value = newAddress.city || '';
            postal_code.value = newAddress.postal_code || '';
            country.value = newAddress.country || 'Belgium';
            is_default.value = newAddress.is_default || false;
        } else {
            resetForm();
        }
    },
    { immediate: true },
);
</script>

<template>
    <Teleport to="body">
        <div
            v-if="props.isOpen"
            class="fixed inset-0 z-50 flex items-center justify-center"
            @click="handleBackdropClick"
            @keydown="handleEscapeKey"
        >
            <!-- Backdrop -->
            <div
                class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-all"
            ></div>

            <!-- Modal -->
            <div
                class="relative mx-4 w-full max-w-lg overflow-hidden rounded-xl bg-white shadow-2xl"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-200 bg-gradient-to-r from-red-500 to-red-600 px-6 py-4"
                >
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-white/20 p-2">
                            <Home class="h-5 w-5 text-white" />
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-white">
                                {{
                                    address ? 'Edit Address' : 'Add New Address'
                                }}
                            </h2>
                            <p class="mt-0.5 text-xs text-white/80">
                                {{
                                    address
                                        ? 'Update your delivery address details'
                                        : 'Enter your delivery address details'
                                }}
                            </p>
                        </div>
                    </div>
                    <button
                        @click="closeModal"
                        class="rounded-full p-1 text-white/80 transition hover:bg-white/20 hover:text-white"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleSubmit" class="space-y-4 p-6">
                    <!-- Two column layout for name fields -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                First Name *
                            </label>
                            <div class="relative">
                                <User
                                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                                />
                                <input
                                    v-model="first_name"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-gray-300 py-2.5 pr-3 pl-10 text-sm transition-all focus:border-red-500 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                                    :class="{
                                        'border-red-500':
                                            form.errors.first_name,
                                    }"
                                    placeholder="John"
                                />
                            </div>
                            <p
                                v-if="form.errors.first_name"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.first_name }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Last Name *
                            </label>
                            <div class="relative">
                                <User
                                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                                />
                                <input
                                    v-model="last_name"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-gray-300 py-2.5 pr-3 pl-10 text-sm transition-all focus:border-red-500 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                                    :class="{
                                        'border-red-500': form.errors.last_name,
                                    }"
                                    placeholder="Doe"
                                />
                            </div>
                            <p
                                v-if="form.errors.last_name"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.last_name }}
                            </p>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Phone Number *
                        </label>
                        <div class="relative">
                            <Phone
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                            />
                            <input
                                v-model="phone"
                                type="tel"
                                required
                                class="w-full rounded-lg border border-gray-300 py-2.5 pr-3 pl-10 text-sm transition-all focus:border-red-500 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                                :class="{ 'border-red-500': form.errors.phone }"
                                placeholder="+32 123 456 789"
                            />
                        </div>
                        <p
                            v-if="form.errors.phone"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ form.errors.phone }}
                        </p>
                    </div>

                    <!-- Street Address -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Street Address *
                        </label>
                        <div class="relative">
                            <MapPin
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                            />
                            <input
                                v-model="street"
                                type="text"
                                required
                                class="w-full rounded-lg border border-gray-300 py-2.5 pr-3 pl-10 text-sm transition-all focus:border-red-500 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                                :class="{
                                    'border-red-500': form.errors.street,
                                }"
                                placeholder="123 Main St, Apt 4B"
                            />
                        </div>
                        <p
                            v-if="form.errors.street"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ form.errors.street }}
                        </p>
                    </div>

                    <!-- City, Postal Code, Country -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                City *
                            </label>
                            <input
                                v-model="city"
                                type="text"
                                required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm transition-all focus:border-red-500 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                                :class="{ 'border-red-500': form.errors.city }"
                                placeholder="Brussels"
                            />
                            <p
                                v-if="form.errors.city"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.city }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Postal Code *
                            </label>
                            <input
                                v-model="postal_code"
                                type="text"
                                required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm transition-all focus:border-red-500 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                                :class="{
                                    'border-red-500': form.errors.postal_code,
                                }"
                                placeholder="1000"
                            />
                            <p
                                v-if="form.errors.postal_code"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.postal_code }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Country
                            </label>
                            <select
                                v-model="country"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm transition-all focus:border-red-500 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                            >
                                <option value="Belgium">Belgium</option>
                                <option value="Netherlands">Netherlands</option>
                                <option value="France">France</option>
                                <option value="Germany">Germany</option>
                                <option value="Luxembourg">Luxembourg</option>
                            </select>
                        </div>
                    </div>

                    <!-- Default Address Checkbox -->
                    <div
                        class="flex items-center gap-3 rounded-lg bg-blue-50 p-3"
                    >
                        <input
                            v-model="is_default"
                            type="checkbox"
                            id="is_default"
                            class="h-4 w-4 rounded border-gray-300 text-red-500 focus:ring-red-500"
                        />
                        <label
                            for="is_default"
                            class="cursor-pointer text-sm text-gray-700"
                        >
                            Set as default address
                        </label>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex gap-3 border-t border-gray-200 pt-4">
                        <button
                            type="button"
                            @click="closeModal"
                            class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="is_loading"
                            class="flex-1 rounded-lg bg-gradient-to-r from-red-500 to-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:from-red-600 hover:to-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span
                                v-if="is_loading"
                                class="flex items-center justify-center gap-2"
                            >
                                <span
                                    class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                                ></span>
                                {{addressToEdit ? 'Updating...' : 'Saving...' }}
                            </span>
                            <span v-else>
                                {{ addressToEdit ? 'Update' : 'Save' }}
                            </span>
                        </button>
                    </div>

                    <!-- Error Summary -->
                    <div
                        v-if="Object.keys(form.errors).length > 0"
                        class="rounded-lg bg-red-50 p-3"
                    >
                        <p class="text-xs text-red-600">
                            Please fix the errors above before submitting.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
/* Modal animations */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.modal-enter-active,
.modal-leave-active {
    transition:
        transform 0.3s ease,
        opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    transform: scale(0.95);
    opacity: 0;
}
</style>
