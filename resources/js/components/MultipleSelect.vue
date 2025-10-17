<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

interface Option {
    value: number;
    label: string;
}

const props = defineProps<{
    modelValue: number[];
    options: Option[];
    label?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number[]): void;
}>();

const selectedValues = ref<number[]>([...props.modelValue]);
const isDropdownOpen = ref(false);
const dropdownRef = ref<HTMLDivElement | null>(null);

watch(
    () => props.modelValue,
    (newValue) => {
        selectedValues.value = newValue;
    },
);

const toggleOptions = (value: number) => {
    const index = selectedValues.value.indexOf(value);
    if (index > -1) {
        selectedValues.value.splice(index, 1);
    } else {
        selectedValues.value.push(value);
    }
    emit('update:modelValue', selectedValues.value);
};

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

const getSelectedLabels = () => {
    return props.options
        .filter((option) => selectedValues.value.includes(option.value))
        .map((option) => option.label);
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
                class="peer block min-h-[42px] w-full appearance-none rounded-lg border-1 border-gray-300 bg-transparent px-2.5 pt-4 pb-2.5 text-left text-sm text-gray-900 focus:border-blue-600 focus:ring-0 focus:outline-none dark:border-gray-600 dark:text-white dark:focus:border-blue-500"
            >
                <div class="flex flex-wrap gap-1">
                    <span
                        v-for="label in getSelectedLabels()"
                        :key="label"
                        class="inline-flex items-center rounded-md bg-blue-100 px-2 py-1 text-xs text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                    >
                        {{ label }}
                        <button
                            type="button"
                            @click.stop="
                                toggleOptions(
                                    props.options.find(
                                        (opt) => opt.label === label,
                                    )!.value,
                                )
                            "
                            class="ml-1 hover:text-blue-600"
                        >
                            ×
                        </button>
                    </span>
                    <span
                        v-if="selectedValues.length === 0"
                        class="text-gray-400"
                    >
                        Select {{ label }}...
                    </span>
                </div>
            </button>
            <label
                class="pointer-events-none absolute start-1 top-2 z-10 origin-[0] -translate-y-4 scale-75 transform bg-white px-2 text-sm text-gray-500 duration-300 peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:scale-100 peer-focus:top-2 peer-focus:-translate-y-4 peer-focus:scale-75 peer-focus:px-2 peer-focus:text-blue-600 rtl:peer-focus:left-auto rtl:peer-focus:translate-x-1/4 dark:bg-[#0a0a0a] dark:text-gray-400 peer-focus:dark:text-blue-500"
                >{{ label }}</label
            >
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

        <!-- Dropdown Menu -->
        <div
            v-if="isDropdownOpen"
            class="minimal-scrollbar absolute z-20 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-gray-300 bg-white shadow-lg dark:border-gray-600 dark:bg-[#0a0a0a]"
        >
            <div
                v-for="option in props.options"
                :key="option.value"
                @click="toggleOptions(option.value)"
                class="flex cursor-pointer items-center px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800"
                :class="{
                    'bg-blue-50 dark:bg-blue-900/30': selectedValues.includes(
                        option.value,
                    ),
                }"
            >
                <div class="flex h-5 items-center">
                    <input
                        type="checkbox"
                        :checked="selectedValues.includes(option.value)"
                        class="h-4 w-4 rounded border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600"
                        readonly
                    />
                </div>
                <label
                    class="ms-2 w-full cursor-pointer py-1 text-sm font-medium text-gray-900 dark:text-gray-300"
                >
                    {{ option.label }}
                </label>
            </div>
        </div>
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

.minimal-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.minimal-scrollbar::-webkit-scrollbar-thumb {
    background: rgb(209 213 219);
    border-radius: 2px;
}

.minimal-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgb(156 163 175);
}

/* Dark mode */
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
