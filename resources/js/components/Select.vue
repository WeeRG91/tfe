<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { getCategoryBadgeColor } from '@/lib/utils';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

interface Option {
    value: number;
    key?: string;
    translationKey?: string;
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

const { t } = useI18n();

const selectedValue = ref<number | null>(props.modelValue ?? null);
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
    emit('update:modelValue', value);
    isDropdownOpen.value = false;
};

const selectedLabel = () => {
    const option = props.options.find(
        (option) => option.value === selectedValue.value,
    );

    return option?.translationKey
        ? t(option.translationKey)
        : option?.label || '';
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
                class="peer block min-h-[42px] w-full appearance-none rounded-lg border bg-transparent px-2.5 pt-4 pb-2.5 text-left text-sm text-foreground focus:ring-0 focus:outline-none"
                :class="
                    props.error
                        ? 'border-destructive'
                        : 'border-border focus:border-ring'
                "
            >
                <Badge
                    v-if="selectedLabel()"
                    :class="getCategoryBadgeColor(selectedValue ?? 1)"
                    >{{ selectedLabel() }}</Badge
                >
                <span
                    v-else
                    :class="
                        props.error
                            ? 'text-destructive'
                            : 'text-muted-foreground'
                    "
                    >{{ label }}...</span
                >
            </button>
            <label
                class="pointer-events-none absolute start-1 top-2 z-10 origin-[0] -translate-y-4 scale-75 transform bg-background px-2 text-sm duration-300 peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:scale-100 peer-focus:top-2 peer-focus:-translate-y-4 peer-focus:scale-75 peer-focus:px-2 rtl:peer-focus:left-auto rtl:peer-focus:translate-x-1/4"
                :class="
                    props.error
                        ? 'text-destructive'
                        : 'text-muted-foreground peer-focus:text-foreground'
                "
            >
                {{ label }}
            </label>
            <div
                class="pointer-events-none absolute top-1/2 right-2 -translate-y-1/2"
            >
                <svg
                    class="h-4 w-4 text-muted-foreground"
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
            class="minimal-scrollbar absolute z-20 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-border bg-popover text-popover-foreground shadow-lg"
        >
            <div
                v-for="option in props.options"
                :key="option.value"
                @click="selectOption(option.value)"
                class="cursor-pointer px-3 py-2 text-sm hover:bg-accent hover:text-accent-foreground"
                :class="{
                    'bg-accent font-medium text-accent-foreground':
                        selectedValue === option.value,
                }"
            >
                {{
                    option.translationKey
                        ? t(option.translationKey)
                        : option.label
                }}
            </div>
        </div>
        <span v-if="props.error" class="text-sm text-destructive">{{
            props.error
        }}</span>
    </div>
</template>

<style scoped>
.minimal-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: var(--muted-foreground) transparent;
}

.minimal-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.minimal-scrollbar::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 2px;
}

.minimal-scrollbar::-webkit-scrollbar-thumb:hover {
    background: var(--muted-foreground);
}
</style>
