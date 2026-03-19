<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import allergen from '@/routes/allergen';
import dish from '@/routes/dish';
import drink from '@/routes/drink';
import ingredient from '@/routes/ingredient';
import meat from '@/routes/meat';
import { GlobalSearchType, SearchResultType } from '@/types';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { CornerDownLeft, Loader } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    open: boolean;
    onClose: () => void;
}>();

const query = ref<string>('');
const results = ref<GlobalSearchType | null>(null);
const isLoading = ref<boolean>(false);

watch(query, async (value) => {
    if (!value) {
        results.value = null;
        return;
    }

    isLoading.value = true;

    try {
        const response = await axios.get('/global-search', {
            params: { query: value },
        });

        results.value = response.data.results;
    } finally {
        isLoading.value = false;
    }
});

const resultTypes: SearchResultType[] = [
    { key: 'dish', label: 'Dishes' },
    { key: 'drink', label: 'Drinks' },
    { key: 'ingredient', label: 'Ingredients' },
    { key: 'meat', label: 'Meats' },
    { key: 'allergen', label: 'Allergens' },
];

const filteredResultTypes = computed(() => {
    return resultTypes.filter((type) => results.value?.[type.key]?.length);
});

const goToEdit = (type: string, id: number) => {
    switch (type) {
        case 'dish':
            router.visit(dish.edit(id).url);
            break;
        case 'drink':
            router.visit(drink.edit(id).url);
            break;
        case 'ingredient':
            router.visit(ingredient.edit(id).url);
            break;
        case 'meat':
            router.visit(meat.edit(id).url);
            break;
        case 'allergen':
            router.visit(allergen.edit(id).url);
            break;
        default:
            break;
    }
};
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

            <div class="border-b px-6 py-4">
                <Input
                    v-model="query"
                    placeholder="Search dishes, drinks, ingredients..."
                    class="h-11"
                    autofocus
                />
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

                <div v-for="type in filteredResultTypes" :key="type.key">
                    <h3
                        class="mb-2 border-b text-xs text-muted-foreground uppercase"
                    >
                        {{ type.label }}
                    </h3>

                    <div
                        v-for="item in results![type.key]"
                        :key="item.id"
                        @click="goToEdit(item.type, item.id)"
                        class="group flex cursor-pointer items-center justify-between gap-3 rounded-md p-2 transition hover:bg-muted"
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
                            class="h-5 w-5 text-gray-400 group-hover:text-gray-500"
                        />
                    </div>
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
