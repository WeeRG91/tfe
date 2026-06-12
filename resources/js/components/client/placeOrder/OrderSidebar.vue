<script setup lang="ts">
import { CreditCard } from 'lucide-vue-next';
import { formatPrice } from '@/lib/utils';

defineProps<{
    itemsCount: number;
    cartTotal: number;
    isFormValid: boolean;
    isLoading: boolean;
}>();

const emit = defineEmits<{
    placeOrder: [];
}>();
</script>

<template>
    <div class="lg:w-80">
        <div class="sticky top-6 rounded-lg border bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold uppercase">
                Ready to Order?
            </h2>

            <div class="space-y-3">
                <div class="rounded-md bg-gray-50 p-3">
                    <div class="flex justify-between text-sm">
                        <span>Items:</span>
                        <span>{{ itemsCount }} item(s)</span>
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

                <div
                    v-if="!isFormValid"
                    class="rounded-md bg-yellow-50 p-2 text-xs text-yellow-800"
                >
                    ⚠️ Please complete all required fields before placing your
                    order
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
                        {{ isLoading ? 'Placing Order...' : 'Confirm Order' }}
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
</template>

<style scoped></style>
