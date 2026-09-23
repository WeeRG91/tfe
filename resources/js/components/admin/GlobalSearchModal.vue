<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { usePermission } from '@/composables/usePermission';
import allergen from '@/routes/admin/allergen';
import dish from '@/routes/admin/dish';
import drink from '@/routes/admin/drink';
import globalSearch from '@/routes/admin/global-search';
import ingredient from '@/routes/admin/ingredient';
import meat from '@/routes/admin/meat';
import role from '@/routes/admin/role';
import user from '@/routes/admin/user';
import { GlobalSearchType, SearchResultType, SearchType } from '@/types';
import {
    AllergenPermissionEnum,
    DishPermissionEnum,
    DrinkPermissionEnum,
    IngredientPermissionEnum,
    MeatPermissionEnum,
    RolePermissionEnum,
    UserPermissionEnum,
} from '@/types/permission';
import { SearchResultEnum } from '@/types/search';
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { CornerDownLeft, Loader, Search, Shield, X } from 'lucide-vue-next';
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

const props = defineProps<{
    open: boolean;
    onClose: () => void;
}>();

const { can } = usePermission();
const { t } = useI18n();

const resultTypes = computed<SearchResultType[]>(() => [
    {
        key: SearchResultEnum.DISH,
        label: t('globalSearch.resultTypes.dishes'),
        permission: DishPermissionEnum.DISH_UPDATE,
    },
    {
        key: SearchResultEnum.DRINK,
        label: t('globalSearch.resultTypes.drinks'),
        permission: DrinkPermissionEnum.DRINK_UPDATE,
    },
    {
        key: SearchResultEnum.INGREDIENT,
        label: t('globalSearch.resultTypes.ingredients'),
        permission: IngredientPermissionEnum.INGREDIENT_UPDATE,
    },
    {
        key: SearchResultEnum.MEAT,
        label: t('globalSearch.resultTypes.meats'),
        permission: MeatPermissionEnum.MEAT_UPDATE,
    },
    {
        key: SearchResultEnum.ALLERGEN,
        label: t('globalSearch.resultTypes.allergens'),
        permission: AllergenPermissionEnum.ALLERGEN_UPDATE,
    },
    {
        key: SearchResultEnum.ROLE,
        label: t('globalSearch.resultTypes.roles'),
        permission: RolePermissionEnum.ROLE_UPDATE,
    },
    {
        key: SearchResultEnum.USER,
        label: t('globalSearch.resultTypes.users'),
        permission: UserPermissionEnum.USER_VIEW,
    },
]);

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
    return resultTypes.value.filter(
        (type) => can(type.permission!) && results.value?.[type.key]?.length,
    );
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
        case SearchResultEnum.ROLE:
            router.visit(role.edit(id).url);
            break;
        case SearchResultEnum.USER:
            router.visit(user.show(id).url);
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
        <DialogContent
            class="gap-0 overflow-hidden border-border bg-card p-0 text-card-foreground sm:max-w-2xl"
        >
            <DialogHeader class="border-b border-border px-6 py-4">
                <DialogTitle class="text-lg font-semibold">
                    {{ t('globalSearch.title') }}
                </DialogTitle>
                <DialogDescription>
                    {{ t('globalSearch.description') }}
                </DialogDescription>
            </DialogHeader>

            <div class="border-b border-border bg-muted/40 px-6 py-5">
                <div class="relative">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        ref="inputRef"
                        v-model="query"
                        :placeholder="t('globalSearch.placeholders.search')"
                        class="h-12 rounded-xl border-input bg-background pr-10 pl-10 text-foreground placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring"
                    />
                    <button
                        v-if="query"
                        @click="clearSearch"
                        class="absolute top-1/2 right-3 -translate-y-1/2 rounded-full p-0.5 transition-colors hover:bg-accent hover:text-accent-foreground"
                    >
                        <X class="h-4 w-4 text-muted-foreground" />
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
                    {{ t('globalSearch.messages.startTyping') }}
                </div>

                <div
                    v-else-if="hasNoResults && !isLoading"
                    class="py-10 text-center text-sm text-muted-foreground"
                >
                    {{ t('globalSearch.messages.noResults') }}
                </div>

                <div
                    v-else-if="results !== null && !isLoading"
                    class="rounded-md bg-muted px-3 py-2 text-muted-foreground"
                >
                    <p class="text-xs">
                        {{
                            t('globalSearch.messages.resultsFound', {
                                count: totalResultsCount,
                            })
                        }}
                    </p>
                </div>

                <div v-for="type in filteredResultTypes" :key="type.key">
                    <h3
                        class="mb-2 flex border-b text-xs text-muted-foreground uppercase"
                    >
                        <span>{{ type.label }}</span>
                        <span class="ml-auto text-xs text-card-foreground">{{
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
                            isSelected(item)
                                ? 'bg-accent text-accent-foreground'
                                : 'text-card-foreground hover:bg-accent hover:text-accent-foreground',
                        ]"
                    >
                        <div class="flex items-center justify-center gap-3">
                            <img
                                v-if="
                                    item.main_image &&
                                    item.type !== SearchResultEnum.ROLE
                                "
                                :src="item.main_image"
                                :alt="item.name"
                                class="h-9 w-9 rounded object-cover"
                            />
                            <Shield v-else class="h-9 w-9 text-current" />
                            <span class="text-sm font-medium">
                                {{ item.name }}
                            </span>
                        </div>

                        <CornerDownLeft
                            class="h-5 w-5 text-current opacity-70"
                        />
                    </div>
                </div>
            </div>

            <div
                class="flex items-center justify-between border-t border-border bg-muted px-6 py-3 text-xs text-muted-foreground"
            >
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1">
                        <kbd
                            class="rounded border border-border bg-card px-1.5 py-0.5 font-mono text-[10px] text-card-foreground"
                            >↑</kbd
                        >
                        <kbd
                            class="rounded border border-border bg-card px-1.5 py-0.5 font-mono text-[10px] text-card-foreground"
                            >↓</kbd
                        >
                        <span>{{ t('globalSearch.keyboard.navigate') }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <kbd
                            class="rounded border border-border bg-card px-1.5 py-0.5 font-mono text-[10px] text-card-foreground"
                            >Enter</kbd
                        >
                        <span>{{ t('globalSearch.keyboard.select') }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <kbd
                        class="rounded border border-border bg-card px-1.5 py-0.5 font-mono text-[10px] text-card-foreground"
                        >Esc</kbd
                    >
                    <span>{{ t('globalSearch.keyboard.close') }}</span>
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
    background-color: var(--border);
}
</style>
