<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

interface Option {
    value: number;
    label: string;
    color?: string;
}

const props = defineProps<{
    modelValue: number | null;
    options: Option[];
    label?: string;
    error?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number | null): void;
}>();

const selectedValue = ref<number | null>(props.modelValue ?? null);
const selectedColor = ref<string | null>(
    props.options.find((option) => option.value === selectedValue.value)
        ?.color || '',
);
const isDropdownOpen = ref(false);
const dropdownRef = ref<HTMLDivElement | null>(null);

watch(
    () => props.modelValue,
    (newValue) => {
        selectedValue.value = newValue;
    },
);

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

const selectOption = (value: number | null) => {
    selectedValue.value = value;
    selectedColor.value =
        props.options.find((option) => option.value === selectedValue.value)
            ?.color || '';
    emit('update:modelValue', value);
    isDropdownOpen.value = false;
};

const selectedLabel = () => {
    return (
        props.options.find((option) => option.value === selectedValue.value)
            ?.label || ''
    );
};

const handleClickOutside = (event: MouseEvent) => {
    if (
        isDropdownOpen.value &&
        dropdownRef.value &&
        !dropdownRef.value.contains(event.target as Node)
    ) {
        isDropdownOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="relative" ref="dropdownRef">
        <div class="relative">
            <button
                type="button"
                @click="toggleDropdown"
                class="peer block min-h-[42px] w-full appearance-none rounded-lg border bg-transparent px-2.5 pt-4 pb-2.5 text-left text-sm text-gray-900 focus:ring-0 focus:outline-none dark:text-white"
                :class="
                    props.error
                        ? 'border-red-500'
                        : 'border-gray-300 focus:border-blue-600 dark:border-gray-600 dark:focus:border-blue-500'
                "
            >
                <Badge v-if="selectedLabel()" :class="selectedColor">{{
                    selectedLabel()
                }}</Badge>
                <span
                    v-else
                    :class="props.error ? 'text-red-500' : 'text-gray-400'"
                    >{{ label }}...</span
                >
            </button>
            <label
                class="pointer-events-none absolute start-1 top-2 z-10 origin-[0] -translate-y-4 scale-75 transform bg-white px-2 text-sm duration-300 peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:scale-100 peer-focus:top-2 peer-focus:-translate-y-4 peer-focus:scale-75 peer-focus:px-2 rtl:peer-focus:left-auto rtl:peer-focus:translate-x-1/4 dark:bg-[#0a0a0a]"
                :class="
                    props.error
                        ? 'text-red-500'
                        : 'text-gray-500 peer-focus:text-blue-600 dark:text-gray-400 peer-focus:dark:text-blue-500'
                "
            >
                {{ label }}
            </label>
            <div
                class="pointer-events-none absolute top-1/2 right-2 -translate-y-1/2"
            >
                <svg
                    class="h-4 w-4 text-gray-500"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 9l-7 7-7-7"
                    />
                </svg>
            </div>
        </div>

        <div
            v-if="isDropdownOpen"
            class="minimal-scrollbar absolute z-20 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-gray-300 bg-white shadow-lg dark:border-gray-600 dark:bg-[#0a0a0a]"
        >
            <div
                v-for="option in props.options"
                :key="option.value"
                @click="selectOption(option.value)"
                class="cursor-pointer px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800"
                :class="{
                    'bg-blue-50 font-medium text-blue-600 dark:bg-blue-900/30 dark:text-blue-300':
                        selectedValue === option.value,
                }"
            >
                {{ option.label }}
            </div>
        </div>
        <span v-if="props.error" class="text-sm text-red-500">{{props.error}}</span>
    </div>
</template>

<style scoped>
.minimal-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgb(209 213 219) transparent;
}

.minimal-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.minimal-scrollbar::-webkit-scrollbar-thumb {
    background: rgb(209 213 219);
    border-radius: 2px;
}

.minimal-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgb(156 163 175);
}

.dark .minimal-scrollbar {
    scrollbar-color: rgb(75 85 99) transparent;
}

.dark .minimal-scrollbar::-webkit-scrollbar-thumb {
    background: rgb(75 85 99);
}

.dark .minimal-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgb(107 114 128);
}
</style>
