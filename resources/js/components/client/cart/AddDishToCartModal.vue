<script setup lang="ts">
import { useCartStore } from '@/stores/cart';
import { ClientDishType } from '@/types/dish';
import { storeToRefs } from 'pinia';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { formatPrice } from '@/lib/utils';
import { ItemTypeEnum } from '@/types/cart';

const props = defineProps<{
    dish: ClientDishType;
    open: boolean;
}>();

const emit = defineEmits(['close']);

const cartStore = useCartStore();
const { isLoading } = storeToRefs(cartStore);

const selectedMeat = ref<number | null>(null);
const quantity = ref<number>(1);
const removedIngredients = ref<number[]>([]);
const notes = ref<string>('');
const error = ref<string>('');

const meats = computed(() => props.dish?.meats ?? []);
const ingredients = computed(() => props.dish?.ingredients ?? []);
const totalPrice = computed(() => {
    const base = props.dish.price ?? 0;

    const meat = meats.value.find((m: any) => m.id === selectedMeat.value);
    const extra = meat?.extra_price ?? 0;

    return (base + extra) * quantity.value;
});

const toggleIngredient = (id: number) => {
    if (removedIngredients.value.includes(id)) {
        removedIngredients.value = removedIngredients.value.filter(
            (i) => i !== id,
        );
    } else {
        removedIngredients.value.push(id);
    }
};

const addToCart = async () => {
    if (!selectedMeat.value) {
        error.value = 'Please select meat';
        return;
    }

    try {
        const response = await cartStore.addDish({
            item_id: props.dish.id,
            item_type: ItemTypeEnum.DISH,
            meat_id: selectedMeat.value,
            quantity: quantity.value,
            removed_ingredients: removedIngredients.value,
            notes: notes.value || '',
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
            selectedMeat.value = null;
            quantity.value = 1;
            removedIngredients.value = [];
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
                        :src="dish.main_image"
                        :alt="dish.name"
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
                                {{ dish.name }}
                            </h2>
                            <span class="text-lg font-bold text-red-500">
                                €{{ formatPrice(dish.price) }}
                            </span>
                        </div>
                    </div>

                    <div v-if="meats.length" class="mb-3">
                        <h3 class="mb-2 flex justify-between">
                            <span
                                class="text-xs font-semibold tracking-wider text-gray-700 uppercase"
                                >Choose your meat</span
                            >
                            <span
                                v-if="error && selectedMeat === null"
                                class="text-xs font-normal text-red-500"
                                >{{ error }}</span
                            >
                        </h3>
                        <div class="grid grid-cols-2 gap-2">
                            <label
                                v-for="meat in meats"
                                :key="meat.id"
                                class="group relative flex cursor-pointer items-center gap-3 rounded-lg border px-1 py-0.5 transition-all duration-200 hover:shadow-md"
                                :class="{
                                    'border-red-500 bg-gradient-to-r from-red-50 to-red-50/50':
                                        selectedMeat === meat.id,
                                    'border-gray-200 hover:border-red-200':
                                        selectedMeat !== meat.id,
                                }"
                            >
                                <div class="relative">
                                    <div
                                        class="h-7 w-7 overflow-hidden rounded-lg transition-transform duration-200 group-hover:scale-105"
                                        :class="{
                                            'opacity-70':
                                                selectedMeat === meat.id,
                                        }"
                                    >
                                        <img
                                            :src="
                                                meat.main_image ||
                                                `https://ui-avatars.com/api/?name=${meat.name}&background=EF4444&color=fff&size=40`
                                            "
                                            :alt="meat.name"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>

                                    <div
                                        v-if="selectedMeat === meat.id"
                                        class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white shadow-lg"
                                    >
                                        <svg
                                            class="h-3 w-3"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="3"
                                                d="M5 13l4 4L19 7"
                                            />
                                        </svg>
                                    </div>
                                </div>

                                <div class="flex-1">
                                    <span
                                        class="block text-xs font-semibold transition-all"
                                        :class="{
                                            'text-red-600':
                                                selectedMeat === meat.id,
                                            'text-gray-800 group-hover:text-red-600':
                                                selectedMeat !== meat.id,
                                        }"
                                    >
                                        {{ meat.name }}
                                    </span>
                                    <span
                                        v-if="meat.extra_price > 0"
                                        class="text-xs font-medium text-red-500"
                                    >
                                        +€{{ meat.extra_price }}
                                    </span>
                                </div>

                                <div class="relative">
                                    <div
                                        class="flex h-5 w-5 items-center justify-center rounded-full border-1 transition-all"
                                        :class="{
                                            'border-red-500 bg-red-500':
                                                selectedMeat === meat.id,
                                            'border-gray-300 group-hover:border-red-400':
                                                selectedMeat !== meat.id,
                                        }"
                                    >
                                        <div
                                            v-if="selectedMeat === meat.id"
                                            class="h-2 w-2 rounded-full bg-white"
                                        ></div>
                                    </div>
                                    <input
                                        type="radio"
                                        :value="meat.id"
                                        v-model="selectedMeat"
                                        class="absolute inset-0 cursor-pointer opacity-0"
                                    />
                                </div>
                            </label>
                        </div>
                    </div>

                    <div v-if="ingredients.length" class="mb-3">
                        <h3
                            class="mb-2 text-xs font-semibold tracking-wider text-gray-700 uppercase"
                        >
                            Remove ingredients
                        </h3>
                        <div class="grid grid-cols-2 gap-1.5 sm:grid-cols-3">
                            <label
                                v-for="ing in ingredients"
                                :key="ing.id"
                                class="group relative flex cursor-pointer items-center gap-2 rounded-lg border p-1.5 transition-all duration-200 hover:shadow-sm"
                                :class="{
                                    'border-red-400 bg-gradient-to-r from-red-50 to-red-50/50':
                                        removedIngredients.includes(ing.id),
                                    'border-gray-200 hover:border-red-200':
                                        !removedIngredients.includes(ing.id),
                                }"
                            >
                                <div class="relative flex-shrink-0">
                                    <div
                                        class="h-6 w-6 overflow-hidden rounded-md transition-transform duration-200 group-hover:scale-105"
                                        :class="{
                                            'opacity-70':
                                                removedIngredients.includes(
                                                    ing.id,
                                                ),
                                        }"
                                    >
                                        <img
                                            :src="
                                                ing.main_image ||
                                                `https://ui-avatars.com/api/?name=${ing.name}&background=EF4444&color=fff&size=28`
                                            "
                                            :alt="ing.name"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <span
                                        class="block truncate text-xs font-medium transition-all"
                                        :class="{
                                            'text-red-600 line-through':
                                                removedIngredients.includes(
                                                    ing.id,
                                                ),
                                            'text-gray-800 group-hover:text-red-600':
                                                !removedIngredients.includes(
                                                    ing.id,
                                                ),
                                        }"
                                    >
                                        {{ ing.name }}
                                    </span>
                                </div>

                                <div class="relative flex-shrink-0">
                                    <div
                                        class="flex h-3.5 w-3.5 items-center justify-center rounded border transition-all"
                                        :class="{
                                            'border-red-500 bg-red-500':
                                                removedIngredients.includes(
                                                    ing.id,
                                                ),
                                            'border-gray-300 group-hover:border-red-400':
                                                !removedIngredients.includes(
                                                    ing.id,
                                                ),
                                        }"
                                    >
                                        <svg
                                            v-if="
                                                removedIngredients.includes(
                                                    ing.id,
                                                )
                                            "
                                            class="h-2 w-2 text-white"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="3"
                                                d="M5 13l4 4L19 7"
                                            />
                                        </svg>
                                    </div>
                                    <input
                                        type="checkbox"
                                        :value="ing.id"
                                        :checked="
                                            removedIngredients.includes(ing.id)
                                        "
                                        @change="toggleIngredient(ing.id)"
                                        class="absolute inset-0 cursor-pointer opacity-0"
                                    />
                                </div>
                            </label>
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
                                placeholder="e.g., extra sauce, no onions, make it spicy..."
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
                            €{{ formatPrice(totalPrice) }}
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
