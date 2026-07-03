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
import AdminLayout from '@/layouts/AdminLayout.vue';
import drink from '@/routes/admin/drink';
import type { BreadcrumbItem, CursorPaginated, FilterType } from '@/types';
import { CategoryOptionType } from '@/types/category';
import { DishType } from '@/types/dish';
import { DrinkAvailabilityEnum, DrinkType } from '@/types/drink';
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
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    categories: CategoryOptionType[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Drinks',
        href: drink.index().url,
    },
];

const confirmModalOpen = ref<boolean>(false);
const confirmModalMessage = ref<string>('');
const confirmModalType = ref<'destructive' | 'info'>('info');
const confirmModalAction = ref<() => void>(() => {});
const drinks = ref<DrinkType[]>([]);
const nextCursor = ref<string | null>(null);
const isLoading = ref<boolean>(false);
const sentinel = ref<HTMLElement | null>(null);
const scrollContainer = ref<HTMLElement | null>(null);
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
            toast.error('Failed to update drink availability');
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
            toast.error('Failed to move dish to bin');
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
            toast.error('Failed to restore drink');
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
            toast.error('Failed to delete drink');
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
    router.visit(drink.edit(id).url);
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
        toast.error('Failed to load more drinks.');
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
                class="relative h-full flex-1 overflow-hidden rounded-t-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <div
                    class="flex flex-col gap-2 border-b bg-gradient-to-b from-white to-gray-50/50 p-4 md:flex-row md:items-center md:justify-between dark:from-gray-900 dark:to-gray-900/50"
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
                                :class="
                                    filter !== 'all' &&
                                    'hover:bg-gray-100 dark:hover:bg-gray-800'
                                "
                            >
                                All
                            </Button>

                            <Button
                                size="sm"
                                :variant="
                                    filter === 'available'
                                        ? 'default'
                                        : 'outline'
                                "
                                @click="changeFilter('available')"
                                class="cursor-pointer text-xs shadow-sm transition-all duration-200 sm:h-9 sm:text-sm"
                                :class="[
                                    filter === 'available'
                                        ? 'bg-green-600 hover:bg-green-700 dark:bg-green-600'
                                        : 'hover:bg-gray-100 dark:hover:bg-gray-800',
                                ]"
                            >
                                Available
                            </Button>

                            <Button
                                size="sm"
                                :variant="
                                    filter === 'unavailable'
                                        ? 'default'
                                        : 'outline'
                                "
                                @click="changeFilter('unavailable')"
                                class="cursor-pointer text-xs shadow-sm transition-all duration-200 sm:h-9 sm:text-sm"
                                :class="[
                                    filter === 'unavailable'
                                        ? 'bg-amber-600 hover:bg-amber-700 dark:bg-amber-600'
                                        : 'hover:bg-gray-100 dark:hover:bg-gray-800',
                                ]"
                            >
                                Unavailable
                            </Button>

                            <Button
                                size="sm"
                                :variant="
                                    filter === 'deleted' ? 'default' : 'outline'
                                "
                                @click="changeFilter('deleted')"
                                class="cursor-pointer text-xs shadow-sm transition-all duration-200 sm:h-9 sm:text-sm"
                                :class="[
                                    filter === 'deleted'
                                        ? 'bg-rose-600 hover:bg-rose-700 dark:bg-rose-600'
                                        : 'hover:bg-gray-100 dark:hover:bg-gray-800',
                                ]"
                            >
                                Deleted
                            </Button>
                        </div>

                        <div
                            class="flex w-full flex-wrap items-center gap-2 sm:w-auto"
                        >
                            <div class="relative flex-1 sm:w-52 sm:flex-none">
                                <SearchIcon
                                    class="absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2 text-gray-400"
                                />
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search dishes..."
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

                            <div
                                class="flex w-full flex-row items-center gap-2 sm:flex-1"
                            >
                                <div
                                    class="relative flex-1 sm:w-52 sm:flex-none"
                                >
                                    <select
                                        v-model="category"
                                        @change="applyFilters"
                                        class="h-8 w-full appearance-none rounded-md border border-gray-200 bg-white px-3 pr-8 text-sm text-gray-700 shadow-sm transition-all hover:border-gray-300 focus:outline-none sm:h-9 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-gray-600"
                                    >
                                        <option :value="null">
                                            All categories
                                        </option>
                                        <option
                                            v-for="cat in props.categories"
                                            :key="cat.value"
                                            :value="cat.value"
                                        >
                                            {{ cat.label }}
                                        </option>
                                    </select>
                                    <ChevronDownIcon
                                        class="pointer-events-none absolute top-1/2 right-2 h-4 w-4 -translate-y-1/2 text-gray-400"
                                    />
                                </div>

                                <div class="flex-none sm:ml-auto sm:hidden">
                                    <a
                                        :href="drink.create().url"
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
                                            Add
                                        </Button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="hidden sm:ml-auto sm:flex">
                        <a :href="drink.create().url" class="inline-block">
                            <Button
                                variant="outline"
                                size="sm"
                                class="group cursor-pointer shadow-sm transition-all duration-300 hover:bg-gray-100 hover:shadow-md sm:h-9 dark:hover:bg-gray-800"
                            >
                                <Plus
                                    class="mr-2 h-5 w-5 transition-transform duration-300 group-hover:scale-110"
                                />
                                Add
                            </Button>
                        </a>
                    </div>
                </div>

                <div
                    v-if="filter !== 'all' || category || search"
                    class="flex flex-wrap items-center gap-2 border-t bg-gray-50/50 px-4 py-2 text-sm dark:bg-gray-900/50"
                >
                    <span
                        class="hidden text-gray-500 sm:inline-block dark:text-gray-400"
                        >Active filters:</span
                    >
                    <span
                        v-if="filter !== 'all'"
                        class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300"
                    >
                        {{ filter }}
                        <button
                            @click="changeFilter('all')"
                            class="ml-1 cursor-pointer hover:text-blue-600"
                        >
                            ×
                        </button>
                    </span>
                    <span
                        v-if="category"
                        class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900/30 dark:text-purple-300"
                    >
                        {{ getCategoryLabel(category) }}
                        <button
                            @click="
                                category = null;
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
                        Clear all
                    </button>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="text-nowrap">Name</TableHead>
                            <TableHead class="text-nowrap">Category</TableHead>
                            <TableHead class="hidden text-nowrap md:table-cell"
                                >price</TableHead
                            >
                            <TableHead>Availability</TableHead>
                            <TableHead class="hidden text-nowrap md:table-cell"
                                >Created at</TableHead
                            >
                            <TableHead
                                v-if="filter !== 'deleted'"
                                class="text-nowrap"
                                >Updated at</TableHead
                            >
                            <TableHead v-else class="text-nowrap"
                                >Deleted at</TableHead
                            >
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
                                    class="text-white/80"
                                    :class="drinkData.category.color"
                                    >{{ drinkData.category.label }}</Badge
                                >
                            </TableCell>
                            <TableCell class="hidden md:table-cell"
                                >{{ drinkData.price }} €</TableCell
                            >
                            <TableCell>
                                <Badge
                                    v-if="
                                        drinkData.is_available === 'Available'
                                    "
                                    class="bg-green-500 text-white"
                                    >{{ drinkData.is_available }}</Badge
                                >
                                <Badge v-else class="bg-red-500 text-white">
                                    {{ drinkData.is_available }}
                                </Badge>
                            </TableCell>
                            <TableCell class="hidden md:table-cell"
                                ><span class="whitespace-nowrap">
                                    {{ drinkData.created_at }}
                                </span>
                            </TableCell>
                            <TableCell v-if="filter !== 'deleted'"
                                ><span class="whitespace-nowrap">
                                    {{ drinkData.updated_at }}
                                </span>
                            </TableCell>
                            <TableCell v-else
                                ><span class="whitespace-nowrap">
                                    {{ drinkData.deleted_at }}
                                </span>
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger>
                                        <EllipsisVerticalIcon />
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        v-if="filter === 'deleted'"
                                    >
                                        <DropdownMenuItem
                                            @click="
                                                openConfirmModal(
                                                    `Are you sure you want to restore this drink?`,
                                                    'info',
                                                    () =>
                                                        restoreDrink(
                                                            drinkData.id,
                                                        ),
                                                )
                                            "
                                        >
                                            <RotateCcwIcon /> Restore
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="
                                                openConfirmModal(
                                                    'Are you sure you want to delete this drink?',
                                                    'destructive',
                                                    () =>
                                                        deleteDrink(
                                                            drinkData.id,
                                                        ),
                                                )
                                            "
                                        >
                                            <TrashIcon /> Delete
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
                                            @click="
                                                openConfirmModal(
                                                    'Are you sure you want to move this dish to bin?',
                                                    'destructive',
                                                    () =>
                                                        moveToBin(drinkData.id),
                                                )
                                            "
                                        >
                                            <TrashIcon /> Move to bin
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="
                                                drinkData.is_available ===
                                                DrinkAvailabilityEnum.AVAILABLE
                                            "
                                            @click="
                                                openConfirmModal(
                                                    'Are you sure you want to mark this drink as unavailable?',
                                                    'info',
                                                    () =>
                                                        toggleAvailability(
                                                            drinkData.id,
                                                        ),
                                                )
                                            "
                                        >
                                            <CircleXIcon /> Unavailable
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-else
                                            @click="
                                                openConfirmModal(
                                                    'Are you sure you want to mark this drink as available?',
                                                    'info',
                                                    () =>
                                                        toggleAvailability(
                                                            drinkData.id,
                                                        ),
                                                )
                                            "
                                        >
                                            <CircleCheckBigIcon /> Available
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
                    v-if="drinks.length === 0 && !isLoading"
                    class="flex h-24 items-center justify-center text-gray-300 sm:h-32"
                >
                    No drink yet
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
