<script setup lang="ts">
import { NotebookPen } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    modelValue: string;
    maxLength?: number;
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const { t } = useI18n();

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
            {{ t('cart.orderNotes.title') }}
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
                :placeholder="placeholder || t('cart.orderNotes.placeholder')"
                :maxlength="maxLength || 500"
                class="w-full resize-y rounded-md border border-gray-300 p-2.5 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
            ></textarea>

            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500">
                    {{ t('cart.orderNotes.helper') }}
                </p>

                <p
                    v-if="maxLength"
                    :class="[
                        'text-xs',
                        isNearLimit ? 'text-amber-600' : 'text-gray-400',
                        remainingCharacters === 0 ? 'text-red-600' : '',
                    ]"
                >
                    {{ remainingCharacters }} / {{ maxLength }}
                    {{ t('cart.orderNotes.remainingCharacters') }}
                </p>
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
                <span class="text-xs text-gray-500"
                    >{{ t('cart.orderNotes.quickSuggestions') }}</span
                >
                <button
                    @click="
                        emit(
                            'update:modelValue',
                            t('cart.orderNotes.suggestions.extraSpicy'),
                        )
                    "
                    class="rounded-md bg-gray-100 px-2 py-1 text-xs transition-colors hover:bg-gray-200"
                >
                    🌶️ {{ t('cart.orderNotes.suggestions.extraSpicy') }}
                </button>
                <button
                    @click="
                        emit(
                            'update:modelValue',
                            t('cart.orderNotes.suggestions.allergyAlert'),
                        )
                    "
                    class="rounded-md bg-gray-100 px-2 py-1 text-xs transition-colors hover:bg-gray-200"
                >
                    🥜 {{ t('cart.orderNotes.suggestions.allergyAlert') }}
                </button>
                <button
                    @click="
                        emit(
                            'update:modelValue',
                            t('cart.orderNotes.suggestions.cutleryNeeded'),
                        )
                    "
                    class="rounded-md bg-gray-100 px-2 py-1 text-xs transition-colors hover:bg-gray-200"
                >
                    🍴 {{ t('cart.orderNotes.suggestions.cutleryNeeded') }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
