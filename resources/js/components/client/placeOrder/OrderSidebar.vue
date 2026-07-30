<script setup lang="ts">
import { CreditCard } from 'lucide-vue-next';
import { formatPrice } from '@/lib/utils';
import { useI18n } from 'vue-i18n';

defineProps<{
    itemsCount: number;
    totalIncVat: number;
    isFormValid: boolean;
    isLoading: boolean;
}>();

const emit = defineEmits<{
    placeOrder: [];
}>();

const { t } = useI18n();
</script>

<template>
    <div class="lg:w-80">
        <div class="sticky rounded-lg border bg-white p-6 sm:top-20">
            <h2 class="mb-4 text-lg font-semibold uppercase">
                {{ t('cart.placeOrderSidebar.title') }}
            </h2>

            <div class="space-y-3">
                <div class="rounded-md bg-gray-50 p-3">
                    <div class="flex justify-between text-sm">
                        <span
                            >{{ t('cart.placeOrderSidebar.itemsLabel') }}:</span
                        >
                        <span>
                            {{
                                t('cart.placeOrderSidebar.title', itemsCount)
                            }}</span
                        >
                    </div>
                    <div
                        class="mt-1 flex justify-between text-sm font-semibold"
                    >
                        <span>{{ t('cart.placeOrderSidebar.total') }}:</span>
                        <span class="text-red-500"
                            >€{{ formatPrice(totalIncVat) }}</span
                        >
                    </div>
                </div>

                <div
                    v-if="!isFormValid"
                    class="rounded-md bg-yellow-50 p-2 text-xs text-yellow-800"
                >
                    ⚠️ {{ t('cart.placeOrderSidebar.invalidForm') }}
                </div>

                <button
                    @click="emit('placeOrder')"
                    :disabled="!isFormValid || isLoading"
                    class="group relative w-full overflow-hidden rounded-lg bg-gradient-to-r from-red-500 to-red-600 py-3 text-sm text-white transition-all hover:shadow-md hover:shadow-red-200 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <span
                        class="relative z-10 flex items-center justify-center gap-2 font-semibold"
                    >
                        <span
                            v-if="isLoading"
                            class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                        ></span>
                        <CreditCard v-else class="h-4 w-4" />
                        {{
                            isLoading
                                ? t('cart.placeOrderSidebar.placingOrder')
                                : t('cart.placeOrderSidebar.confirmOrder')
                        }}
                    </span>
                    <div
                        class="absolute inset-0 -translate-x-full transform bg-gradient-to-r from-red-600 to-red-700 transition-transform duration-300 group-hover:translate-x-0"
                    ></div>
                </button>

                <div class="text-center text-xs text-gray-500">
                    <p>{{ t('cart.placeOrderSidebar.agreement') }}</p>
                    <p>{{ t('cart.placeOrderSidebar.termsAndPrivacy') }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
