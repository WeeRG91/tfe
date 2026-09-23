<script setup lang="ts">
import address from '@/routes/address';
import { AddressType } from '@/types/address';
import axios from 'axios';
import { Home, MapPin, Phone, User, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    isOpen: boolean;
    addressToEdit?: any | null;
}>();

const emit = defineEmits<{
    close: [];
    save: [address: AddressType];
}>();

const { t } = useI18n();

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
            const { data } = await axios.post(
                address.update(props.addressToEdit.id).url,
                {
                    first_name: first_name.value,
                    last_name: last_name.value,
                    phone: phone.value,
                    street: street.value,
                    city: city.value,
                    postal_code: postal_code.value,
                    country: country.value,
                    is_default: is_default.value,
                },
            );

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
        toast.error(t('cart.orderType.deliveryForm.addressModal.addFailed'));

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
            <div
                class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-all"
            ></div>

            <div
                class="relative mx-4 w-full max-w-lg overflow-hidden rounded-xl border border-border bg-card text-card-foreground shadow-2xl"
            >
                <div
                    class="flex items-center justify-between border-b border-primary/20 bg-primary px-6 py-4 text-primary-foreground"
                >
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-primary-foreground/15 p-2">
                            <Home class="h-5 w-5 text-primary-foreground" />
                        </div>
                        <div>
                            <h2
                                class="text-xl font-semibold text-primary-foreground"
                            >
                                {{
                                    addressToEdit
                                        ? t(
                                              'cart.orderType.deliveryForm.addressModal.editTitle',
                                          )
                                        : t(
                                              'cart.orderType.deliveryForm.addressModal.addTitle',
                                          )
                                }}
                            </h2>
                            <p
                                class="mt-0.5 text-xs text-primary-foreground/80"
                            >
                                {{
                                    addressToEdit
                                        ? t(
                                              'cart.orderType.deliveryForm.addressModal.editDescription',
                                          )
                                        : t(
                                              'cart.orderType.deliveryForm.addressModal.addDescription',
                                          )
                                }}
                            </p>
                        </div>
                    </div>
                    <button
                        @click="closeModal"
                        class="rounded-full p-1 text-primary-foreground/80 transition-colors hover:bg-primary-foreground/15 hover:text-primary-foreground"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form @submit.prevent="handleSubmit" class="space-y-4 p-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-card-foreground"
                            >
                                {{
                                    t(
                                        'cart.orderType.deliveryForm.addressModal.firstName',
                                    )
                                }}
                                <span
                                    class="text-sm text-destructive sm:text-[18px]"
                                    >*</span
                                >
                            </label>
                            <div class="relative">
                                <User
                                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <input
                                    v-model="first_name"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-input bg-background py-2.5 pr-3 pl-10 text-sm text-foreground transition-colors placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/20 focus:outline-none"
                                    :class="{
                                        'border-destructive':
                                            form.errors.first_name,
                                    }"
                                    :placeholder="
                                        t(
                                            'cart.orderType.deliveryForm.addressModal.firstNamePlaceholder',
                                        )
                                    "
                                />
                            </div>
                            <p
                                v-if="form.errors.first_name"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.first_name }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-card-foreground"
                            >
                                {{
                                    t(
                                        'cart.orderType.deliveryForm.addressModal.lastName',
                                    )
                                }}
                                <span
                                    class="text-sm text-destructive sm:text-[18px]"
                                    >*</span
                                >
                            </label>
                            <div class="relative">
                                <User
                                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <input
                                    v-model="last_name"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-input bg-background py-2.5 pr-3 pl-10 text-sm text-foreground transition-colors placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/20 focus:outline-none"
                                    :class="{
                                        'border-destructive':
                                            form.errors.last_name,
                                    }"
                                    :placeholder="
                                        t(
                                            'cart.orderType.deliveryForm.addressModal.lastNamePlaceholder',
                                        )
                                    "
                                />
                            </div>
                            <p
                                v-if="form.errors.last_name"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.last_name }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-card-foreground"
                        >
                            {{
                                t(
                                    'cart.orderType.deliveryForm.addressModal.phoneNumber',
                                )
                            }}
                            <span
                                class="text-sm text-destructive sm:text-[18px]"
                                >*</span
                            >
                        </label>
                        <div class="relative">
                            <Phone
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <input
                                v-model="phone"
                                type="tel"
                                required
                                class="w-full rounded-lg border border-input bg-background py-2.5 pr-3 pl-10 text-sm text-foreground transition-colors placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/20 focus:outline-none"
                                :class="{
                                    'border-destructive': form.errors.phone,
                                }"
                                placeholder="+32 123 456 789"
                            />
                        </div>
                        <p
                            v-if="form.errors.phone"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.phone }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-card-foreground"
                        >
                            {{
                                t(
                                    'cart.orderType.deliveryForm.addressModal.streetAddress',
                                )
                            }}
                            <span
                                class="text-sm text-destructive sm:text-[18px]"
                                >*</span
                            >
                        </label>
                        <div class="relative">
                            <MapPin
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <input
                                v-model="street"
                                type="text"
                                required
                                class="w-full rounded-lg border border-input bg-background py-2.5 pr-3 pl-10 text-sm text-foreground transition-colors placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/20 focus:outline-none"
                                :class="{
                                    'border-destructive': form.errors.street,
                                }"
                                :placeholder="
                                    t(
                                        'cart.orderType.deliveryForm.addressModal.streetPlaceholder',
                                    )
                                "
                            />
                        </div>
                        <p
                            v-if="form.errors.street"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.street }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-card-foreground"
                            >
                                {{
                                    t(
                                        'cart.orderType.deliveryForm.addressModal.city',
                                    )
                                }}
                                <span
                                    class="text-sm text-destructive sm:text-[18px]"
                                    >*</span
                                >
                            </label>
                            <input
                                v-model="city"
                                type="text"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm text-foreground transition-colors placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/20 focus:outline-none"
                                :class="{
                                    'border-destructive': form.errors.city,
                                }"
                                :placeholder="
                                    t(
                                        'cart.orderType.deliveryForm.addressModal.cityPlaceholder',
                                    )
                                "
                            />
                            <p
                                v-if="form.errors.city"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.city }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-card-foreground"
                            >
                                {{
                                    t(
                                        'cart.orderType.deliveryForm.addressModal.postalCode',
                                    )
                                }}
                                <span
                                    class="text-sm text-destructive sm:text-[18px]"
                                    >*</span
                                >
                            </label>
                            <input
                                v-model="postal_code"
                                type="text"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm text-foreground transition-colors placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/20 focus:outline-none"
                                :class="{
                                    'border-destructive':
                                        form.errors.postal_code,
                                }"
                                placeholder="1000"
                            />
                            <p
                                v-if="form.errors.postal_code"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.postal_code }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-card-foreground"
                            >
                                {{
                                    t(
                                        'cart.orderType.deliveryForm.addressModal.country',
                                    )
                                }}
                                <span
                                    class="text-sm text-destructive sm:text-[18px]"
                                    >*</span
                                >
                            </label>
                            <select
                                v-model="country"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm text-foreground transition-colors focus:border-ring focus:ring-2 focus:ring-ring/20 focus:outline-none"
                            >
                                <option value="Belgium">
                                    {{
                                        t(
                                            'cart.orderType.deliveryForm.addressModal.countries.belgium',
                                        )
                                    }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-3 rounded-lg border border-info/20 bg-info/10 p-3"
                    >
                        <input
                            v-model="is_default"
                            type="checkbox"
                            id="is_default"
                            class="h-4 w-4 rounded border-input accent-primary focus:ring-ring"
                        />
                        <label
                            for="is_default"
                            class="cursor-pointer text-sm text-foreground"
                        >
                            {{
                                t(
                                    'cart.orderType.deliveryForm.addressModal.defaultAddress',
                                )
                            }}
                        </label>
                    </div>

                    <div class="flex gap-3 border-t border-border pt-4">
                        <button
                            type="button"
                            @click="closeModal"
                            class="flex-1 rounded-lg border border-input bg-background px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                        >
                            {{
                                t(
                                    'cart.orderType.deliveryForm.addressModal.cancel',
                                )
                            }}
                        </button>
                        <button
                            type="submit"
                            :disabled="is_loading"
                            class="flex-1 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span
                                v-if="is_loading"
                                class="flex items-center justify-center gap-2"
                            >
                                <span
                                    class="h-4 w-4 animate-spin rounded-full border-2 border-primary-foreground border-t-transparent"
                                ></span>
                                {{
                                    addressToEdit
                                        ? t(
                                              'cart.orderType.deliveryForm.addressModal.updating',
                                          )
                                        : t(
                                              'cart.orderType.deliveryForm.addressModal.saving',
                                          )
                                }}
                            </span>
                            <span v-else>
                                {{
                                    addressToEdit
                                        ? t(
                                              'cart.orderType.deliveryForm.addressModal.update',
                                          )
                                        : t(
                                              'cart.orderType.deliveryForm.addressModal.save',
                                          )
                                }}
                            </span>
                        </button>
                    </div>

                    <div
                        v-if="Object.keys(form.errors).length > 0"
                        class="rounded-lg border border-destructive/30 bg-destructive/10 p-3"
                    >
                        <p class="text-xs text-destructive">
                            {{
                                t(
                                    'cart.orderType.deliveryForm.addressModal.validationMessage',
                                )
                            }}
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
