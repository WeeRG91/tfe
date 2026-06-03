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
import { BreadcrumbItem, CursorPaginated, type FilterType } from '@/types';
import { MeatType } from '@/types/meat';
import { Head, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import axios from 'axios';
import {
    EllipsisVerticalIcon,
    Loader,
    RotateCcwIcon,
    SearchIcon,
    SquarePenIcon,
    SquarePlusIcon,
    TrashIcon,
    XIcon,
} from 'lucide-vue-next';
import { onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import meat from '@/routes/admin/meat';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Meats',
        href: meat.index().url,
    },
];

const confirmModalOpen = ref<boolean>(false);
const confirmModalMessage = ref<string>('');
const confirmModalType = ref<'destructive' | 'info'>('info');
const confirmModalAction = ref<() => void>(() => {});
const meats = ref<MeatType[]>([]);
const nextCursor = ref<string | null>(null);
const isLoading = ref<boolean>(false);
const sentinel = ref<HTMLElement | null>(null);
const scrollContainer = ref<HTMLElement | null>(null);
const observer = ref<IntersectionObserver | null>(null);
const filter = ref<FilterType>('all');
const search = ref<string | null>(null);

const moveToBin = async (id: number) => {
    isLoading.value = true;

    try {
        const response = await axios.delete(meat.destroy(id).url);

        meats.value = meats.value.filter((d) => d.id !== id);

        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error('Failed to move meat to bin');
        }
    } finally {
        isLoading.value = false;
        closeConfirmModal();
    }
};

const restoreMeat = async (id: number) => {
    isLoading.value = true;

    try {
        const response = await axios.post(meat.restore(id).url);

        meats.value = meats.value.filter((d) => d.id !== id);

        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error('Failed to restore meat');
        }
    } finally {
        isLoading.value = false;
        closeConfirmModal();
    }
};

const deleteMeat = async (id: number) => {
    isLoading.value = true;

    try {
        const response = await axios.delete(meat.forceDelete(id).url);

        meats.value = meats.value.filter((d) => d.id !== id);

        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error('Failed to delete meat');
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
    router.visit(meat.edit(id).url);
};

const loadMeats = async () => {
    if (isLoading.value) return;

    isLoading.value = true;

    try {
        const response = await axios.get<CursorPaginated<MeatType>>(
            meat.getMeats().url,
            {
                params: {
                    cursor: nextCursor.value,
                    filter: filter.value,
                    search: search.value,
                },
            },
        );
        const newMeats = response.data;
        meats.value.push(...newMeats.data);
        nextCursor.value = newMeats.next_cursor;
    } catch (error) {
        console.log(error);
        toast.error('Failed to load more meats.');
    } finally {
        isLoading.value = false;
    }
};

const applyFilters = () => {
    meats.value = [];
    nextCursor.value = null;

    observer.value?.disconnect();

    loadMeats();

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
    loadMeats();

    observer.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    loadMeats();
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
</script>

<template>
    <Head title="Meats" />

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
                        <a :href="meat.create().url" class="inline-block">
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
                        class="ml-auto hidden cursor-pointer text-xs text-gray-500 hover:text-gray-700 sm:inline-block dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        Clear all
                    </button>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Extra price</TableHead>
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
                            v-for="meatData in meats"
                            :key="meatData.id"
                            @dblclick="goToEdit(meatData.id)"
                            class="cursor-pointer"
                        >
                            <TableCell class="flex items-center gap-4">
                                <img
                                    :src="meatData.main_image"
                                    :alt="meatData.name"
                                    class="hidden h-8 w-8 rounded-lg md:block"
                                />
                                <span class="whitespace-nowrap">
                                    {{ meatData.name }}
                                </span>
                            </TableCell>
                            <TableCell class="group relative">
                                <span class="whitespace-nowrap">
                                    +{{ meatData.extra_price }} €
                                </span>
                            </TableCell>
                            <TableCell
                                ><span class="whitespace-nowrap">
                                    {{ meatData.created_at }}</span
                                >
                            </TableCell>
                            <TableCell v-if="filter !== 'deleted'"
                                ><span class="whitespace-nowrap">
                                    {{ meatData.updated_at }}
                                </span>
                            </TableCell>
                            <TableCell v-else
                                ><span class="whitespace-nowrap">
                                    {{ meatData.deleted_at }}
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
                                                    `Are you sure you want to restore this meat?`,
                                                    'info',
                                                    () =>
                                                        restoreMeat(
                                                            meatData.id,
                                                        ),
                                                )
                                            "
                                        >
                                            <RotateCcwIcon /> Restore
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="
                                                openConfirmModal(
                                                    'Are you sure you want to delete this meat?',
                                                    'destructive',
                                                    () =>
                                                        deleteMeat(meatData.id),
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
                                                    meat.edit(meatData.id).url
                                                "
                                                class="flex gap-2"
                                            >
                                                <SquarePenIcon /> Edit
                                            </a>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="
                                                openConfirmModal(
                                                    'Are you sure you want to move this meat to bin?',
                                                    'destructive',
                                                    () =>
                                                        moveToBin(meatData.id),
                                                )
                                            "
                                        >
                                            <TrashIcon /> Move to bin
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
                    v-if="meats.length === 0 && !isLoading"
                    class="flex h-24 items-center justify-center text-gray-300 sm:h-32"
                >
                    No meat yet
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
