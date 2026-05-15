<script setup lang="ts">
import { Table } from 'lucide-vue-next';

const props = defineProps<{
    modelValue: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <div class="rounded-lg border bg-white p-6">
        <h2
            class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
        >
            <Table class="h-5 w-5" />
            Table Details
        </h2>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Table Number *
            </label>
            <input
                :value="props.modelValue"
                @input="
                    emit(
                        'update:modelValue',
                        ($event.target as HTMLInputElement).value,
                    )
                "
                type="text"
                placeholder="e.g., 12, A3, 5B"
                class="w-full rounded-md border border-gray-300 p-2.5 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
            />
            <p class="mt-1 text-xs text-gray-500">
                Enter your table number for dine-in service
            </p>
        </div>
    </div>
</template>

<style scoped></style>
