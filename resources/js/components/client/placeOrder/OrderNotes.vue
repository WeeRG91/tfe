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
    <div
        class="rounded-lg border border-border bg-card p-6 text-card-foreground"
    >
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
                class="w-full resize-y rounded-md border border-input bg-background p-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:ring-1 focus:ring-ring focus:outline-none"
            ></textarea>

            <div class="flex items-center justify-between">
                <p class="text-xs text-muted-foreground">
                    {{ t('cart.orderNotes.helper') }}
                </p>

                <p
                    v-if="maxLength"
                    :class="[
                        'text-xs',
                        isNearLimit ? 'text-warning' : 'text-muted-foreground',
                        remainingCharacters === 0 ? 'text-destructive' : '',
                    ]"
                >
                    {{ remainingCharacters }} / {{ maxLength }}
                    {{ t('cart.orderNotes.remainingCharacters') }}
                </p>
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
                <span class="text-xs text-muted-foreground">{{
                    t('cart.orderNotes.quickSuggestions')
                }}</span>
                <button
                    @click="
                        emit(
                            'update:modelValue',
                            t('cart.orderNotes.suggestions.extraSpicy'),
                        )
                    "
                    class="rounded-md bg-muted px-2 py-1 text-xs text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
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
                    class="rounded-md bg-muted px-2 py-1 text-xs text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
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
                    class="rounded-md bg-muted px-2 py-1 text-xs text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                >
                    🍴 {{ t('cart.orderNotes.suggestions.cutleryNeeded') }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
