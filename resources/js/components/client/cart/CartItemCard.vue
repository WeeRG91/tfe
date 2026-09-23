<script setup lang="ts">
import { getSpicyLevelLabels } from '@/lib/const';
import { useCartStore } from '@/stores/cart';
import { CartItemType, ItemTypeEnum } from '@/types/cart';
import { SquarePen } from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { formatPrice } from '@/lib/utils';

const cartStore = useCartStore();
const { isLoading } = storeToRefs(cartStore);

const props = defineProps<{
    cartItem: CartItemType;
    updateNoteItemId: number | null;
    loadingRemoveItemId: number | null;
    type?: ItemTypeEnum;
}>();

const emit = defineEmits<{
    'update-quantity': [cartItemId: number, action: 'increase' | 'decrease'];
    'update-notes': [cartItemId: number, notes: string];
    remove: [cartItemId: number];
}>();

const { t } = useI18n();
const spicyLevelLabels = computed(() => getSpicyLevelLabels(t));

const categoryLabel = computed(() => {
    return props.type === ItemTypeEnum.DISH
        ? t(`cart.dishCategories.${props.cartItem.item.category.key}`)
        : t(`cart.drinkCategories.${props.cartItem.item.category.key}`);
});

const isEditingNotes = ref<boolean>(false);
const editedNotes = ref<string>(props.cartItem.notes || '');

const updateQuantity = (action: 'increase' | 'decrease') => {
    emit('update-quantity', props.cartItem.id, action);
};

const saveNotes = () => {
    emit('update-notes', props.cartItem.id, editedNotes.value);
    isEditingNotes.value = false;
};

const cancelEditNotes = () => {
    editedNotes.value = props.cartItem.notes || '';
    isEditingNotes.value = false;
};

const startEditNotes = () => {
    editedNotes.value = props.cartItem.notes || '';
    isEditingNotes.value = true;
};

watch(isLoading, (newVal) => {
    if (!newVal) {
        isEditingNotes.value = false;
    }
});

const cardBorderClass = computed(() => {
    return props.type === ItemTypeEnum.DRINK
        ? 'hover:border-info/40'
        : 'hover:border-primary/40';
});

const priceColorClass = computed(() => {
    return props.type === ItemTypeEnum.DRINK ? 'text-info' : 'text-primary';
});

const sectionHeaderClass = computed(() => {
    return props.type === ItemTypeEnum.DRINK
        ? 'text-info'
        : 'text-muted-foreground';
});
</script>

<template>
    <div
        :class="[
            'flex flex-col overflow-hidden rounded-md border border-border bg-card text-card-foreground transition hover:shadow-lg sm:h-[210px] sm:flex-row',
            cardBorderClass,
        ]"
    >
        <div class="overflow-hidden sm:w-40 sm:flex-shrink-0">
            <img
                :src="cartItem.item.main_image"
                :alt="cartItem.item.name"
                class="h-32 w-full object-cover transition duration-500 group-hover:scale-105 sm:h-full"
            />
        </div>

        <div class="flex flex-1 flex-col px-3 py-2">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h3 class="text-base font-semibold uppercase">
                        {{ cartItem.item.name }}
                    </h3>
                    <span
                        class="text-xs tracking-widest text-muted-foreground uppercase"
                    >
                        {{ categoryLabel }}
                    </span>
                </div>
                <span
                    :class="[
                        'font-semibold',
                        priceColorClass,
                        'ml-3 shrink-0 text-sm',
                    ]"
                >
                    €{{ formatPrice(cartItem.total_inc_vat) }}
                </span>
            </div>

            <div class="mt-1 min-h-[40px]">
                <template v-if="type === ItemTypeEnum.DISH">
                    <div class="flex items-center gap-1.5 text-xs">
                        <span :class="[sectionHeaderClass]"
                            >🌶️ {{ t('common.spicyLevel.title') }}:</span
                        >
                        <div class="text-xs font-medium text-foreground">
                            <span
                                :class="[
                                    cartItem.spicy_level === 0 &&
                                        'text-success',
                                    cartItem.spicy_level === 1 &&
                                        'text-warning',
                                    cartItem.spicy_level === 2 &&
                                        'text-primary',
                                    cartItem.spicy_level === 3 &&
                                        'text-destructive',
                                ]"
                            >
                                {{ spicyLevelLabels[cartItem.spicy_level] }}
                            </span>
                        </div>
                    </div>

                    <div
                        v-if="cartItem.meat"
                        class="flex items-center gap-1.5 text-xs"
                    >
                        <span :class="[sectionHeaderClass]"
                            >🥩 {{ t('cart.cartItemCard.meat') }}:</span
                        >
                        <span class="font-medium">{{
                            cartItem.meat.name
                        }}</span>
                        <span
                            v-if="cartItem.meat.extra_price > 0"
                            class="text-xs text-primary"
                        >
                            (+€{{ formatPrice(cartItem.meat.extra_price) }})
                        </span>
                    </div>

                    <div
                        v-if="cartItem.removed_ingredients?.length"
                        class="mt-0.5 text-xs"
                    >
                        <span :class="[sectionHeaderClass]"
                            >🚫 {{ t('cart.cartItemCard.removed') }}:</span
                        >
                        <span class="ml-1 text-xs text-muted-foreground">
                            {{
                                cartItem.removed_ingredients
                                    .map((i) => i.name)
                                    .join(', ')
                            }}
                        </span>
                    </div>

                    <div
                        v-if="
                            !cartItem.meat &&
                            !cartItem.removed_ingredients?.length
                        "
                        class="flex items-center gap-1.5 text-xs text-muted-foreground italic"
                    >
                        <span
                            >🍽️
                            {{ t('cart.cartItemCard.noCustomizations') }}</span
                        >
                    </div>
                </template>

                <template v-else>
                    <div class="flex items-center gap-1.5 text-xs">
                        <span class="text-info"
                            >🍹 {{ t('cart.cartItemCard.readyToServe') }}</span
                        >
                    </div>
                </template>
            </div>

            <div class="mt-1">
                <div v-if="!isEditingNotes" class="group relative">
                    <div
                        v-if="cartItem.notes"
                        class="rounded-md bg-muted p-1.5 text-xs text-muted-foreground"
                    >
                        <span class="text-foreground"
                            >📝 {{ t('cart.cartItemCard.notes') }}:</span
                        >
                        <span class="ml-1">{{ cartItem.notes }}</span>
                    </div>
                    <div v-else class="text-xs text-muted-foreground italic">
                        {{ t('cart.cartItemCard.noNotes') }}
                    </div>
                    <button
                        @click="startEditNotes"
                        v-if="updateNoteItemId !== cartItem.id"
                        class="absolute top-1 right-1 cursor-pointer text-primary transition hover:text-primary/80"
                    >
                        <SquarePen class="h-4 w-4" />
                    </button>
                    <span
                        v-else
                        class="absolute top-1 right-1 text-xs text-success"
                    >
                        {{ t('cart.cartItemCard.saved') }}
                    </span>
                </div>

                <div v-else class="space-y-1">
                    <textarea
                        v-model="editedNotes"
                        :placeholder="t('cart.cartItemCard.notesPlaceholder')"
                        rows="1"
                        class="w-full rounded-md border border-input bg-background p-1.5 text-xs text-foreground placeholder:text-muted-foreground focus:border-ring focus:ring-1 focus:ring-ring focus:outline-none"
                        @keydown.ctrl.enter.prevent="saveNotes"
                        @keydown.meta.enter.prevent="saveNotes"
                    ></textarea>
                    <div class="flex gap-1.5">
                        <button
                            @click="saveNotes"
                            :disabled="isLoading"
                            class="flex w-14 cursor-pointer items-center justify-center gap-1 rounded-md bg-primary px-2 py-0.5 text-xs text-primary-foreground transition hover:bg-primary/90 disabled:opacity-50"
                        >
                            <span
                                v-if="isLoading"
                                class="h-3 w-3 animate-spin rounded-full border-2 border-primary-foreground border-t-transparent"
                            ></span>
                            <span v-else>{{
                                t('cart.cartItemCard.save')
                            }}</span>
                        </button>
                        <button
                            @click="cancelEditNotes"
                            class="w-14 cursor-pointer rounded-md border border-input px-2 py-0.5 text-xs text-muted-foreground transition hover:bg-accent hover:text-accent-foreground"
                        >
                            {{ t('cart.cartItemCard.cancel') }}
                        </button>
                        <span
                            class="ml-auto flex items-center text-xs text-muted-foreground"
                        >
                            ⌘/Ctrl + ↵
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-auto pt-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5">
                        <button
                            @click="updateQuantity('decrease')"
                            :disabled="cartItem.quantity <= 1"
                            class="flex h-6 w-6 cursor-pointer items-center justify-center rounded-full border border-input bg-background text-foreground transition-colors hover:bg-accent hover:text-accent-foreground disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            -
                        </button>
                        <span
                            class="min-w-[32px] text-center text-sm font-medium"
                        >
                            {{ cartItem.quantity }}
                        </span>
                        <button
                            @click="updateQuantity('increase')"
                            class="flex h-6 w-6 cursor-pointer items-center justify-center rounded-full border border-input bg-background text-foreground transition-colors hover:bg-accent hover:text-accent-foreground disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            +
                        </button>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="text-xs text-muted-foreground">
                            €{{ formatPrice(cartItem.unit_price) }}/{{
                                t('cart.cartItemCard.each')
                            }}
                        </span>
                        <button
                            @click.prevent="emit('remove', cartItem.id)"
                            class="flex cursor-pointer items-center gap-0.5 text-xs text-destructive transition hover:text-destructive/80"
                        >
                            <span>{{ t('cart.cartItemCard.remove') }}</span>
                            <span
                                v-if="loadingRemoveItemId === cartItem.id"
                                class="h-1 w-1 animate-spin rounded-full border-2 border-destructive border-t-transparent p-1"
                            ></span>
                            <span v-else class="text-base">×</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.flex.flex-col {
    min-height: auto;
}

@media (min-width: 640px) {
    .flex.flex-col {
        min-height: auto;
    }
}
</style>
