<script setup lang="ts">
import CartItemCard from '@/components/client/cart/CartItemCard.vue';
import EmptyCartList from '@/components/client/cart/EmptyCartList.vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { formatPrice } from '@/lib/utils';
import { useCartStore } from '@/stores/cart';
import { Head } from '@inertiajs/vue3';
import { Coffee, CreditCard, Utensils } from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import { ItemTypeEnum } from '@/types/cart';

const cartStore = useCartStore();
const { items } = storeToRefs(cartStore);

const updateNoteItemId = ref<number | null>(null);
const loadingRemoveItemId = ref<number | null>(null);

const dishItems = computed(() =>
    items.value.filter((item) => item.item_type === ItemTypeEnum.DISH),
);

const drinkItems = computed(() =>
    items.value.filter((item) => item.item_type === ItemTypeEnum.DRINK),
);

const subtotalDishes = computed(() => {
    return dishItems.value.reduce((sum, item) => sum + (item.total_inc_vat || 0), 0);
});

const subtotalDrinks = computed(() => {
    return drinkItems.value.reduce((sum, item) => sum + (item.total_inc_vat || 0), 0);
});

const subtotal = computed(() => {
    return subtotalDishes.value + subtotalDrinks.value;
});

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

const handleUpdateQuantity = async (
    cartItemId: number,
    action: 'increase' | 'decrease',
) => {
    try {
        await cartStore.updateQuantity(cartItemId, action);
    } catch (error) {
        console.log('Failed to update quantity: ', error);
    }
};

const handleRemoveItem = async (cartItemId: number) => {
    loadingRemoveItemId.value = cartItemId;

    try {
        const response = await cartStore.removeItem(cartItemId);

        toast.success(response.message);
    } catch (error) {
        console.log('Failed to remove item: ', error);
    } finally {
        loadingRemoveItemId.value = null;
    }
};
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
                <p class="mt-1 text-sm text-gray-600">
                    Review your cart before placing your order
                </p>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row">
                <div class="flex-1">
                    <div
                        v-if="!items.length"
                        key="empty-list"
                        class="flex min-h-[400px] items-center justify-center"
                    >
                        <EmptyCartList />
                    </div>

                    <div v-else key="content" class="space-y-4">
                        <div v-if="dishItems.length > 0" class="space-y-2">
                            <div
                                class="sticky top-0 z-10 py-1.5 backdrop-blur-sm"
                            >
                                <div class="flex items-center gap-2">
                                    <Utensils class="h-4 w-4 text-red-500" />
                                    <h2
                                        class="text-sm font-semibold tracking-wide text-gray-700 uppercase"
                                    >
                                        Dishes
                                    </h2>
                                    <span class="text-xs text-gray-400">
                                        {{ dishItems.length }}
                                    </span>
                                </div>
                                <div class="mt-0.5 h-0.5 w-10 bg-red-500"></div>
                            </div>

                            <div class="space-y-3">
                                <CartItemCard
                                    v-for="item in dishItems"
                                    :key="item.id"
                                    :cart-item="item"
                                    :type="ItemTypeEnum.DISH"
                                    :update-note-item-id="updateNoteItemId"
                                    :loading-remove-item-id="
                                        loadingRemoveItemId
                                    "
                                    @update-quantity="handleUpdateQuantity"
                                    @remove="handleRemoveItem"
                                    @update-notes="handleUpdateNotes"
                                />
                            </div>
                        </div>

                        <div v-if="drinkItems.length > 0" class="space-y-2">
                            <div
                                class="sticky top-0 z-10 py-1.5 backdrop-blur-sm"
                            >
                                <div class="flex items-center gap-2">
                                    <Coffee class="h-4 w-4 text-blue-500" />
                                    <h2
                                        class="text-sm font-semibold tracking-wide text-gray-700 uppercase"
                                    >
                                        Drinks
                                    </h2>
                                    <span class="text-xs text-gray-400">
                                        {{ drinkItems.length }}
                                    </span>
                                </div>
                                <div
                                    class="mt-0.5 h-0.5 w-10 bg-blue-500"
                                ></div>
                            </div>

                            <div class="space-y-3">
                                <CartItemCard
                                    v-for="item in drinkItems"
                                    :key="item.id"
                                    :cart-item="item"
                                    :type="ItemTypeEnum.DRINK"
                                    :update-note-item-id="updateNoteItemId"
                                    :loading-remove-item-id="
                                        loadingRemoveItemId
                                    "
                                    @update-quantity="handleUpdateQuantity"
                                    @remove="handleRemoveItem"
                                    @update-notes="handleUpdateNotes"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="items.length" class="lg:w-80">
                    <div class="sticky sm:top-20 rounded-lg border bg-white p-4">
                        <h2 class="mb-3 text-lg font-semibold uppercase">
                            Order Summary
                        </h2>

                        <div class="mt-3 flex justify-between font-semibold">
                            <span>Total (incl. VAT)</span>
                            <span class="text-red-500"
                                >€{{ formatPrice(subtotal) }}</span
                            >
                        </div>

                        <div class="mt-4 space-y-3">
                            <textarea
                                placeholder="Special instructions..."
                                class="w-full rounded-md border p-2 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                                rows="2"
                            ></textarea>

                            <button
                                @click="$inertia.visit('/cart/place-order')"
                                class="group relative w-full overflow-hidden rounded-lg bg-gradient-to-r from-red-500 to-red-600 py-2 text-sm text-white transition-all hover:shadow-md hover:shadow-red-200"
                            >
                                <span
                                    class="relative z-10 flex items-center justify-center gap-2 font-semibold"
                                >
                                    <CreditCard class="h-4 w-4" />
                                    Place your order
                                </span>
                                <div
                                    class="absolute inset-0 -translate-x-full transform bg-gradient-to-r from-red-600 to-red-700 transition-transform duration-300 group-hover:translate-x-0"
                                ></div>
                            </button>
                        </div>

                        <div class="mt-3 text-center text-xs text-gray-500">
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
