<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { CornerDownLeft, Loader, Search, X } from 'lucide-vue-next';
import {
    ComponentPublicInstance,
    computed,
    nextTick,
    onMounted,
    onUnmounted,
    ref,
    watch,
} from 'vue';
import dish from '@/routes/admin/dish';
import drink from '@/routes/admin/drink';
import ingredient from '@/routes/admin/ingredient';
import meat from '@/routes/admin/meat';
import allergen from '@/routes/admin/allergen';
import globalSearch from '@/routes/admin/global-search';
import {
    GlobalSearchType,
    SearchResultType,
    SearchType,
} from '@/types';
import { SearchResultEnum } from '@/types/search';

const props = defineProps<{
    open: boolean;
    onClose: () => void;
}>();

const resultTypes: SearchResultType[] = [
    { key: SearchResultEnum.DISH, label: 'Dishes' },
    { key: SearchResultEnum.DRINK, label: 'Drinks' },
    { key: SearchResultEnum.INGREDIENT, label: 'Ingredients' },
    { key: SearchResultEnum.MEAT, label: 'Meats' },
    { key: SearchResultEnum.ALLERGEN, label: 'Allergens' },
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
        const response = await axios.get(globalSearch.search().url, {
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
                goToEdit(item.type as SearchResultEnum, item.id);
            }
            break;

        case 'Escape':
            e.preventDefault();
            props.onClose();
            break;
    }
};

const goToEdit = (type: SearchResultEnum, id: number) => {
    switch (type) {
        case SearchResultEnum.DISH:
            router.visit(dish.edit(id).url);
            break;
        case SearchResultEnum.DRINK:
            router.visit(drink.edit(id).url);
            break;
        case SearchResultEnum.INGREDIENT:
            router.visit(ingredient.edit(id).url);
            break;
        case SearchResultEnum.MEAT:
            router.visit(meat.edit(id).url);
            break;
        case SearchResultEnum.ALLERGEN:
            router.visit(allergen.edit(id).url);
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
        <DialogContent class="gap-0 overflow-hidden p-0 sm:max-w-2xl">
            <DialogHeader class="border-b px-6 py-4">
                <DialogTitle class="text-lg font-semibold">
                    Global Search
                </DialogTitle>
                <DialogDescription>
                    What are you looking for?
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
                        placeholder="Search dishes, drinks, ingredients..."
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

            <div
                class="scrollable max-h-[420px] space-y-6 overflow-y-auto px-6 py-4"
            >
                <div
                    v-if="isLoading"
                    class="flex items-center justify-center py-12"
                >
                    <Loader
                        class="h-6 w-6 animate-spin text-muted-foreground"
                    />
                </div>

                <div
                    v-if="!query && !isLoading"
                    class="py-10 text-center text-sm text-muted-foreground"
                >
                    Start typing to search…
                </div>

                <div
                    v-else-if="hasNoResults && !isLoading"
                    class="py-10 text-center text-sm text-muted-foreground"
                >
                    Not found what you are looking for !
                </div>

                <div
                    v-else-if="results !== null && !isLoading"
                    class="bg-gray-50/50"
                >
                    <p class="text-xs text-gray-500">
                        Found {{ totalResultsCount }} result{{
                            totalResultsCount !== 1 ? 's' : ''
                        }}
                    </p>
                </div>

                <div v-for="type in filteredResultTypes" :key="type.key">
                    <h3
                        class="mb-2 flex border-b text-xs text-muted-foreground uppercase"
                    >
                        <span>{{ type.label }}</span>
                        <span class="ml-auto text-xs text-gray-400">{{
                            results![type.key].length
                        }}</span>
                    </h3>

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
                        v-for="item in results![type.key]"
                        :key="item.id"
                        @click="
                            goToEdit(item.type as SearchResultEnum, item.id)
                        "
                        :class="[
                            'group flex cursor-pointer items-center justify-between gap-3 rounded-md p-2 transition',
                            isSelected(item) ? 'bg-muted' : 'hover:bg-muted',
                        ]"
                    >
                        <div class="flex items-center justify-center gap-3">
                            <img
                                v-if="item.main_image"
                                :src="item.main_image"
                                :alt="item.name"
                                class="h-9 w-9 rounded object-cover"
                            />
                            <span class="text-sm font-medium">
                                {{ item.name }}
                            </span>
                        </div>

                        <CornerDownLeft
                            :class="[
                                'h-5 w-5 text-gray-400',
                                isSelected(item)
                                    ? 'text-gray-500'
                                    : 'group-hover:text-gray-500',
                            ]"
                        />
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
    width: 4px;
    height: 4px;
}

.scrollable::-webkit-scrollbar-track {
    background: transparent;
}

.scrollable::-webkit-scrollbar-thumb {
    background-color: rgba(100, 100, 100, 0.3);
}
</style>
