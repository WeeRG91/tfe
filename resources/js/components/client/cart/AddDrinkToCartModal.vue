<script setup lang="ts">
import { useCartStore } from '@/stores/cart';
import { storeToRefs } from 'pinia';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { ClientDrinkType } from '@/types/drink';
import { formatPrice } from '@/lib/utils';
import { ItemTypeEnum } from '@/types/cart';

const props = defineProps<{
    drink: ClientDrinkType;
    open: boolean;
}>();

const emit = defineEmits(['close']);

const cartStore = useCartStore();
const { isLoading } = storeToRefs(cartStore);

const quantity = ref<number>(1);
const notes = ref<string>('');
const error = ref<string>('');

const totalPrice = computed(() => {
    const base = props.drink.price ?? 0;

    return base * quantity.value;
});

const addToCart = async () => {
    try {
        const response = await cartStore.addDrink({
            item_id: props.drink.id,
            item_type: ItemTypeEnum.DRINK,
            quantity: quantity.value,
            notes: notes.value,
        });

        emit('close');

        toast.success(response.message);
    } catch (error) {
        console.log(error);
        toast.error('Failed to add item');
    }
};

const decrementQuantity = () => {
    if (quantity.value > 1) quantity.value--;
};

const incrementQuantity = () => {
    quantity.value++;
};

onMounted(() => {
    if (props.open) {
        document.body.style.overflow = 'hidden';
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
});

watch(
    () => props.open,
    async (isOpen) => {
        if (isOpen) {
            quantity.value = 1;
            notes.value = '';
            error.value = '';
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    },
);
</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            @click="$emit('close')"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
        >
            <div
                v-if="open"
                @click.stop
                class="relative flex w-full max-w-md animate-in flex-col rounded-2xl border bg-white shadow-xl duration-200 zoom-in-95 fade-in slide-in-from-top-4"
                style="max-height: 90vh"
            >
                <div class="relative flex-shrink-0">
                    <img
                        :src="drink.main_image"
                        :alt="drink.name"
                        class="h-32 w-full rounded-t-2xl object-cover"
                    />
                    <button
                        @click="$emit('close')"
                        class="absolute top-3 right-3 flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-gray-600 transition hover:bg-white hover:text-gray-900"
                    >
                        ✕
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4">
                    <div class="mb-3">
                        <div class="flex items-start justify-between">
                            <h2 class="text-lg font-bold uppercase">
                                {{ drink.name }}
                            </h2>
                            <span class="text-lg font-bold text-red-500">
                                €{{ formatPrice(drink.price) }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h3
                            class="mb-2 text-xs font-semibold tracking-wider text-gray-700 uppercase"
                        >
                            Special instructions (optional)
                        </h3>
                        <div class="relative">
                            <textarea
                                v-model="notes"
                                rows="1"
                                placeholder="e.g.more sugar, no sugar..."
                                class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 placeholder-gray-400 transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                                :class="{
                                    'border-red-500 ring-2 ring-red-500/20':
                                        notes.length > 0,
                                }"
                            ></textarea>
                            <div
                                v-if="notes.length > 0"
                                class="absolute right-2 bottom-2 text-xs text-gray-400"
                            >
                                {{ notes.length }}/500
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h3
                            class="mb-2 text-xs font-semibold tracking-wider text-gray-700 uppercase"
                        >
                            Quantity
                        </h3>
                        <div class="flex items-center gap-3">
                            <button
                                @click="decrementQuantity"
                                :disabled="quantity <= 1"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300 text-gray-600 transition hover:border-red-500 hover:bg-red-50 hover:text-red-500 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                −
                            </button>
                            <span
                                class="w-10 text-center text-lg font-semibold"
                            >
                                {{ quantity }}
                            </span>
                            <button
                                @click="incrementQuantity"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300 text-gray-600 transition hover:border-red-500 hover:bg-red-50 hover:text-red-500"
                            >
                                +
                            </button>
                        </div>
                    </div>
                </div>

                <div class="border-t p-5 pt-4">
                    <div class="mb-3 flex items-center justify-between">
                        <span
                            class="text-xs tracking-wider text-gray-600 uppercase"
                        >
                            Total
                        </span>
                        <span class="text-2xl font-bold text-red-500">
                            €{{ totalPrice.toFixed(2) }}
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <button
                            @click="$emit('close')"
                            class="flex-1 rounded-lg border border-gray-300 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            @click="addToCart"
                            class="group relative flex-1 overflow-hidden rounded-lg bg-gradient-to-r from-red-500 to-red-600 py-3 text-white transition-all hover:shadow-lg hover:shadow-red-200"
                        >
                            <span
                                class="relative z-10 flex items-center justify-center"
                            >
                                <span
                                    v-if="isLoading"
                                    class="flex items-center justify-center"
                                >
                                    <svg
                                        class="h-4 w-4 animate-spin text-white"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        ></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                </span>
                                <span v-else>
                                    Add to Cart
                                    <span
                                        class="animate-arrow ml-2 inline-block"
                                        >→</span
                                    >
                                </span>
                            </span>
                            <div
                                class="absolute inset-0 -translate-x-full transform bg-gradient-to-r from-red-600 to-red-700 transition-transform duration-300 group-hover:translate-x-0"
                            ></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.animate-arrow {
    transition: transform 0.2s ease;
}

button:hover .animate-arrow {
    transform: translateX(4px);
}

.overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: #e5e7eb #f3f4f6;
}

.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f3f4f6;
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
}

@keyframes fade-in {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes zoom-in-95 {
    from {
        transform: scale(0.95);
    }
    to {
        transform: scale(1);
    }
}

@keyframes slide-in-from-top-4 {
    from {
        transform: translateY(-1rem);
    }
    to {
        transform: translateY(0);
    }
}

.animate-in {
    animation-duration: 0.2s;
    animation-fill-mode: both;
}

.fade-in {
    animation-name: fade-in;
}

.zoom-in-95 {
    animation-name: zoom-in-95;
}

.slide-in-from-top-4 {
    animation-name: slide-in-from-top-4;
}
</style>
