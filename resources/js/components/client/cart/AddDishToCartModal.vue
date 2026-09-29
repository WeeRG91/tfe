<script setup lang="ts">
import { getSpicyLevelLabels } from '@/lib/const';
import { formatPrice } from '@/lib/utils';
import { useCartStore } from '@/stores/cart';
import { ItemTypeEnum } from '@/types/cart';
import { ClientDishType } from '@/types/dish';
import { Flame } from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

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
    const base = Number(props.dish.price ?? 0);

    const meat = meats.value.find((m) => m.id === selectedMeat.value);

    const extra = Number(meat?.extra_price ?? 0);

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
    <Teleport to="body">
        <Transition name="client-modal" appear>
            <div
                v-if="open"
                @click="$emit('close')"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            >
                <div
                    @click.stop
                    class="client-modal-panel relative flex w-full max-w-md flex-col rounded-2xl border border-border bg-card text-card-foreground shadow-xl"
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
                            class="absolute top-3 right-3 flex h-7 w-7 items-center justify-center rounded-full bg-popover/90 text-popover-foreground hover:bg-popover"
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
                                <span class="text-lg font-bold text-primary">
                                    €{{ formatPrice(dish.price) }}
                                </span>
                            </div>
                        </div>

                        <div v-if="meats.length" class="mb-3">
                            <h3 class="mb-2 flex justify-between">
                                <span
                                    class="text-xs font-semibold tracking-wider text-card-foreground uppercase"
                                    >{{
                                        t('menu.addDishModal.chooseMeat')
                                    }}</span
                                >
                                <span
                                    v-if="error && selectedMeat === null"
                                    class="text-xs font-normal text-destructive"
                                    >{{ error }}</span
                                >
                            </h3>
                            <div class="grid grid-cols-2 gap-2">
                                <label
                                    v-for="meat in meats"
                                    :key="meat.id"
                                    class="group relative flex cursor-pointer items-center gap-3 rounded-lg border px-1 py-0.5 transition-all duration-200 hover:shadow-md"
                                    :class="{
                                        'border-primary bg-primary/10':
                                            selectedMeat === meat.id,
                                        'border-border hover:border-primary/40':
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
                                            class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg"
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
                                                'text-primary':
                                                    selectedMeat === meat.id,
                                                'text-card-foreground group-hover:text-primary':
                                                    selectedMeat !== meat.id,
                                            }"
                                        >
                                            {{ meat.name }}
                                        </span>
                                        <span
                                            class="text-xs font-medium text-primary"
                                        >
                                            +€{{ meat.extra_price }}
                                        </span>
                                    </div>

                                    <div class="relative">
                                        <div
                                            class="flex h-5 w-5 items-center justify-center rounded-full border-1 transition-all"
                                            :class="{
                                                'border-primary bg-primary':
                                                    selectedMeat === meat.id,
                                                'border-input group-hover:border-primary/60':
                                                    selectedMeat !== meat.id,
                                            }"
                                        >
                                            <div
                                                v-if="selectedMeat === meat.id"
                                                class="h-2 w-2 rounded-full bg-primary-foreground"
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
                                class="mb-2 text-xs font-semibold tracking-wider text-card-foreground uppercase"
                            >
                                {{ t('menu.addDishModal.removeIngredients') }}
                            </h3>
                            <div
                                class="grid grid-cols-2 gap-1.5 sm:grid-cols-3"
                            >
                                <label
                                    v-for="ing in ingredients"
                                    :key="ing.id"
                                    class="group relative flex cursor-pointer items-center gap-2 rounded-lg border p-1.5 transition-all duration-200 hover:shadow-sm"
                                    :class="{
                                        'border-destructive/40 bg-destructive/10':
                                            removedIngredients.includes(ing.id),
                                        'border-border hover:border-primary/40':
                                            !removedIngredients.includes(
                                                ing.id,
                                            ),
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
                                                'text-destructive line-through':
                                                    removedIngredients.includes(
                                                        ing.id,
                                                    ),
                                                'text-card-foreground group-hover:text-primary':
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
                                                'border-destructive bg-destructive':
                                                    removedIngredients.includes(
                                                        ing.id,
                                                    ),
                                                'border-input group-hover:border-primary/60':
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
                                                class="h-2 w-2 text-destructive-foreground"
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
                                                removedIngredients.includes(
                                                    ing.id,
                                                )
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
                                class="mb-2 text-xs font-semibold tracking-wider text-card-foreground uppercase"
                            >
                                {{
                                    t('menu.addDishModal.instructionsOptional')
                                }}
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
                                    class="w-full resize-none rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground transition placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/20 focus:outline-none"
                                    :class="{
                                        'border-primary ring-2 ring-primary/20':
                                            notes.length > 0,
                                    }"
                                ></textarea>
                                <div
                                    v-if="notes.length > 0"
                                    class="absolute right-2 bottom-2 text-xs text-muted-foreground"
                                >
                                    {{ notes.length }}/500
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h3
                                class="mb-2 flex flex-row items-center text-xs font-semibold tracking-wider text-card-foreground uppercase"
                            >
                                <span
                                    :class="[
                                        spicyLevel === 0 && 'text-success',
                                        spicyLevel === 1 && 'text-warning',
                                        spicyLevel === 2 && 'text-primary',
                                        spicyLevel === 3 && 'text-destructive',
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
                                                      'text-success',
                                                      'text-warning',
                                                      'text-primary',
                                                      'text-destructive',
                                                  ][i]
                                                : 'text-muted-foreground/40',
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
                                                  0: 'border-success bg-success text-success-foreground shadow-lg',
                                                  1: 'border-warning bg-warning text-warning-foreground shadow-lg',
                                                  2: 'border-primary bg-primary text-primary-foreground shadow-lg',
                                                  3: 'border-destructive bg-destructive text-destructive-foreground shadow-lg',
                                              }[level - 1]
                                            : 'border-input bg-background text-muted-foreground hover:border-primary',
                                    ]"
                                >
                                    <span class="relative z-10">{{
                                        level - 1
                                    }}</span>
                                </button>

                                <div
                                    class="ml-2 text-xs font-medium text-muted-foreground"
                                >
                                    <span
                                        :class="[
                                            spicyLevel === 0 && 'text-success',
                                            spicyLevel === 1 && 'text-warning',
                                            spicyLevel === 2 && 'text-primary',
                                            spicyLevel === 3 &&
                                                'text-destructive',
                                        ]"
                                    >
                                        {{ spicyLevelLabels[spicyLevel] }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h3
                                class="mb-2 text-xs font-semibold tracking-wider text-card-foreground uppercase"
                            >
                                {{ t('menu.addDishModal.quantity') }}
                            </h3>
                            <div class="flex items-center gap-3">
                                <button
                                    @click="decrementQuantity"
                                    :disabled="quantity <= 1"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-input bg-background text-foreground transition hover:border-primary hover:bg-primary/10 hover:text-primary disabled:cursor-not-allowed disabled:opacity-50"
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
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-input bg-background text-foreground transition hover:border-primary hover:bg-primary/10 hover:text-primary"
                                >
                                    +
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-border p-5 pt-4">
                        <div class="mb-3 flex items-center justify-between">
                            <span
                                class="text-xs tracking-wider text-muted-foreground uppercase"
                            >
                                {{ t('menu.addDishModal.total') }}
                            </span>
                            <span class="text-2xl font-bold text-primary">
                                €{{ formatPrice(totalPrice) }}
                            </span>
                        </div>

                        <div class="flex gap-2">
                            <button
                                @click="emit('close')"
                                class="flex-1 rounded-lg border border-input bg-background py-3 text-sm font-medium text-foreground transition hover:bg-accent hover:text-accent-foreground"
                            >
                                {{ $t('menu.addDishModal.cancel') }}
                            </button>
                            <button
                                @click="addToCart"
                                class="group relative flex-1 overflow-hidden rounded-lg bg-gradient-to-r from-primary to-primary/80 py-3 text-primary-foreground transition-all hover:shadow-lg hover:shadow-primary/20"
                            >
                                <span
                                    class="relative z-10 flex items-center justify-center"
                                >
                                    <span
                                        v-if="isLoading"
                                        class="h-4 w-4 animate-spin rounded-full border-2 border-primary-foreground border-t-transparent text-primary-foreground"
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
                                    class="absolute inset-0 -translate-x-full transform bg-gradient-to-r from-primary/90 to-primary transition-transform duration-300 group-hover:translate-x-0"
                                ></div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
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
    scrollbar-color: var(--border) var(--muted);
}

.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: var(--muted);
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: var(--muted-foreground);
}

.fixed {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

.client-modal-enter-active,
.client-modal-leave-active {
    transition: opacity 200ms ease;
}

.client-modal-enter-active .client-modal-panel,
.client-modal-leave-active .client-modal-panel {
    transition:
        opacity 200ms ease,
        transform 200ms ease;
}

.client-modal-enter-from,
.client-modal-leave-to {
    opacity: 0;
}

.client-modal-enter-from .client-modal-panel,
.client-modal-leave-to .client-modal-panel {
    opacity: 0;
    transform: translateY(-1rem) scale(0.95);
}
</style>
