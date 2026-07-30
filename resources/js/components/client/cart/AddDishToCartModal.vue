<script setup lang="ts">
import { formatPrice } from '@/lib/utils';
import { useCartStore } from '@/stores/cart';
import { ItemTypeEnum } from '@/types/cart';
import { ClientDishType } from '@/types/dish';
import { Flame } from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { useI18n } from 'vue-i18n';
import { getSpicyLevelLabels } from '@/lib/const';

const props = defineProps<{
    dish: ClientDishType;
    open: boolean;
}>();

const emit = defineEmits<{
    close: [];
}>();

const { t } = useI18n();
const spicyLevelLabels = computed(() => getSpicyLevelLabels(t));

const cartStore = useCartStore();
const { isLoading } = storeToRefs(cartStore);

const selectedMeat = ref<number | null>(null);
const quantity = ref<number>(1);
const removedIngredients = ref<number[]>([]);
const notes = ref<string>('');
const error = ref<string>('');

const spicyLevel = ref<number>(props.dish.default_spicy_level ?? 0);
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
        error.value = t('menu.addDishModal.selectMeat');
        return;
    }

    try {
        const response = await cartStore.addDish({
            item_id: props.dish.id,
            item_type: ItemTypeEnum.DISH,
            meat_id: selectedMeat.value,
            quantity: quantity.value,
            spicy_level: spicyLevel.value,
            removed_ingredients: removedIngredients.value,
            notes: notes.value || '',
        });

        emit('close');

        toast.success(response.message);
    } catch (error) {
        console.log(error);
        toast.error(t('menu.addDishModal.addFailed'));
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
            spicyLevel.value = props.dish.default_spicy_level ?? 0;
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
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
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
                                >{{ t('menu.addDishModal.chooseMeat') }}</span
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

                                <div class="flex-1 flex-row">
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
                            {{ t('menu.addDishModal.removeIngredients') }}
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
                            {{ t('menu.addDishModal.instructionsOptional') }}
                        </h3>
                        <div class="relative">
                            <textarea
                                v-model="notes"
                                rows="1"
                                :placeholder="
                                    t(
                                        'menu.addDishModal.instructionsPlaceholder',
                                    )
                                "
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
                            class="mb-2 flex flex-row items-center text-xs font-semibold tracking-wider text-gray-700 uppercase"
                        >
                            <span
                                :class="[
                                    spicyLevel === 0 && 'text-green-500',
                                    spicyLevel === 1 && 'text-yellow-500',
                                    spicyLevel === 2 && 'text-orange-500',
                                    spicyLevel === 3 && 'text-red-500',
                                ]"
                            >
                                {{ t('common.spicyLevel.title') }}
                            </span>
                            <div class="flex items-center gap-1">
                                <Flame
                                    v-for="i in spicyLevel"
                                    :key="i"
                                    fill="currentColor"
                                    class="h-3.5 w-3.5 transition-colors duration-200 sm:h-4 sm:w-4"
                                    :class="[
                                        i <= spicyLevel
                                            ? [
                                                  'text-green-500',
                                                  'text-yellow-500',
                                                  'text-orange-500',
                                                  'text-red-500',
                                              ][i]
                                            : 'text-gray-300',
                                    ]"
                                />
                            </div>
                        </h3>

                        <div class="flex items-center gap-2">
                            <button
                                v-for="level in 4"
                                :key="level - 1"
                                @click="spicyLevel = level - 1"
                                class="relative flex h-10 w-10 items-center justify-center rounded-full border-2 text-sm font-medium transition-all duration-200 hover:scale-105"
                                :class="[
                                    spicyLevel === level - 1
                                        ? {
                                              0: 'border-green-500 bg-green-500 text-white shadow-lg shadow-green-200',
                                              1: 'border-yellow-500 bg-yellow-500 text-white shadow-lg shadow-yellow-200',
                                              2: 'border-orange-500 bg-orange-500 text-white shadow-lg shadow-orange-200',
                                              3: 'border-red-500 bg-red-500 text-white shadow-lg shadow-red-200',
                                          }[level - 1]
                                        : 'border-gray-300 bg-white text-gray-600 hover:border-gray-400',
                                ]"
                            >
                                <span class="relative z-10">{{
                                    level - 1
                                }}</span>
                            </button>

                            <div class="ml-2 text-xs font-medium text-gray-600">
                                <span
                                    :class="[
                                        spicyLevel === 0 && 'text-green-600',
                                        spicyLevel === 1 && 'text-yellow-600',
                                        spicyLevel === 2 && 'text-orange-600',
                                        spicyLevel === 3 && 'text-red-600',
                                    ]"
                                >
                                    {{ spicyLevelLabels[spicyLevel] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h3
                            class="mb-2 text-xs font-semibold tracking-wider text-gray-700 uppercase"
                        >
                            {{ t('menu.addDishModal.quantity') }}
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
                            {{ t('menu.addDishModal.total') }}
                        </span>
                        <span class="text-2xl font-bold text-red-500">
                            €{{ formatPrice(totalPrice) }}
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <button
                            @click="emit('close')"
                            class="flex-1 rounded-lg border border-gray-300 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            {{ $t('menu.addDishModal.cancel') }}
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
                                    class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent text-white"
                                ></span>
                                <span v-else>
                                    {{ t('menu.addDishModal.addToCart') }}
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
    </Transition>
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

.fixed {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}
</style>
