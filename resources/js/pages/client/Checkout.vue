<script setup lang="ts">
import CartItemCard from '@/components/client/cart/CartItemCard.vue';
import EmptyCartList from '@/components/client/cart/EmptyCartList.vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { useCartStore } from '@/stores/cart';
import { Head } from '@inertiajs/vue3';
import { CreditCard } from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';

const cartStore = useCartStore();
const { items } = storeToRefs(cartStore);

const deliveryFee = ref<number>(2.99);
const serviceFee = ref<number>(1.5);
const updateNoteItemId = ref<number | null>(null);

const subtotal = computed(() => {
    return items.value.reduce((sum, item) => sum + (item.total_price || 0), 0);
});

const cartTotal = computed(() => {
    return subtotal.value + deliveryFee.value + serviceFee.value;
});

const formatPrice = (price: number) => {
    return price.toFixed(2);
};

const handleUpdateNotes = async (cartItemId: number, notes: string) => {
    try {
        await cartStore.updateNotes(cartItemId, notes);

        updateNoteItemId.value = cartItemId;

        setTimeout(() => {
            updateNoteItemId.value = null;
        }, 3000);
    } catch (error) {
        console.log('Failed to update notes: ', error);
    }
};

const handleUpdateQuantity = () => {};

const handleRemoveItem = async (cartItemId: number) => {
    try {
        const response = await cartStore.removeItem(cartItemId);

        toast.success(response.message);
    } catch (error) {
        console.log('Failed to remove item: ', error);
    }
};

const placeOrder = () => {};
</script>

<template>
    <Head title="Checkout Page" />
    <ClientLayout>
        <section class="mx-auto max-w-6xl px-6 py-4">
            <div class="mb-4">
                <p class="text-sm tracking-widest text-red-500 uppercase">
                    [ Checkout ]
                </p>
                <h1 class="text-4xl font-semibold uppercase md:text-5xl">
                    Your Cart
                </h1>
                <p class="mt-2 text-gray-600">
                    Review your order before proceeding to checkout
                </p>
            </div>

            <div class="flex flex-col gap-8 lg:flex-row">
                <div class="flex-1">
                    <div
                        v-if="!items.length"
                        key="empty-list"
                        class="flex min-h-[500px] items-center justify-center sm:min-h-[600px]"
                    >
                        <EmptyCartList />
                    </div>

                    <div v-else key="content" class="space-y-4">
                        <CartItemCard
                            v-for="item in items"
                            :key="item.id"
                            :cart-item="item"
                            :updateNoteItemId="updateNoteItemId"
                            @update-quantity="handleUpdateQuantity"
                            @remove="handleRemoveItem"
                            @update-notes="handleUpdateNotes"
                        />
                    </div>
                </div>

                <div v-if="items.length" class="lg:w-96">
                    <div class="sticky top-4 rounded-lg border bg-white p-6">
                        <h2 class="mb-4 text-xl font-semibold uppercase">
                            Order Summary
                        </h2>

                        <div class="space-y-3 border-b pb-4">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span>€{{ formatPrice(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Delivery Fee</span>
                                <span>€{{ formatPrice(deliveryFee) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Service Fee</span>
                                <span>€{{ formatPrice(serviceFee) }}</span>
                            </div>
                        </div>

                        <div
                            class="mt-4 flex justify-between text-lg font-semibold"
                        >
                            <span>Total</span>
                            <span class="text-red-500"
                                >€{{ formatPrice(cartTotal) }}</span
                            >
                        </div>

                        <div class="mt-6 space-y-3">
                            <textarea
                                placeholder="Special instructions or notes..."
                                class="w-full rounded-md border p-3 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                                rows="3"
                            ></textarea>

                            <button
                                @click="placeOrder"
                                class="group relative w-full overflow-hidden rounded-xl bg-gradient-to-r from-red-500 to-red-600 py-3 text-white transition-all hover:shadow-lg hover:shadow-red-200"
                            >
                                <span
                                    class="relative z-10 flex items-center justify-center gap-2 font-semibold"
                                >
                                    <CreditCard class="h-5 w-5" />
                                    Place your order
                                </span>
                                <div
                                    class="absolute inset-0 -translate-x-full transform bg-gradient-to-r from-red-600 to-red-700 transition-transform duration-300 group-hover:translate-x-0"
                                ></div>
                            </button>
                        </div>

                        <div class="mt-4 text-center text-xs text-gray-500">
                            <p>By placing your order, you agree to our</p>
                            <p>Terms of Service and Privacy Policy</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </ClientLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
