<script setup lang="ts">
import ConfirmModal from '@/components/ConfirmModal.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useDateFormatter } from '@/composables/useDateFormatter';
import { usePermission } from '@/composables/usePermission';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ingredient from '@/routes/admin/ingredient';
import type { BreadcrumbItem, CursorPaginated, FilterType } from '@/types';
import { AllergenOptionType } from '@/types/allergen';
import { IngredientType } from '@/types/ingredient';
import { IngredientPermissionEnum } from '@/types/permission';
import { Head, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import axios from 'axios';
import {
    ChevronDownIcon,
    EllipsisVerticalIcon,
    Loader,
    Plus,
    RotateCcwIcon,
    SearchIcon,
    SquarePenIcon,
    TrashIcon,
    XIcon,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    allergens: AllergenOptionType[];
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('ingredient.title'),
        href: ingredient.index().url,
    },
]);

const { can } = usePermission();
const { formatDateOnly } = useDateFormatter();

const confirmModalOpen = ref<boolean>(false);
const confirmModalMessage = ref<string>('');
const confirmModalType = ref<'destructive' | 'info'>('info');
const confirmModalAction = ref<() => void>(() => {});
const ingredients = ref<IngredientType[]>([]);
const nextCursor = ref<string | null>(null);
const isLoading = ref<boolean>(false);
const sentinel = ref<HTMLElement | null>(null);
const scrollContainer = ref<HTMLElement | null>(null);
const observer = ref<IntersectionObserver | null>(null);
const filter = ref<FilterType>('all');
const allergen = ref<number | null>(null);
const search = ref<string | null>(null);

const moveToBin = async (id: number) => {
    isLoading.value = true;

    try {
        const response = await axios.delete(ingredient.destroy(id).url);

        ingredients.value = ingredients.value.filter((d) => d.id !== id);

        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error(t('ingredient.errors.binFailed'));
        }
    } finally {
        isLoading.value = false;
        closeConfirmModal();
    }
};

const restoreIngredient = async (id: number) => {
    isLoading.value = true;

    try {
        const response = await axios.post(ingredient.restore(id).url);

        ingredients.value = ingredients.value.filter((d) => d.id !== id);

        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error(t('ingredient.errors.restoreFailed'));
        }
    } finally {
        isLoading.value = false;
        closeConfirmModal();
    }
};

const deleteIngredient = async (id: number) => {
    isLoading.value = true;

    try {
        const response = await axios.delete(ingredient.forceDelete(id).url);

        ingredients.value = ingredients.value.filter((d) => d.id !== id);

        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error(t('ingredient.errors.deleteFailed'));
        }
    } finally {
        isLoading.value = false;
        closeConfirmModal();
    }
};

const openConfirmModal = (
    message: string,
    type: 'destructive' | 'info',
    action: () => void,
) => {
    confirmModalMessage.value = message;
    confirmModalType.value = type;
    confirmModalAction.value = action;
    confirmModalOpen.value = true;
};

const closeConfirmModal = () => {
    confirmModalOpen.value = false;
};

const goToEdit = (id: number) => {
    if (can(IngredientPermissionEnum.INGREDIENT_UPDATE)) {
        router.visit(ingredient.edit(id).url);
    }
};

const loadIngredients = async () => {
    if (isLoading.value) return;

    isLoading.value = true;

    try {
        const response = await axios.get<CursorPaginated<IngredientType>>(
            ingredient.getIngredients().url,
            {
                params: {
                    cursor: nextCursor.value,
                    filter: filter.value,
                    allergen: allergen.value,
                    search: search.value,
                },
            },
        );
        const newIngredients = response.data;
        ingredients.value.push(...newIngredients.data);
        nextCursor.value = newIngredients.next_cursor;
    } catch (error) {
        console.log(error);
        toast.error(t('ingredient.errors.loadFailed'));
    } finally {
        isLoading.value = false;
    }
};

const applyFilters = () => {
    ingredients.value = [];
    nextCursor.value = null;

    observer.value?.disconnect();

    loadIngredients();

    if (sentinel.value) {
        observer.value?.observe(sentinel.value);
    }
};

const changeFilter = (value: FilterType) => {
    filter.value = value;
    applyFilters();
};

const getAllergenLabel = (allergen: number) => {
    const filteredAllergen = props.allergens.find((a) => a.value === allergen);
    return filteredAllergen ? filteredAllergen.label : null;
};

const resetAllFilters = () => {
    filter.value = 'all';
    allergen.value = null;
    search.value = null;
    applyFilters();
};

watchDebounced(
    search,
    () => {
        applyFilters();
    },
    { debounce: 400 },
);

onMounted(() => {
    loadIngredients();

    observer.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    loadIngredients();
                }
            });
        },
        {
            root: scrollContainer.value,
            rootMargin: '300px',
            threshold: 0,
        },
    );

    watch(sentinel, (element) => {
        if (element) observer.value?.observe(element);
    });
});

onBeforeUnmount(() => {
    if (observer.value && sentinel.value)
        observer.value.unobserve(sentinel.value);
});
</script>

<template>
    <Head :title="t('ingredient.title')" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-hidden rounded-t-xl p-4"
        >
            <div
                ref="scrollContainer"
                class="relative h-full flex-1 overflow-hidden rounded-t-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <div
                    class="flex flex-col gap-2 border-b bg-gradient-to-b from-white to-gray-50/50 p-4 md:flex-row md:items-center md:justify-between dark:from-gray-900 dark:to-gray-900/50"
                >
                    <div
                        class="flex w-full flex-wrap items-center gap-2 sm:w-auto"
                    >
                        <div
                            class="flex w-full flex-row items-center gap-2 sm:flex-1"
                        >
                            <div
                                class="flex flex-nowrap justify-around gap-1.5"
                            >
                                <Button
                                    size="sm"
                                    :variant="
                                        filter === 'all' ? 'default' : 'outline'
                                    "
                                    @click="changeFilter('all')"
                                    class="cursor-pointer text-xs shadow-sm transition-all duration-200 sm:h-9 sm:text-sm"
                                    :class="
                                        filter !== 'all' &&
                                        'hover:bg-gray-100 dark:hover:bg-gray-800'
                                    "
                                >
                                    {{ t('admin.filters.all') }}
                                </Button>

                                <Button
                                    size="sm"
                                    :variant="
                                        filter === 'deleted'
                                            ? 'default'
                                            : 'outline'
                                    "
                                    @click="changeFilter('deleted')"
                                    class="cursor-pointer text-xs shadow-sm transition-all duration-200 sm:h-9 sm:text-sm"
                                    :class="[
                                        filter === 'deleted'
                                            ? 'bg-rose-600 hover:bg-rose-700 dark:bg-rose-600'
                                            : 'hover:bg-gray-100 dark:hover:bg-gray-800',
                                    ]"
                                >
                                    {{ t('admin.filters.deleted') }}
                                </Button>
                            </div>

                            <div class="relative flex-1 sm:w-52 sm:flex-none">
                                <SearchIcon
                                    class="absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2 text-gray-400"
                                />
                                <input
                                    v-model="search"
                                    type="text"
                                    :placeholder="
                                        t('admin.filters.searchPlaceholder')
                                    "
                                    class="h-8 w-full rounded-md border border-gray-200 bg-white pr-3 pl-8 text-sm text-gray-700 shadow-sm transition-all placeholder:text-gray-400 hover:border-gray-300 focus:outline-none sm:h-9 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:placeholder:text-gray-500 dark:hover:border-gray-600"
                                    @keyup.enter="applyFilters"
                                />
                                <button
                                    v-if="search"
                                    @click="
                                        search = '';
                                        applyFilters();
                                    "
                                    class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                >
                                    <XIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <div
                            class="flex w-full flex-row items-center gap-2 sm:flex-1"
                        >
                            <div class="relative flex-1 sm:w-52 sm:flex-none">
                                <select
                                    v-model="allergen"
                                    @change="applyFilters"
                                    class="h-8 w-full appearance-none rounded-md border border-gray-200 bg-white px-3 pr-8 text-sm text-gray-700 shadow-sm transition-all hover:border-gray-300 focus:outline-none sm:h-9 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-gray-600"
                                >
                                    <option :value="null">
                                        {{ t('ingredient.allAllergens') }}
                                    </option>
                                    <option
                                        v-for="al in props.allergens"
                                        :key="al.value"
                                        :value="al.value"
                                    >
                                        {{ al.label }}
                                    </option>
                                </select>
                                <ChevronDownIcon
                                    class="pointer-events-none absolute top-1/2 right-2 h-4 w-4 -translate-y-1/2 text-gray-400"
                                />
                            </div>

                            <div
                                v-if="
                                    can(
                                        IngredientPermissionEnum.INGREDIENT_CREATE,
                                    )
                                "
                                class="flex-none sm:ml-auto sm:hidden"
                            >
                                <a
                                    :href="ingredient.create().url"
                                    class="inline-block"
                                >
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="group cursor-pointer shadow-sm transition-all duration-300 hover:bg-gray-100 hover:shadow-md sm:h-9 dark:hover:bg-gray-800"
                                    >
                                        <Plus
                                            class="mr-2 h-5 w-5 transition-transform group-hover:scale-110"
                                        />
                                        {{ t('admin.buttons.add') }}
                                    </Button>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="can(IngredientPermissionEnum.INGREDIENT_CREATE)"
                        class="hidden sm:ml-auto sm:flex"
                    >
                        <a :href="ingredient.create().url" class="inline-block">
                            <Button
                                variant="outline"
                                size="sm"
                                class="group cursor-pointer shadow-sm transition-all duration-300 hover:bg-gray-100 hover:shadow-md sm:h-9 dark:hover:bg-gray-800"
                            >
                                <Plus
                                    class="mr-2 h-5 w-5 transition-transform duration-300 group-hover:scale-110"
                                />
                                {{ t('admin.buttons.add') }}
                            </Button>
                        </a>
                    </div>
                </div>

                <div
                    v-if="filter !== 'all' || allergen || search"
                    class="flex flex-wrap items-center gap-2 border-t bg-gray-50/50 px-4 py-2 text-sm dark:bg-gray-900/50"
                >
                    <span
                        class="hidden text-gray-500 sm:inline-block dark:text-gray-400"
                        >{{ t('admin.filters.activeFilters') }}</span
                    >
                    <span
                        v-if="filter !== 'all'"
                        class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300"
                    >
                        {{ t(`admin.filters.${filter}`) }}
                        <button
                            @click="changeFilter('all')"
                            class="ml-1 cursor-pointer hover:text-blue-600"
                        >
                            ×
                        </button>
                    </span>
                    <span
                        v-if="allergen"
                        class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900/30 dark:text-purple-300"
                    >
                        {{ getAllergenLabel(allergen) }}
                        <button
                            @click="
                                allergen = null;
                                applyFilters();
                            "
                            class="ml-1 cursor-pointer hover:text-purple-600"
                        >
                            ×
                        </button>
                    </span>
                    <span
                        v-if="search"
                        class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/30 dark:text-amber-300"
                    >
                        "{{ search }}"
                        <button
                            @click="
                                search = '';
                                applyFilters();
                            "
                            class="ml-1 cursor-pointer hover:text-amber-600"
                        >
                            ×
                        </button>
                    </span>
                    <button
                        @click="resetAllFilters"
                        class="ml-auto hidden cursor-pointer text-xs text-gray-500 hover:text-gray-700 sm:inline-block dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        {{ t('admin.filters.clearAll') }}
                    </button>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="text-nowrap">
                                {{ t('ingredient.table.name') }}
                            </TableHead>
                            <TableHead class="text-nowrap">
                                {{ t('ingredient.table.allergen') }}
                            </TableHead>
                            <TableHead class="text-nowrap">
                                {{ t('ingredient.table.createdAt') }}
                            </TableHead>
                            <TableHead
                                v-if="filter !== 'deleted'"
                                class="text-nowrap"
                            >
                                {{ t('ingredient.table.updatedAt') }}
                            </TableHead>
                            <TableHead v-else class="text-nowrap">
                                {{ t('ingredient.table.deletedAt') }}
                            </TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="ingredientData in ingredients"
                            :key="ingredientData.id"
                            @dblclick="goToEdit(ingredientData.id)"
                            class="cursor-pointer"
                        >
                            <TableCell class="flex items-center gap-4">
                                <img
                                    :src="ingredientData.main_image"
                                    :alt="ingredientData.name"
                                    class="hidden h-8 w-8 rounded-lg md:block"
                                />
                                <span class="whitespace-nowrap">
                                    {{ ingredientData.name }}
                                </span>
                            </TableCell>
                            <TableCell class="group relative">
                                <img
                                    v-if="ingredientData.allergen?.main_image"
                                    :src="ingredientData.allergen?.main_image"
                                    :alt="ingredientData.allergen?.name"
                                    class="h-8 w-8 rounded-full"
                                />
                                <div
                                    v-if="ingredientData.allergen?.name"
                                    class="absolute top-5 left-13 mb-1 w-max rounded bg-gray-800 px-2 py-1 text-xs text-white opacity-0 transition group-hover:opacity-100"
                                >
                                    {{ ingredientData.allergen.name }}
                                </div>
                            </TableCell>
                            <TableCell
                                ><span class="whitespace-nowrap">{{
                                    formatDateOnly(ingredientData.created_at)
                                }}</span></TableCell
                            >
                            <TableCell v-if="filter !== 'deleted'"
                                ><span class="whitespace-nowrap">{{
                                    formatDateOnly(ingredientData.updated_at)
                                }}</span>
                            </TableCell>
                            <TableCell v-else
                                ><span class="whitespace-nowrap">{{
                                    formatDateOnly(ingredientData.deleted_at)
                                }}</span>
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger
                                        v-if="
                                            can(
                                                IngredientPermissionEnum.INGREDIENT_UPDATE,
                                            )
                                        "
                                    >
                                        <EllipsisVerticalIcon />
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        v-if="filter === 'deleted'"
                                    >
                                        <DropdownMenuItem
                                            v-if="
                                                can(
                                                    IngredientPermissionEnum.INGREDIENT_RESTORE,
                                                )
                                            "
                                            @click="
                                                openConfirmModal(
                                                    t(
                                                        'ingredient.messages.confirmRestore',
                                                    ),
                                                    'info',
                                                    () =>
                                                        restoreIngredient(
                                                            ingredientData.id,
                                                        ),
                                                )
                                            "
                                        >
                                            <RotateCcwIcon />
                                            {{ t('admin.buttons.restore') }}
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="
                                                can(
                                                    IngredientPermissionEnum.INGREDIENT_DELETE,
                                                )
                                            "
                                            @click="
                                                openConfirmModal(
                                                    t(
                                                        'ingredient.messages.confirmDelete',
                                                    ),
                                                    'destructive',
                                                    () =>
                                                        deleteIngredient(
                                                            ingredientData.id,
                                                        ),
                                                )
                                            "
                                        >
                                            <TrashIcon />
                                            {{ t('admin.buttons.delete') }}
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                    <DropdownMenuContent v-else>
                                        <DropdownMenuItem>
                                            <a
                                                :href="
                                                    ingredient.edit(
                                                        ingredientData.id,
                                                    ).url
                                                "
                                                class="flex gap-2"
                                            >
                                                <SquarePenIcon />
                                                {{ t('admin.buttons.edit') }}
                                            </a>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="
                                                can(
                                                    IngredientPermissionEnum.INGREDIENT_DELETE,
                                                )
                                            "
                                            @click="
                                                openConfirmModal(
                                                    t(
                                                        'ingredient.messages.confirmMoveToBin',
                                                    ),
                                                    'destructive',
                                                    () =>
                                                        moveToBin(
                                                            ingredientData.id,
                                                        ),
                                                )
                                            "
                                        >
                                            <TrashIcon />
                                            {{ t('admin.buttons.moveToBin') }}
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>

                        <tr ref="sentinel" v-if="nextCursor">
                            <td
                                :colspan="5"
                                class="py-2 text-center text-gray-500"
                            >
                                <Loader
                                    class="mx-auto animate-spin text-muted-foreground"
                                />
                            </td>
                        </tr>
                    </TableBody>
                </Table>

                <div
                    v-if="isLoading"
                    class="flex h-24 items-center justify-center sm:h-32"
                >
                    <Loader
                        class="mx-auto animate-spin text-muted-foreground"
                    />
                </div>

                <div
                    v-if="ingredients.length === 0 && !isLoading"
                    class="flex h-24 items-center justify-center text-gray-300 sm:h-32"
                >
                    {{ t('ingredient.messages.noIngredient') }}
                </div>

                <ConfirmModal
                    :open="confirmModalOpen"
                    :onClose="closeConfirmModal"
                    :message="confirmModalMessage"
                    :type="confirmModalType"
                    :isLoading="isLoading"
                    @confirm="confirmModalAction"
                />
            </div>
        </div>
    </AdminLayout>
</template>
