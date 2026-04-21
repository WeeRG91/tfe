<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import menu from '@/routes/menu';
import { GlobalSearchType, SearchResultType, SearchType } from '@/types';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { CornerDownLeft, Loader, Search, Sparkles, X } from 'lucide-vue-next';
import {
    ComponentPublicInstance,
    computed,
    nextTick,
    onMounted,
    onUnmounted,
    ref,
    watch,
} from 'vue';
import { useDebounceFn } from '@vueuse/core';

const props = defineProps<{
    open: boolean;
    onClose: () => void;
}>();

const resultTypes: SearchResultType[] = [
    { key: 'dish', label: 'Dishes' },
    { key: 'drink', label: 'Drinks' },
];

const query = ref<string>('');
const results = ref<GlobalSearchType | null>(null);
const isLoading = ref<boolean>(false);
const inputRef = ref<HTMLInputElement | null>(null);
const selectedIndex = ref<number>(-1);
const itemRefs = ref<HTMLElement[]>([]);

const hasNoResults = computed(() => {
    if (!results.value) return false;

    return !Object.values(results.value).some((items) => items.length > 0);
});

const flatResults = computed(() => {
    if (!results.value) return [];

    return Object.entries(results.value).flatMap(([type, items]) =>
        items.map((item) => ({
            ...item,
            type,
        })),
    );
});

const filteredResultTypes = computed(() => {
    return resultTypes.filter((type) => results.value?.[type.key]?.length);
});

const totalResultsCount = computed(() => {
    if (!results.value) return 0;

    return Object.values(results.value).reduce(
        (acc, items) => acc + items.length,
        0,
    );
});

const indexMap = computed(() => {
    const map = new Map<string, number>();

    flatResults.value.forEach((item, i) => {
        map.set(`${item.type}-${item.id}`, i);
    });

    return map;
});

const search = useDebounceFn(async (value: string) => {
    if (!value) {
        results.value = null;
        return;
    }

    isLoading.value = true;

    try {
        const response = await axios.get('/client-global-search', {
            params: { query: value },
        });

        results.value = response.data.results;
    } finally {
        isLoading.value = false;
    }
}, 300);

const handleKeydown = (e: KeyboardEvent) => {
    if (!props.open) return;

    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault();
            if (selectedIndex.value < flatResults.value.length - 1) {
                selectedIndex.value++;
            }
            break;

        case 'ArrowUp':
            e.preventDefault();
            if (selectedIndex.value > 0) {
                selectedIndex.value--;
            }
            break;

        case 'Enter':
            e.preventDefault();
            const item = flatResults.value[selectedIndex.value];
            if (item) {
                goToEdit(item.type, item.id);
            }
            break;

        case 'Escape':
            e.preventDefault();
            props.onClose();
            break;
    }
};

const goToEdit = (type: string, id: number) => {
    switch (type) {
        case 'dish':
            router.visit(menu.showDish(id).url);
            break;
        case 'drink':
            router.visit(menu.showDish(id).url);
            break;
        default:
            break;
    }
};

const isSelected = (item: SearchType) => {
    const current = flatResults.value[selectedIndex.value];
    return current?.id === item.id && current?.type === item.type;
};

const setItemRef = (
    el: Element | ComponentPublicInstance | null,
    index: number,
) => {
    if (el instanceof HTMLElement) {
        itemRefs.value[index] = el;
    }
};

const clearSearch = () => {
    query.value = '';
    results.value = null;
    inputRef.value?.focus();
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

watch(query, search);

watch(
    () => props.open,
    async (isOpen) => {
        if (isOpen) {
            selectedIndex.value = -1;
            await nextTick();
            inputRef.value?.focus();
        }
    },
);

watch(selectedIndex, (index) => {
    nextTick(() => {
        itemRefs.value[index]?.scrollIntoView({
            block: 'nearest',
        });
    });
});

watch(flatResults, () => {
    itemRefs.value = [];
});
</script>

<template>
    <Dialog :open="open" @update:open="props.onClose">
        <DialogContent
            class="gap-0 overflow-hidden p-0 sm:max-w-2xl"
            @pointer-down-outside="props.onClose"
        >
            <DialogHeader class="border-b border-gray-100 px-6 py-5">
                <DialogTitle
                    class="flex items-center gap-2 bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-xl font-semibold text-transparent"
                >
                    <Sparkles class="h-5 w-5 text-red-600" />
                    Find your favorites
                </DialogTitle>
                <DialogDescription class="text-sm text-gray-500">
                    Search through dishes and drinks
                </DialogDescription>
            </DialogHeader>

            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-5">
                <div class="relative">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                    />
                    <Input
                        ref="inputRef"
                        v-model="query"
                        placeholder="Search dishes, drinks..."
                        class="h-12 rounded-xl border-gray-200 pr-10 pl-10 transition-all duration-200 focus:ring-0"
                    />
                    <button
                        v-if="query"
                        @click="clearSearch"
                        class="absolute top-1/2 right-3 -translate-y-1/2 rounded-full p-0.5 transition-colors hover:bg-gray-100"
                    >
                        <X class="h-4 w-4 text-gray-400" />
                    </button>
                </div>
            </div>

            <div class="scrollable max-h-[460px] overflow-y-auto bg-white">
                <div
                    v-if="isLoading"
                    class="flex flex-col items-center justify-center gap-3 py-16"
                >
                    <Loader class="h-8 w-8 animate-spin text-blue-500" />
                    <p class="text-sm text-gray-500">Searching...</p>
                </div>

                <div
                    v-else-if="!query && !isLoading"
                    class="flex flex-col items-center justify-center gap-3 py-16"
                >
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100"
                    >
                        <Search class="h-8 w-8 text-gray-400" />
                    </div>
                    <p class="text-sm text-gray-500">
                        Start typing to search for dishes and drinks
                    </p>
                </div>

                <div
                    v-else-if="hasNoResults && !isLoading"
                    class="flex flex-col items-center justify-center gap-3 py-16"
                >
                    <p class="text-base font-medium text-gray-700">
                        No results found
                    </p>
                    <p class="text-sm text-gray-500">
                        Try searching with different keywords
                    </p>
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <div
                        v-if="results !== null && !isLoading"
                        class="bg-gray-50/50 px-6 py-3"
                    >
                        <p class="text-xs text-gray-500">
                            Found {{ totalResultsCount }} result{{
                                totalResultsCount !== 1 ? 's' : ''
                            }}
                        </p>
                    </div>

                    <div
                        v-for="type in filteredResultTypes"
                        :key="type.key"
                        class="py-2"
                    >
                        <div class="px-6 py-3">
                            <h3
                                class="flex items-center gap-2 text-xs font-semibold tracking-wider text-gray-500 uppercase"
                            >
                                <span>{{
                                    type.key === 'dish' ? '🍽️' : '🍹'
                                }}</span>
                                <span>{{ type.label }}</span>
                                <span class="ml-auto text-xs text-gray-400">{{
                                    results![type.key].length
                                }}</span>
                            </h3>
                        </div>

                        <div class="space-y-1 px-3">
                            <div
                                :ref="
                                    (el) => {
                                        const key = `${item.type}-${item.id}`;
                                        const i = indexMap.get(key);
                                        if (i !== undefined) {
                                            setItemRef(el, i);
                                        }
                                    }
                                "
                                v-for="(item, index) in results![type.key]"
                                :key="item.id"
                                @click="goToEdit(item.type, item.id)"
                                :class="[
                                    'group relative flex cursor-pointer items-center justify-between gap-3 rounded-lg p-3 transition-all duration-150',
                                    isSelected(item)
                                        ? 'scale-[1.02] bg-gradient-to-r from-gray-50 to-gray-100'
                                        : 'hover:scale-[1.02] hover:bg-gradient-to-r hover:from-gray-50 hover:to-gray-100',
                                ]"
                                :style="{ animationDelay: `${index * 30}ms` }"
                            >
                                <div
                                    class="flex min-w-0 flex-1 items-center gap-4"
                                >
                                    <div class="relative">
                                        <img
                                            v-if="item.main_image"
                                            :src="item.main_image"
                                            :alt="item.name"
                                            class="h-12 w-12 rounded-lg object-cover shadow-sm"
                                        />
                                        <div
                                            v-else
                                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-gray-100 to-gray-200"
                                        >
                                            <span class="text-xl">{{
                                                type.key === 'dish'
                                                    ? '🍽️'
                                                    : '🍹'
                                            }}</span>
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p
                                            :class="[
                                                'truncate text-sm font-medium transition-colors',
                                                isSelected(item)
                                                    ? 'text-blue-600'
                                                    : 'text-gray-900 group-hover:text-blue-600',
                                            ]"
                                        >
                                            {{ item.name }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-gray-500">
                                            {{ item.category.label }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    :class="[
                                        'flex items-center gap-2 opacity-0 transition-opacity duration-200',
                                        isSelected(item)
                                            ? 'opacity-100'
                                            : 'group-hover:opacity-100',
                                    ]"
                                >
                                    <span class="text-xs text-gray-400"
                                        >Enter</span
                                    >
                                    <CornerDownLeft
                                        class="h-4 w-4 text-gray-400"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="flex items-center justify-between border-t border-gray-100 bg-gray-50/50 px-6 py-3 text-xs text-gray-500"
            >
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1">
                        <kbd
                            class="rounded border border-gray-300 bg-white px-1.5 py-0.5 font-mono text-[10px]"
                            >↑</kbd
                        >
                        <kbd
                            class="rounded border border-gray-300 bg-white px-1.5 py-0.5 font-mono text-[10px]"
                            >↓</kbd
                        >
                        <span>to navigate</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <kbd
                            class="rounded border border-gray-300 bg-white px-1.5 py-0.5 font-mono text-[10px]"
                            >Enter</kbd
                        >
                        <span>to select</span>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <kbd
                        class="rounded border border-gray-300 bg-white px-1.5 py-0.5 font-mono text-[10px]"
                        >Esc</kbd
                    >
                    <span>to close</span>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
.scrollable::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.scrollable::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.scrollable::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.scrollable::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Animation for results */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.group {
    animation: slideIn 0.2s ease-out backwards;
}

kbd {
    font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', monospace;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 4px;
    background: linear-gradient(to bottom, #fafafa, #f0f0f0);
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
}
</style>
