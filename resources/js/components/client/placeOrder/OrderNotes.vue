<script setup lang="ts">
import { NotebookPen } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    modelValue: string;
    maxLength?: number;
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const characterCount = computed(() => props.modelValue.length);
const remainingCharacters = computed(
    () => (props.maxLength || 500) - characterCount.value,
);
const isNearLimit = computed(() => remainingCharacters.value < 50);
</script>

<template>
    <div class="rounded-lg border bg-white p-6">
        <h2
            class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
        >
            <NotebookPen class="h-5 w-5" />
            Order Notes (Optional)
        </h2>

        <div class="space-y-2">
            <textarea
                :value="modelValue"
                @input="
                    emit(
                        'update:modelValue',
                        ($event.target as HTMLTextAreaElement).value,
                    )
                "
                rows="3"
                :placeholder="
                    placeholder ||
                    'Any special requests or dietary requirements?'
                "
                :maxlength="maxLength || 500"
                class="w-full resize-y rounded-md border border-gray-300 p-2.5 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
            ></textarea>

            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500">
                    We'll do our best to accommodate your requests
                </p>

                <!-- Character counter (if maxLength is provided) -->
                <p
                    v-if="maxLength"
                    :class="[
                        'text-xs',
                        isNearLimit ? 'text-amber-600' : 'text-gray-400',
                        remainingCharacters === 0 ? 'text-red-600' : '',
                    ]"
                >
                    {{ remainingCharacters }} / {{ maxLength }} characters
                    remaining
                </p>
            </div>

            <!-- Quick note suggestions -->
            <div class="mt-3 flex flex-wrap gap-2">
                <span class="text-xs text-gray-500">Quick suggestions:</span>
                <button
                    @click="emit('update:modelValue', 'No onions please')"
                    class="rounded-md bg-gray-100 px-2 py-1 text-xs transition-colors hover:bg-gray-200"
                >
                    🧅 No onions
                </button>
                <button
                    @click="emit('update:modelValue', 'Extra spicy')"
                    class="rounded-md bg-gray-100 px-2 py-1 text-xs transition-colors hover:bg-gray-200"
                >
                    🌶️ Extra spicy
                </button>
                <button
                    @click="emit('update:modelValue', 'Allergies: nuts')"
                    class="rounded-md bg-gray-100 px-2 py-1 text-xs transition-colors hover:bg-gray-200"
                >
                    🥜 Allergy alert
                </button>
                <button
                    @click="emit('update:modelValue', 'Cutlery needed')"
                    class="rounded-md bg-gray-100 px-2 py-1 text-xs transition-colors hover:bg-gray-200"
                >
                    🍴 Cutlery needed
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
