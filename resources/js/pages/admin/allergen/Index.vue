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
import AdminLayout from '@/layouts/AdminLayout.vue';
import allergen from '@/routes/allergen';
import type { BreadcrumbItem, CursorPaginated, FilterType } from '@/types';
import { AllergenType } from '@/types/allergen';
import { Head, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import axios from 'axios';
import {
    EllipsisVerticalIcon,
    RotateCcwIcon,
    SearchIcon,
    SquarePenIcon,
    SquarePlusIcon,
    TrashIcon,
    XIcon,
} from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Allergens',
        href: allergen.index().url,
    },
];

const confirmModalOpen = ref<boolean>(false);
const confirmModalMessage = ref<string>('');
const confirmModalType = ref<'destructive' | 'info'>('info');
const confirmModalAction = ref<() => void>(() => {});
const allergens = ref<AllergenType[]>([]);
const nextCursor = ref<string | null>(null);
const isLoading = ref<boolean>(false);
const sentinel = ref<HTMLElement | null>(null);
const scrollContainer = ref<HTMLElement | null>(null);
const observer = ref<IntersectionObserver | null>(null);
const filter = ref<FilterType>('all');
const search = ref<string | null>(null);

const moveToBin = async (id: number) => {
    try {
        const response = await axios.delete(allergen.destroy(id).url);

        allergens.value = allergens.value.filter((d) => d.id !== id);

        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error('Failed to move allergen to bin');
        }
    } finally {
        closeConfirmModal();
    }
};

const restoreAllergen = async (id: number) => {
    try {
        const response = await axios.post(allergen.restore(id).url);

        allergens.value = allergens.value.filter((d) => d.id !== id);

        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error('Failed to restore allergen');
        }
    } finally {
        closeConfirmModal();
    }
};

const deleteAllergen = async (id: number) => {
    try {
        const response = await axios.delete(allergen.forceDelete(id).url);

        allergens.value = allergens.value.filter((d) => d.id !== id);

        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error('Failed to delete allergen');
        }
    } finally {
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
    router.visit(allergen.edit(id).url);
};

const loadAllergens = async () => {
    if (isLoading.value) return;

    isLoading.value = true;

    try {
        const response = await axios.get<CursorPaginated<AllergenType>>(
            '/allergens/get-allergens',
            {
                params: {
                    cursor: nextCursor.value,
                    filter: filter.value,
                    search: search.value,
                },
            },
        );
        const newAllergen = response.data;
        allergens.value.push(...newAllergen.data);
        nextCursor.value = newAllergen.next_cursor;
    } catch (error) {
        console.log(error);
        toast.error('Failed to load more allergens.');
    } finally {
        isLoading.value = false;
    }
};

const applyFilters = () => {
    allergens.value = [];
    nextCursor.value = null;

    observer.value?.disconnect();

    loadAllergens();

    if (sentinel.value) {
        observer.value?.observe(sentinel.value);
    }
};

const changeFilter = (value: FilterType) => {
    filter.value = value;
    applyFilters();
};

const resetAllFilters = () => {
    filter.value = 'all';
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
    loadAllergens();

    observer.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    loadAllergens();
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
    <Head title="Allergens" />

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
                                    All
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
                                    Deleted
                                </Button>
                            </div>

                            <div class="relative flex-1 sm:w-52 sm:flex-none">
                                <SearchIcon
                                    class="absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2 text-gray-400"
                                />
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search dishes..."
                                    class="h-8 w-full rounded-md border border-gray-200 bg-white pr-3 pl-8 text-sm text-gray-700 shadow-sm transition-all placeholder:text-gray-400 hover:border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none sm:h-9 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:placeholder:text-gray-500 dark:hover:border-gray-600"
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
                    </div>

                    <div class="ml-auto flex">
                        <a :href="allergen.create().url" class="inline-block">
                            <Button
                                variant="outline"
                                size="sm"
                                class="group cursor-pointer shadow-sm transition-all duration-300 hover:bg-gray-100 hover:shadow-md sm:h-9 dark:hover:bg-gray-800"
                            >
                                <SquarePlusIcon
                                    class="mr-2 h-5 w-5 transition-transform duration-300 group-hover:scale-110"
                                />
                                Add
                            </Button>
                        </a>
                    </div>
                </div>

                <div
                    v-if="filter !== 'all' || search"
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
                        class="ml-auto cursor-pointer hidden text-xs text-gray-500 hover:text-gray-700 sm:inline-block dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        Clear all
                    </button>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Created at</TableHead>
                            <TableHead v-if="filter !== 'deleted'"
                                >Updated at</TableHead
                            >
                            <TableHead v-else>Deleted at</TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="allergenData in allergens"
                            :key="allergenData.id"
                            @dblclick="goToEdit(allergenData.id)"
                            class="cursor-pointer"
                        >
                            <TableCell class="flex items-center gap-4">
                                <img
                                    :src="allergenData.main_image"
                                    :alt="allergenData.name"
                                    class="hidden h-8 w-8 rounded-lg md:block"
                                />
                                <span class="whitespace-nowrap">
                                    {{ allergenData.name }}
                                </span>
                            </TableCell>
                            <TableCell
                                ><span class="whitespace-nowrap">{{
                                    allergenData.created_at
                                }}</span></TableCell
                            >
                            <TableCell v-if="filter !== 'deleted'"
                                ><span class="whitespace-nowrap">{{
                                    allergenData.updated_at
                                }}</span> </TableCell
                            ><TableCell v-else
                                ><span class="whitespace-nowrap">{{
                                    allergenData.deleted_at
                                }}</span>
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
                                                    `Are you sure you want to restore this allergen?`,
                                                    'info',
                                                    () =>
                                                        restoreAllergen(
                                                            allergenData.id,
                                                        ),
                                                )
                                            "
                                        >
                                            <RotateCcwIcon /> Restore
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="
                                                openConfirmModal(
                                                    'Are you sure you want to delete this allergen?',
                                                    'destructive',
                                                    () =>
                                                        deleteAllergen(
                                                            allergenData.id,
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
                                                    allergen.edit(
                                                        allergenData.id,
                                                    ).url
                                                "
                                                class="flex gap-2"
                                            >
                                                <SquarePenIcon /> Edit
                                            </a>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="
                                                openConfirmModal(
                                                    'Are you sure you want to move this allergen to bin?',
                                                    'destructive',
                                                    () =>
                                                        moveToBin(
                                                            allergenData.id,
                                                        ),
                                                )
                                            "
                                        >
                                            <TrashIcon /> Move to bin
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="allergens.length === 0">
                            <TableCell
                                class="text-center text-gray-300"
                                :colspan="4"
                                >No allergen yet</TableCell
                            >
                        </TableRow>
                    </TableBody>
                </Table>

                <ConfirmModal
                    :open="confirmModalOpen"
                    :onClose="closeConfirmModal"
                    :message="confirmModalMessage"
                    :type="confirmModalType"
                    @confirm="confirmModalAction"
                />
            </div>
        </div>
    </AdminLayout>
</template>
