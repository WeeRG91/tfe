<script setup lang="ts">
import menu from '@/routes/menu';
import { GlobalSearchType, SearchResultType, SearchType } from '@/types';
import { SearchResultEnum } from '@/types/search';
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
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
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    open: boolean;
    onClose: () => void;
}>();

const resultTypes: SearchResultType[] = [
    { key: SearchResultEnum.DISH, label: t('search.dishes') },
    { key: SearchResultEnum.DRINK, label: t('search.drinks') },
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
    if (props.open) {
        document.body.style.overflow = 'hidden';
    }
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});

watch(query, search);

watch(
    () => props.open,
    async (isOpen) => {
        if (isOpen) {
            selectedIndex.value = -1;
            document.body.style.overflow = 'hidden';
            await nextTick();
            inputRef.value?.focus();
        } else {
            document.body.style.overflow = '';
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
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-4 backdrop-blur-sm transition-all duration-200"
            @click.self="onClose"
        >
            <div
                class="relative w-full max-w-2xl animate-in duration-200 zoom-in-95 fade-in slide-in-from-top-4"
            >
                <div
                    class="overflow-hidden rounded-2xl border border-border bg-popover text-popover-foreground shadow-2xl"
                >
                    <div
                        class="border-b border-border px-4 py-4 sm:px-6 sm:py-5"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <div
                                class="flex items-center gap-2 text-lg font-semibold text-popover-foreground sm:text-xl"
                            >
                                <Sparkles
                                    class="h-4 w-4 text-primary sm:h-5 sm:w-5"
                                />
                                {{ t('search.title') }}
                            </div>

                            <button
                                @click="props.onClose"
                                class="rounded-full p-2 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                            >
                                <X class="h-5 w-5" />
                            </button>
                        </div>
                        <p
                            class="mt-1 text-xs text-muted-foreground sm:text-sm"
                        >
                            {{ t('search.subtitle') }}
                        </p>
                    </div>

                    <div
                        class="border-b border-border bg-muted/40 px-4 py-4 sm:px-6 sm:py-5"
                    >
                        <div class="relative">
                            <Search
                                class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground sm:h-4 sm:w-4"
                            />
                            <input
                                ref="inputRef"
                                v-model="query"
                                type="text"
                                :placeholder="t('search.placeholder')"
                                class="h-10 w-full rounded-xl border border-input bg-background pr-9 pl-9 text-sm text-foreground transition-colors placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/30 focus:outline-none sm:h-12 sm:pr-10 sm:pl-10 sm:text-base"
                            />
                            <button
                                v-if="query"
                                @click="clearSearch"
                                class="absolute top-1/2 right-2 -translate-y-1/2 rounded-full p-0.5 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground sm:right-3"
                            >
                                <X class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                            </button>
                        </div>
                    </div>

                    <div
                        class="scrollable max-h-[50vh] overflow-y-auto bg-popover sm:max-h-[460px]"
                    >
                        <div
                            v-if="isLoading"
                            class="flex flex-col items-center justify-center gap-3 py-12 sm:py-16"
                        >
                            <Loader
                                class="h-6 w-6 animate-spin text-primary sm:h-8 sm:w-8"
                            />
                            <p class="text-xs text-muted-foreground sm:text-sm">
                                {{ t('search.searching') }}
                            </p>
                        </div>

                        <div
                            v-else-if="!query && !isLoading"
                            class="flex flex-col items-center justify-center gap-3 py-12 sm:py-16"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-muted sm:h-16 sm:w-16"
                            >
                                <Search
                                    class="h-6 w-6 text-muted-foreground sm:h-8 sm:w-8"
                                />
                            </div>
                            <p class="text-xs text-muted-foreground sm:text-sm">
                                {{ t('search.startTyping') }}
                            </p>
                        </div>

                        <div
                            v-else-if="hasNoResults && !isLoading"
                            class="flex flex-col items-center justify-center gap-3 py-12 sm:py-16"
                        >
                            <p
                                class="text-sm font-medium text-popover-foreground sm:text-base"
                            >
                                {{ t('search.noResults') }}
                            </p>
                            <p class="text-xs text-muted-foreground sm:text-sm">
                                {{ t('search.tryDifferentKeywords') }}
                            </p>
                        </div>

                        <div v-else class="divide-y divide-border">
                            <div
                                v-if="results !== null && !isLoading"
                                class="bg-muted/40 px-4 py-2 sm:px-6 sm:py-3"
                            >
                                <p
                                    class="text-[10px] text-muted-foreground sm:text-xs"
                                >
                                    {{
                                        t(
                                            'search.foundResults',
                                            totalResultsCount,
                                        )
                                    }}
                                </p>
                            </div>

                            <div
                                v-for="type in filteredResultTypes"
                                :key="type.key"
                                class="py-2"
                            >
                                <div class="px-4 py-2 sm:px-6 sm:py-3">
                                    <h3
                                        class="flex items-center gap-2 text-[10px] font-semibold tracking-wider text-muted-foreground uppercase sm:text-xs"
                                    >
                                        <span>{{
                                            type.key === 'dish' ? '🍽️' : '🍹'
                                        }}</span>
                                        <span>{{ type.label }}</span>
                                        <span
                                            class="ml-auto text-[10px] text-muted-foreground sm:text-xs"
                                            >{{
                                                results![type.key].length
                                            }}</span
                                        >
                                    </h3>
                                </div>

                                <div class="space-y-1 px-2 sm:px-3">
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
                                        v-for="(item, index) in results![
                                            type.key
                                        ]"
                                        :key="item.id"
                                        @click="goToEdit(item.type, item.id)"
                                        :class="[
                                            'group relative flex cursor-pointer items-center justify-between gap-2 rounded-lg p-2 transition-all duration-150 sm:gap-3 sm:p-3',
                                            isSelected(item)
                                                ? 'scale-[1.02] bg-accent text-accent-foreground ring-1 ring-ring/20'
                                                : 'hover:scale-[1.02] hover:bg-accent/60 hover:text-accent-foreground',
                                        ]"
                                        :style="{
                                            animationDelay: `${index * 30}ms`,
                                        }"
                                    >
                                        <div
                                            class="flex min-w-0 flex-1 items-center gap-3 sm:gap-4"
                                        >
                                            <div class="relative">
                                                <img
                                                    v-if="item.main_image"
                                                    :src="item.main_image"
                                                    :alt="item.name"
                                                    class="h-10 w-10 rounded-lg object-cover shadow-sm sm:h-12 sm:w-12"
                                                />
                                                <div
                                                    v-else
                                                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-muted sm:h-12 sm:w-12"
                                                >
                                                    <span
                                                        class="text-base sm:text-xl"
                                                        >{{
                                                            type.key === 'dish'
                                                                ? '🍽️'
                                                                : '🍹'
                                                        }}</span
                                                    >
                                                </div>
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <p
                                                    :class="[
                                                        'truncate text-xs font-medium transition-colors sm:text-sm',
                                                        isSelected(item)
                                                            ? 'text-primary'
                                                            : 'text-popover-foreground group-hover:text-primary',
                                                    ]"
                                                >
                                                    {{ item.name }}
                                                </p>
                                                <p
                                                    class="mt-0.5 text-[10px] text-muted-foreground sm:text-xs"
                                                >
                                                    {{
                                                        t(
                                                            `menu.categories.${item.category.key}`,
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            :class="[
                                                'flex items-center gap-1 opacity-0 transition-opacity duration-200 sm:gap-2',
                                                isSelected(item)
                                                    ? 'opacity-100'
                                                    : 'group-hover:opacity-100',
                                            ]"
                                        >
                                            <span
                                                class="text-[10px] text-muted-foreground sm:text-xs"
                                                >Enter</span
                                            >
                                            <CornerDownLeft
                                                class="h-3 w-3 text-muted-foreground sm:h-4 sm:w-4"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex flex-wrap items-center justify-between gap-2 border-t border-border bg-muted/40 px-4 py-2 text-[10px] text-muted-foreground sm:px-6 sm:py-3 sm:text-xs"
                    >
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="flex items-center gap-1">
                                <kbd
                                    class="rounded border border-input bg-background px-1 py-0.5 font-mono text-[8px] text-foreground shadow-xs sm:px-1.5 sm:py-0.5 sm:text-[10px]"
                                    >↑</kbd
                                >
                                <kbd
                                    class="rounded border border-input bg-background px-1 py-0.5 font-mono text-[8px] text-foreground shadow-xs sm:px-1.5 sm:py-0.5 sm:text-[10px]"
                                    >↓</kbd
                                >
                                <span>{{ t('search.keyboard.navigate') }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <kbd
                                    class="rounded border border-input bg-background px-1 py-0.5 font-mono text-[8px] text-foreground shadow-xs sm:px-1.5 sm:py-0.5 sm:text-[10px]"
                                    >Enter</kbd
                                >
                                <span>{{ t('search.keyboard.select') }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <kbd
                                class="rounded border border-input bg-background px-1 py-0.5 font-mono text-[8px] text-foreground shadow-xs sm:px-1.5 sm:py-0.5 sm:text-[10px]"
                                >Esc</kbd
                            >
                            <span>{{ t('search.keyboard.close') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.scrollable {
    scrollbar-color: var(--border) var(--muted);
    scrollbar-width: thin;
}

.scrollable::-webkit-scrollbar-track {
    background: var(--muted);
    border-radius: 10px;
}

.scrollable::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 10px;
}

.scrollable::-webkit-scrollbar-thumb:hover {
    background: var(--muted-foreground);
}

.fixed {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}
</style>
