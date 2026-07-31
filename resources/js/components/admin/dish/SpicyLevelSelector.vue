<script setup lang="ts">
import { Flame } from 'lucide-vue-next';
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
        bg: 'bg-green-400',
        text: 'text-green-500',
        border: 'border-green-500',
    },
    {
        value: 1,
        label: t('dish.spicyLevel.mild'),
        bg: 'bg-yellow-400',
        text: 'text-yellow-500',
        border: 'border-yellow-500',
    },
    {
        value: 2,
        label: t('dish.spicyLevel.spicy'),
        bg: 'bg-orange-400',
        text: 'text-orange-500',
        border: 'border-orange-500',
    },
    {
        value: 3,
        label: t('dish.spicyLevel.hot'),
        bg: 'bg-red-400',
        text: 'text-red-500',
        border: 'border-red-500',
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
                        ? `${level.bg} ${level.border} text-white shadow-md`
                        : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600',
                    disabled && 'cursor-not-allowed opacity-60',
                ]"
            >
                <span
                    class="text-xs font-medium"
                    :class="
                        selectedLevel === level.value
                            ? 'text-white'
                            : 'text-muted-foreground'
                    "
                >
                    {{ level.label }}
                </span>
            </button>
        </div>

        <p class="text-sm text-red-500">
            {{ error }}
        </p>
    </div>
</template>
