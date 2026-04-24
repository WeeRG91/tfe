<script setup lang="ts">
import { CartItemType } from '@/types/cart';
import { ref } from 'vue';
import { SquarePen } from 'lucide-vue-next';

const props = defineProps<{
    cartItem: CartItemType;
    updateNoteItemId: number | null;
}>();

const emit = defineEmits(['update-quantity', 'update-notes', 'remove']);

const isEditingNotes = ref(false);
const editedNotes = ref(props.cartItem.notes || '');

const formatPrice = (price: number) => {
    return (price || 0).toFixed(2);
};

const incrementQuantity = () => {
    const newQuantity = props.cartItem.quantity + 1;
    emit('update-quantity', props.cartItem.id, newQuantity);
};

const decrementQuantity = () => {
    if (props.cartItem.quantity > 1) {
        const newQuantity = props.cartItem.quantity - 1;
        emit('update-quantity', props.cartItem.id, newQuantity);
    }
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
</script>

<template>
    <div
        class="flex flex-col overflow-hidden rounded-md border bg-white transition hover:shadow-lg sm:flex-row"
    >
        <div class="overflow-hidden sm:w-40">
            <img
                :src="cartItem.item.main_image"
                :alt="cartItem.item.name"
                class="h-40 w-full object-cover transition duration-500 group-hover:scale-105 sm:h-full"
            />
        </div>

        <div class="flex flex-1 flex-col px-4 py-3">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold uppercase">
                        {{ cartItem.item.name }}
                    </h3>
                    <span
                        class="text-xs tracking-widest text-gray-400 uppercase"
                    >
                        {{ cartItem.item.category.label }}
                    </span>
                </div>
                <span class="font-semibold text-red-500">
                    €{{ formatPrice(cartItem.total_price) }}
                </span>
            </div>

            <div
                v-if="cartItem.meat"
                class="mt-2 flex items-center gap-2 text-sm"
            >
                <span class="text-gray-500">🥩 Meat:</span>
                <span class="font-medium">{{ cartItem.meat.name }}</span>
                <span
                    v-if="cartItem.meat.extra_price > 0"
                    class="text-xs text-red-500"
                >
                    (+€{{ formatPrice(cartItem.meat.extra_price) }})
                </span>
            </div>

            <div
                v-if="cartItem.removed_ingredients?.length"
                class="mt-1 text-sm"
            >
                <span class="text-gray-500">🚫 Removed:</span>
                <span class="ml-1 text-gray-600">
                    {{
                        cartItem.removed_ingredients
                            .map((i) => i.name)
                            .join(', ')
                    }}
                </span>
            </div>

            <div class="mt-2">
                <div v-if="!isEditingNotes" class="group relative">
                    <div
                        v-if="cartItem.notes"
                        class="rounded-md bg-gray-50 p-2 text-sm text-gray-600"
                    >
                        <span class="text-gray-500">📝 Notes:</span>
                        <span class="ml-1">{{ cartItem.notes }}</span>
                    </div>
                    <div v-else class="text-sm text-gray-400 italic">
                        No notes added
                    </div>
                    <button
                        @click="startEditNotes"
                        v-if="updateNoteItemId !== cartItem.id"
                        class="absolute top-2 right-2 cursor-pointer text-xs text-blue-500 transition hover:text-blue-600"
                    >
                        <SquarePen class="h-4 w-4" />
                    </button>
                    <span
                        v-else
                        class="absolute top-2 right-2 cursor-pointer text-xs text-blue-500 transition hover:text-blue-600"
                    >
                        Saved!
                    </span>
                </div>

                <div v-else class="space-y-2">
                    <textarea
                        v-model="editedNotes"
                        placeholder="Add special instructions, preferences, or notes about this item..."
                        rows="2"
                        class="w-full rounded-md border border-gray-300 p-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                        @keyup.ctrl.enter="saveNotes"
                        @keyup.meta.enter="saveNotes"
                    ></textarea>
                    <div class="flex gap-2">
                        <button
                            @click="saveNotes"
                            class="cursor-pointer rounded-md bg-blue-500 px-3 py-1 text-sm text-white transition hover:bg-blue-600"
                        >
                            Save
                        </button>
                        <button
                            @click="cancelEditNotes"
                            class="cursor-pointer rounded-md border border-gray-300 px-3 py-1 text-sm text-gray-600 transition hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <span
                            class="ml-auto flex items-center text-xs text-gray-400"
                        >
                            ⌘/Ctrl + Enter to save
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-auto">
                <div
                    class="mt-3 flex flex-wrap items-center justify-between gap-3"
                >
                    <div class="flex items-center gap-2">
                        <button
                            @click="decrementQuantity"
                            :disabled="cartItem.quantity <= 1"
                            class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full border text-gray-600 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            -
                        </button>
                        <span class="min-w-[40px] text-center font-medium">
                            {{ cartItem.quantity }}
                        </span>
                        <button
                            @click="incrementQuantity"
                            class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full border text-gray-600 transition hover:bg-gray-100"
                        >
                            +
                        </button>
                    </div>

                    <div class="flex items-center gap-4">
                        <span class="text-xs text-gray-500">
                            Unit: €{{ formatPrice(cartItem.unit_price) }}
                        </span>
                        <button
                            @click.prevent="$emit('remove', cartItem.id)"
                            class="flex cursor-pointer items-center gap-1 text-sm text-red-500 transition hover:text-red-600"
                        >
                            <span>Remove</span>
                            <span class="text-lg">×</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
