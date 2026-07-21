<script setup lang="ts">
import { Flame } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    modelValue: number;
    label?: string;
    error?: string;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number): void;
}>();

const levels = [
    {
        value: 0,
        label: 'Not spicy',
        bg: 'bg-green-400',
        text: 'text-green-500',
        border: 'border-green-500',
    },
    {
        value: 1,
        label: 'Mild',
        bg: 'bg-yellow-400',
        text: 'text-yellow-500',
        border: 'border-yellow-500',
    },
    {
        value: 2,
        label: 'Spicy',
        bg: 'bg-orange-400',
        text: 'text-orange-500',
        border: 'border-orange-500',
    },
    {
        value: 3,
        label: 'Hot',
        bg: 'bg-red-400',
        text: 'text-red-500',
        border: 'border-red-500',
    },
];

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
                <div class="flex items-center gap-1">
                    <Flame
                        v-for="i in level.value"
                        :key="i"
                        class="h-3.5 w-3.5 transition-colors duration-200 sm:h-4 sm:w-4"
                        :class="[
                            selectedLevel === level.value
                                ? 'text-white'
                                : level.text,
                        ]"
                        :fill="
                            selectedLevel === level.value
                                ? 'white'
                                : 'currentColor'
                        "
                    />
                </div>
                <span
                    class="text-[11px] font-medium sm:text-xs"
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
