<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    modelValue: number;
    label?: string;
    error?: string;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number): void;
}>();

const { t } = useI18n();

const levels = computed(() => [
    {
        value: 0,
        label: t('dish.spicyLevel.noSpicy'),
        selectedClass: 'border-success bg-success text-success-foreground',
    },
    {
        value: 1,
        label: t('dish.spicyLevel.mild'),
        selectedClass: 'border-warning bg-warning text-warning-foreground',
    },
    {
        value: 2,
        label: t('dish.spicyLevel.spicy'),
        selectedClass: 'border-primary bg-primary text-primary-foreground',
    },
    {
        value: 3,
        label: t('dish.spicyLevel.hot'),
        selectedClass: 'border-destructive bg-destructive text-destructive-foreground',
    },
]);

const selectedLevel = computed({
    get: () => props.modelValue,
    set: (value: number) => emit('update:modelValue', value),
});
</script>

<template>
    <div class="space-y-2">
        <label v-if="label" class="text-xs text-muted-foreground">
            {{ label }}
        </label>

        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:gap-2">
            <button
                v-for="level in levels"
                :key="level.value"
                type="button"
                :disabled="disabled"
                @click="selectedLevel = level.value"
                class="group relative flex items-center justify-center gap-1.5 rounded-lg border-2 px-2 py-2.5 transition-all duration-200 active:scale-95 sm:min-w-[80px] sm:flex-1 sm:px-3"
                :class="[
                    selectedLevel === level.value
                        ? `${level.selectedClass} shadow-md`
                        : 'border-border bg-card text-card-foreground hover:border-primary/50 hover:bg-accent',
                    disabled && 'cursor-not-allowed opacity-60',
                ]"
            >
                <span class="text-xs font-medium">
                    {{ level.label }}
                </span>
            </button>
        </div>

        <p class="text-sm text-destructive">
            {{ error }}
        </p>
    </div>
</template>
