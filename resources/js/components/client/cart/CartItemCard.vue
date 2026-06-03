<script setup lang="ts">
import { useCartStore } from '@/stores/cart';
import { CartItemType, ItemTypeEnum } from '@/types/cart';
import { SquarePen } from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { computed, ref, watch } from 'vue';

const cartStore = useCartStore();
const { isLoading } = storeToRefs(cartStore);

const props = defineProps<{
    cartItem: CartItemType;
    updateNoteItemId: number | null;
    loadingRemoveItemId: number | null;
    type?: ItemTypeEnum;
}>();

const emit = defineEmits(['update-quantity', 'update-notes', 'remove']);

const isEditingNotes = ref(false);
const editedNotes = ref(props.cartItem.notes || '');

const formatPrice = (price: number) => {
    return (price || 0).toFixed(2);
};

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
        ? 'hover:border-blue-200'
        : 'hover:border-red-200';
});

const priceColorClass = computed(() => {
    return props.type === ItemTypeEnum.DRINK ? 'text-blue-600' : 'text-red-500';
});

const sectionHeaderClass = computed(() => {
    return props.type === ItemTypeEnum.DRINK
        ? 'text-blue-500'
        : 'text-gray-500';
});
</script>

<template>
    <div
        :class="[
            'flex flex-col overflow-hidden rounded-md border bg-white transition hover:shadow-lg sm:h-[210px] sm:flex-row',
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
                        :class="[
                            'text-xs tracking-widest uppercase',
                            type === ItemTypeEnum.DRINK
                                ? 'text-blue-500'
                                : 'text-gray-400',
                        ]"
                    >
                        {{ cartItem.item.category.label }}
                    </span>
                </div>
                <span
                    :class="[
                        'font-semibold',
                        priceColorClass,
                        'ml-3 shrink-0 text-sm',
                    ]"
                >
                    €{{ formatPrice(cartItem.total) }}
                </span>
            </div>

            <div class="mt-1 min-h-[40px]">
                <template v-if="type === ItemTypeEnum.DISH">
                    <div
                        v-if="cartItem.meat"
                        class="flex items-center gap-1.5 text-xs"
                    >
                        <span :class="[sectionHeaderClass]">🥩 Meat:</span>
                        <span class="font-medium">{{
                            cartItem.meat.name
                        }}</span>
                        <span
                            v-if="cartItem.meat.extra_price > 0"
                            class="text-xs text-red-500"
                        >
                            (+€{{ formatPrice(cartItem.meat.extra_price) }})
                        </span>
                    </div>

                    <div
                        v-if="cartItem.removed_ingredients?.length"
                        class="mt-0.5 text-xs"
                    >
                        <span :class="[sectionHeaderClass]">🚫 Removed:</span>
                        <span class="ml-1 text-xs text-gray-600">
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
                        class="flex items-center gap-1.5 text-xs text-gray-400 italic"
                    >
                        <span>🍽️ No customizations</span>
                    </div>
                </template>

                <template v-else>
                    <div class="flex items-center gap-1.5 text-xs">
                        <span class="text-blue-500">🍹 Ready to serve</span>
                    </div>
                </template>
            </div>

            <div class="mt-1">
                <div v-if="!isEditingNotes" class="group relative">
                    <div
                        v-if="cartItem.notes"
                        class="rounded-md bg-gray-50 p-1.5 text-xs text-gray-600"
                    >
                        <span class="text-gray-500">📝 Notes:</span>
                        <span class="ml-1">{{ cartItem.notes }}</span>
                    </div>
                    <div v-else class="text-xs text-gray-400 italic">
                        No notes added
                    </div>
                    <button
                        @click="startEditNotes"
                        v-if="updateNoteItemId !== cartItem.id"
                        class="absolute top-1 right-1 cursor-pointer text-blue-500 transition hover:text-blue-600"
                    >
                        <SquarePen class="h-4 w-4" />
                    </button>
                    <span
                        v-else
                        class="absolute top-1 right-1 cursor-pointer text-xs text-green-500 transition"
                    >
                        Saved!
                    </span>
                </div>

                <div v-else class="space-y-1">
                    <textarea
                        v-model="editedNotes"
                        placeholder="Add special instructions..."
                        rows="1"
                        class="w-full rounded-md border border-gray-300 p-1.5 text-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                        @keydown.ctrl.enter.prevent="saveNotes"
                        @keydown.meta.enter.prevent="saveNotes"
                    ></textarea>
                    <div class="flex gap-1.5">
                        <button
                            @click="saveNotes"
                            :disabled="isLoading"
                            class="flex w-14 cursor-pointer items-center justify-center gap-1 rounded-md bg-blue-500 px-2 py-0.5 text-xs text-white transition hover:bg-blue-600 disabled:opacity-50"
                        >
                            <span
                                v-if="isLoading"
                                class="h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent"
                            ></span>
                            <span v-else>Save</span>
                        </button>
                        <button
                            @click="cancelEditNotes"
                            class="w-14 cursor-pointer rounded-md border border-gray-300 px-2 py-0.5 text-xs text-gray-600 transition hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <span
                            class="ml-auto flex items-center text-xs text-gray-400"
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
                            class="flex h-6 w-6 cursor-pointer items-center justify-center rounded-full border text-gray-600 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50"
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
                            class="flex h-6 w-6 cursor-pointer items-center justify-center rounded-full border text-gray-600 transition hover:bg-gray-100"
                        >
                            +
                        </button>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500">
                            €{{ formatPrice(cartItem.unit_price) }}/each
                        </span>
                        <button
                            @click.prevent="$emit('remove', cartItem.id)"
                            class="flex cursor-pointer items-center gap-0.5 text-xs text-red-500 transition hover:text-red-600"
                        >
                            <span>Remove</span>
                            <span
                                v-if="loadingRemoveItemId === cartItem.id"
                                class="h-1 w-1 animate-spin rounded-full border-2 border-red-500 border-t-transparent p-1"
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
