<script setup lang="ts">
import ConfirmModal from '@/components/ConfirmModal.vue';
import { Badge } from '@/components/ui/badge';
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
import { getCategoryBadgeColor } from '@/lib/utils';
import drink from '@/routes/admin/drink';
import type { BreadcrumbItem, CursorPaginated, FilterType } from '@/types';
import { CategoryOptionType } from '@/types/category';
import { DishType } from '@/types/dish';
import { DrinkAvailabilityEnum, DrinkType } from '@/types/drink';
import { DrinkPermissionEnum } from '@/types/permission';
import { Head, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import axios from 'axios';
import {
    ChevronDownIcon,
    CircleCheckBigIcon,
    CircleXIcon,
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
    categories: CategoryOptionType[];
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('drink.title'),
        href: drink.index().url,
    },
]);

const { can } = usePermission();
const { formatDateOnly } = useDateFormatter();

const confirmModalOpen = ref<boolean>(false);
const confirmModalMessage = ref<string>('');
const confirmModalType = ref<'destructive' | 'info'>('info');
const confirmModalAction = ref<() => void>(() => {});
const drinks = ref<DrinkType[]>([]);
const nextCursor = ref<string | null>(null);
const isLoading = ref<boolean>(false);
const sentinel = ref<HTMLElement | null>(null);
const observer = ref<IntersectionObserver | null>(null);
const filter = ref<FilterType>('all');
const category = ref<number | null>(null);
const search = ref<string | null>(null);

const toggleAvailability = async (id: number) => {
    isLoading.value = true;

    try {
        const response = await axios.patch(drink.available(id).url);

        const updatedDish = response.data.drink as DishType;

        const index = drinks.value.findIndex((d) => d.id === id);

        if (index !== -1) {
            drinks.value[index] = updatedDish;
        }

        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error(t('drink.errors.availabilityFailed'));
        }
    } finally {
        isLoading.value = false;
        closeConfirmModal();
    }
};

const moveToBin = async (id: number) => {
    isLoading.value = true;

    try {
        const response = await axios.delete(drink.destroy(id).url);

        drinks.value = drinks.value.filter((d) => d.id !== id);

        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error(t('drink.errors.binFailed'));
        }
    } finally {
        isLoading.value = false;
        closeConfirmModal();
    }
};

const restoreDrink = async (id: number) => {
    isLoading.value = true;

    try {
        const response = await axios.post(drink.restore(id).url);

        drinks.value = drinks.value.filter((d) => d.id !== id);

        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error(t('drink.errors.restoreFailed'));
        }
    } finally {
        isLoading.value = false;
        closeConfirmModal();
    }
};

const deleteDrink = async (id: number) => {
    isLoading.value = true;

    try {
        const response = await axios.delete(drink.forceDelete(id).url);

        drinks.value = drinks.value.filter((d) => d.id !== id);

        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error(t('drink.errors.deleteFailed'));
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
    if (can(DrinkPermissionEnum.DRINK_UPDATE)) {
        router.visit(drink.edit(id).url);
    }
};

const loadDrinks = async () => {
    if (isLoading.value) return;

    isLoading.value = true;

    try {
        const response = await axios.get<CursorPaginated<DrinkType>>(
            drink.getDrinks().url,
            {
                params: {
                    cursor: nextCursor.value,
                    filter: filter.value,
                    category: category.value,
                    search: search.value,
                },
            },
        );
        const newDrinks = response.data;
        drinks.value.push(...newDrinks.data);
        nextCursor.value = newDrinks.next_cursor;
    } catch (error) {
        console.log(error);
        toast.error(t('drink.errors.loadFailed'));
    } finally {
        isLoading.value = false;
    }
};

const applyFilters = () => {
    drinks.value = [];
    nextCursor.value = null;

    observer.value?.disconnect();

    loadDrinks();

    if (sentinel.value) {
        observer.value?.observe(sentinel.value);
    }
};

const changeFilter = (value: FilterType) => {
    filter.value = value;
    applyFilters();
};

const getCategoryLabel = (category: number) => {
    const filteredCategory = props.categories.find((c) => c.value === category);
    return filteredCategory ? filteredCategory.label : null;
};

const resetAllFilters = () => {
    filter.value = 'all';
    category.value = null;
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
    loadDrinks();

    observer.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    loadDrinks();
                }
            });
        },
        {
            root: null,
            rootMargin: '100px',
            threshold: 0.1,
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
    <Head title="Drinks" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-hidden rounded-t-xl p-4"
        >
            <div
                ref="scrollContainer"
                class="relative h-full flex-1 overflow-hidden rounded-t-xl border border-border"
            >
                <div
                    class="flex flex-col gap-2 border-b border-border bg-gradient-to-b from-card to-muted/50 p-4 md:flex-row md:items-center md:justify-between"
                >
                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center"
                    >
                        <div class="flex flex-nowrap justify-evenly gap-1.5">
                            <Button
                                size="sm"
                                :variant="
                                    filter === 'all' ? 'default' : 'outline'
                                "
                                @click="changeFilter('all')"
                                class="cursor-pointer text-xs shadow-sm transition-all duration-200 sm:h-9 sm:text-sm"
                            >
                                {{ t('admin.filters.all') }}
                            </Button>

                            <Button
                                size="sm"
                                :variant="
                                    filter === 'available'
                                        ? 'success'
                                        : 'outline'
                                "
                                @click="changeFilter('available')"
                                class="cursor-pointer text-xs shadow-sm transition-all duration-200 sm:h-9 sm:text-sm"
                            >
                                {{ t('admin.filters.available') }}
                            </Button>

                            <Button
                                size="sm"
                                :variant="
                                    filter === 'unavailable'
                                        ? 'warning'
                                        : 'outline'
                                "
                                @click="changeFilter('unavailable')"
                                class="cursor-pointer text-xs shadow-sm transition-all duration-200 sm:h-9 sm:text-sm"
                            >
                                {{ t('admin.filters.unavailable') }}
                            </Button>

                            <Button
                                size="sm"
                                :variant="
                                    filter === 'deleted'
                                        ? 'destructive'
                                        : 'outline'
                                "
                                @click="changeFilter('deleted')"
                                class="cursor-pointer text-xs shadow-sm transition-all duration-200 sm:h-9 sm:text-sm"
                            >
                                {{ t('admin.filters.deleted') }}
                            </Button>
                        </div>

                        <div
                            class="flex w-full flex-wrap items-center gap-2 sm:w-auto"
                        >
                            <div class="relative flex-1 sm:w-52 sm:flex-none">
                                <SearchIcon
                                    class="absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <input
                                    v-model="search"
                                    type="text"
                                    :placeholder="
                                        t('admin.filters.searchPlaceholder')
                                    "
                                    class="h-8 w-full rounded-md border border-border bg-background pr-3 pl-8 text-sm text-foreground shadow-sm transition-all placeholder:text-muted-foreground hover:border-primary/50 focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none sm:h-9"
                                    @keyup.enter="applyFilters"
                                />
                                <button
                                    v-if="search"
                                    @click="
                                        search = '';
                                        applyFilters();
                                    "
                                    class="absolute top-1/2 right-2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                >
                                    <XIcon class="h-4 w-4" />
                                </button>
                            </div>

                            <div
                                class="flex w-full flex-row items-center gap-2 sm:flex-1"
                            >
                                <div
                                    class="relative flex-1 sm:w-52 sm:flex-none"
                                >
                                    <select
                                        v-model="category"
                                        @change="applyFilters"
                                        class="h-8 w-full appearance-none rounded-md border border-border bg-background px-3 pr-8 text-sm text-foreground shadow-sm transition-all hover:border-primary/50 focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none sm:h-9"
                                    >
                                        <option :value="null">
                                            {{
                                                t('admin.filters.allCategories')
                                            }}
                                        </option>
                                        <option
                                            v-for="cat in props.categories"
                                            :key="cat.value"
                                            :value="cat.value"
                                        >
                                            {{
                                                t(`drink.categories.${cat.key}`)
                                            }}
                                        </option>
                                    </select>
                                    <ChevronDownIcon
                                        class="pointer-events-none absolute top-1/2 right-2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                    />
                                </div>

                                <div
                                    v-if="can(DrinkPermissionEnum.DRINK_CREATE)"
                                    class="flex-none sm:ml-auto sm:hidden"
                                >
                                    <a
                                        :href="drink.create().url"
                                        class="inline-block"
                                    >
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            class="group cursor-pointer shadow-sm transition-all duration-300 hover:shadow-md sm:h-9"
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
                    </div>

                    <div
                        v-if="can(DrinkPermissionEnum.DRINK_CREATE)"
                        class="hidden sm:ml-auto sm:flex"
                    >
                        <a :href="drink.create().url" class="inline-block">
                            <Button
                                variant="outline"
                                size="sm"
                                class="group cursor-pointer shadow-sm transition-all duration-300 hover:shadow-md sm:h-9"
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
                    v-if="filter !== 'all' || category || search"
                    class="flex flex-wrap items-center gap-2 border-t border-border bg-muted/50 px-4 py-2 text-sm"
                >
                    <span
                        class="hidden text-muted-foreground sm:inline-block"
                        >{{ t('admin.buttons.activeFilters') }}</span
                    >
                    <span
                        v-if="filter !== 'all'"
                        class="inline-flex items-center rounded-full bg-info px-2.5 py-0.5 text-xs font-medium text-info-foreground"
                    >
                        {{ t(`admin.filters.${filter}`) }}
                        <button
                            @click="changeFilter('all')"
                            class="ml-1 cursor-pointer rounded-full hover:bg-info-foreground/20"
                        >
                            ×
                        </button>
                    </span>
                    <span
                        v-if="category"
                        class="inline-flex items-center rounded-full bg-accent px-2.5 py-0.5 text-xs font-medium text-accent-foreground"
                    >
                        {{ getCategoryLabel(category) }}
                        <button
                            @click="
                                category = null;
                                applyFilters();
                            "
                            class="ml-1 cursor-pointer rounded-full hover:bg-accent-foreground/20"
                        >
                            ×
                        </button>
                    </span>
                    <span
                        v-if="search"
                        class="inline-flex items-center rounded-full bg-warning px-2.5 py-0.5 text-xs font-medium text-warning-foreground"
                    >
                        "{{ search }}"
                        <button
                            @click="
                                search = '';
                                applyFilters();
                            "
                            class="ml-1 cursor-pointer rounded-full hover:bg-warning-foreground/20"
                        >
                            ×
                        </button>
                    </span>
                    <button
                        @click="resetAllFilters"
                        class="ml-auto hidden cursor-pointer text-xs text-muted-foreground hover:text-foreground sm:inline-block"
                    >
                        {{ t('admin.buttons.clearAll') }}
                    </button>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="text-nowrap">
                                {{ t('drink.table.name') }}
                            </TableHead>
                            <TableHead class="text-nowrap">
                                {{ t('drink.table.category') }}
                            </TableHead>
                            <TableHead class="hidden text-nowrap md:table-cell">
                                {{ t('drink.table.price') }}
                            </TableHead>
                            <TableHead>
                                {{ t('drink.table.availability') }}
                            </TableHead>
                            <TableHead class="hidden text-nowrap md:table-cell">
                                {{ t('drink.table.createdAt') }}
                            </TableHead>
                            <TableHead
                                v-if="filter !== 'deleted'"
                                class="text-nowrap"
                            >
                                {{ t('drink.table.updatedAt') }}
                            </TableHead>
                            <TableHead v-else class="text-nowrap">
                                {{ t('drink.table.deletedAt') }}
                            </TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="drinkData in drinks"
                            :key="drinkData.id"
                            @dblclick="goToEdit(drinkData.id)"
                            class="cursor-pointer"
                        >
                            <TableCell class="flex items-center gap-4">
                                <img
                                    :src="drinkData.main_image"
                                    :alt="drinkData.name"
                                    class="hidden h-8 w-8 rounded-lg md:block"
                                />
                                <span class="whitespace-nowrap">
                                    {{ drinkData.name }}
                                </span>
                            </TableCell>
                            <TableCell>
                                <Badge
                                    :class="
                                        getCategoryBadgeColor(
                                            drinkData.category.value,
                                        )
                                    "
                                    >{{
                                        t(
                                            `drink.categories.${drinkData.category.key}`,
                                        )
                                    }}</Badge
                                >
                            </TableCell>
                            <TableCell class="hidden md:table-cell"
                                >{{ drinkData.price }} €</TableCell
                            >
                            <TableCell>
                                <Badge
                                    v-if="
                                        drinkData.is_available === 'available'
                                    "
                                    class="bg-success text-success-foreground"
                                    >{{
                                        t(
                                            `admin.status.${drinkData.is_available}`,
                                        )
                                    }}</Badge
                                >
                                <Badge
                                    v-else
                                    class="bg-destructive text-destructive-foreground"
                                >
                                    {{
                                        t(
                                            `admin.status.${drinkData.is_available}`,
                                        )
                                    }}
                                </Badge>
                            </TableCell>
                            <TableCell class="hidden md:table-cell"
                                ><span class="whitespace-nowrap">
                                    {{ formatDateOnly(drinkData.created_at) }}
                                </span>
                            </TableCell>
                            <TableCell v-if="filter !== 'deleted'"
                                ><span class="whitespace-nowrap">
                                    {{ formatDateOnly(drinkData.updated_at) }}
                                </span>
                            </TableCell>
                            <TableCell v-else
                                ><span class="whitespace-nowrap">
                                    {{ formatDateOnly(drinkData.deleted_at) }}
                                </span>
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger
                                        v-if="
                                            can(
                                                DrinkPermissionEnum.DRINK_UPDATE,
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
                                                    DrinkPermissionEnum.DRINK_RESTORE,
                                                )
                                            "
                                            @click="
                                                openConfirmModal(
                                                    t(
                                                        'drink.messages.confirmRestore',
                                                    ),
                                                    'info',
                                                    () =>
                                                        restoreDrink(
                                                            drinkData.id,
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
                                                    DrinkPermissionEnum.DRINK_DELETE,
                                                )
                                            "
                                            @click="
                                                openConfirmModal(
                                                    t(
                                                        'drink.messages.confirmDelete',
                                                    ),
                                                    'destructive',
                                                    () =>
                                                        deleteDrink(
                                                            drinkData.id,
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
                                                    drink.edit(drinkData.id).url
                                                "
                                                class="flex gap-2"
                                            >
                                                <SquarePenIcon /> Edit
                                            </a>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="
                                                can(
                                                    DrinkPermissionEnum.DRINK_DELETE,
                                                )
                                            "
                                            @click="
                                                openConfirmModal(
                                                    t(
                                                        'drink.messages.confirmMoveToBin',
                                                    ),
                                                    'destructive',
                                                    () =>
                                                        moveToBin(drinkData.id),
                                                )
                                            "
                                        >
                                            <TrashIcon />
                                            {{ t('admin.buttons.moveToBin') }}
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="
                                                drinkData.is_available ===
                                                DrinkAvailabilityEnum.AVAILABLE
                                            "
                                            @click="
                                                openConfirmModal(
                                                    t(
                                                        'drink.messages.confirmAvailable',
                                                    ),
                                                    'info',
                                                    () =>
                                                        toggleAvailability(
                                                            drinkData.id,
                                                        ),
                                                )
                                            "
                                        >
                                            <CircleXIcon />
                                            {{ t('admin.status.unavailable') }}
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-else
                                            @click="
                                                openConfirmModal(
                                                    t(
                                                        'drink.messages.confirmUnavailable',
                                                    ),
                                                    'info',
                                                    () =>
                                                        toggleAvailability(
                                                            drinkData.id,
                                                        ),
                                                )
                                            "
                                        >
                                            <CircleCheckBigIcon />
                                            {{ t('admin.status.available') }}
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>

                        <tr ref="sentinel" v-if="nextCursor">
                            <td
                                :colspan="5"
                                class="py-2 text-center text-muted-foreground"
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
                    v-if="drinks.length === 0 && !isLoading"
                    class="flex h-24 items-center justify-center text-muted-foreground sm:h-32"
                >
                    {{ t('drink.messages.noDrink') }}
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
