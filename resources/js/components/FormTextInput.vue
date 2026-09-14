<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{
    modelValue: string | number | null;
    id: string;
    label: string;
    type?: string;
    error?: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number | null): void;
}>();

const value = ref(props.modelValue);

watch(
    () => props.modelValue,
    (newVal) => (value.value = newVal),
);

watch(value, (val) => emit('update:modelValue', val));
</script>

<template>
    <div>
        <div class="relative">
            <input
                :id="id"
                :type="type || 'text'"
                v-model="value"
                :placeholder="placeholder || ''"
                class="peer block w-full appearance-none rounded-lg border-1 bg-transparent px-2.5 pt-4 pb-2.5 text-sm text-gray-900 focus:ring-0 focus:outline-none dark:text-white"
                :class="
                    props.error
                        ? 'border-red-500'
                        : 'border-gray-300 focus:border-blue-600 dark:border-gray-600 dark:focus:border-blue-500'
                "
            />
            <label
                :for="id"
                class="absolute start-1 top-2 z-10 origin-[0] -translate-y-4 scale-75 transform bg-white px-2 text-sm duration-300 peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:scale-100 peer-focus:top-2 peer-focus:-translate-y-4 peer-focus:scale-75 peer-focus:px-2 rtl:peer-focus:left-auto rtl:peer-focus:translate-x-1/4 dark:bg-[#0a0a0a]"
                :class="
                    props.error
                        ? 'text-red-500'
                        : 'text-gray-500 peer-focus:text-blue-600 dark:text-gray-400 peer-focus:dark:text-blue-500'
                "
            >
                {{ label }}
            </label>
        </div>
        <span v-if="props.error" class="text-sm text-red-500">{{
            props.error
        }}</span>
    </div>
</template>
