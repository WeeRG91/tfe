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
import dish from '@/routes/dish';
import type { BreadcrumbItem, CursorPaginated } from '@/types';
import { DishType } from '@/types/dish';
import { Head, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import {
    CircleCheckBigIcon,
    CircleXIcon,
    EllipsisVerticalIcon,
    SquarePenIcon,
    SquarePlusIcon,
    TrashIcon,
} from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dishes',
        href: dish.index().url,
    },
];

const confirmModalOpen = ref<boolean>(false);
const confirmModalMessage = ref<string>('');
const confirmModalType = ref<'destructive' | 'info'>('info');
const confirmModalAction = ref<() => void>(() => {});
const dishes = ref<DishType[]>([]);
const nextCursor = ref<string | null>(null);
const isLoading = ref<boolean>(false);
const sentinel = ref<HTMLElement | null>(null);
const observer = ref<IntersectionObserver | null>(null);

const dishForm = useForm({});

const deleteDish = (id: number) => {
    dishForm.delete(dish.destroy(id).url, {
        onSuccess: () => {
            closeConfirmModal();
            toast.success('Dish successfully deleted.');
        },
        onError: (error) => {
            if (error.message) toast.error(error.meessage);

            toast.error('Something went wrong. Please try again.');
        },
    });
};

const toggleAvailability = (id: number) => {
    dishForm.patch(dish.available(id).url, {
        onSuccess: () => {
            closeConfirmModal();
            toast.success('Dish availability updated successfully.');
        },
        onError: (error) => {
            if (error.message) toast.error(error.meessage);

            toast.error('Something went wrong. Please try again.');
        },
    });
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
    router.visit(dish.edit(id).url);
};

const loadDishes = async () => {
    if (isLoading.value) return;

    isLoading.value = true;

    try {
        const response = await axios.get<CursorPaginated<DishType>>(
            '/dishes/get-dishes',
            {
                params: { cursor: nextCursor.value },
            },
        );
        const newDishes = response.data;
        dishes.value.push(...newDishes.data);
        nextCursor.value = newDishes.next_cursor;
    } catch (error) {
        console.log(error);
        toast.error('Failed to load more dishes.');
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    loadDishes();

    observer.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    loadDishes();
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
    if (observer.value && sentinel.value) observer.value.unobserve(sentinel.value);
});
</script>

<template>
    <Head title="Dishes" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-t-xl p-4"
        >
            <div
                class="relative h-full flex-1 overflow-hidden rounded-t-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <div class="m-2 flex cursor-pointer justify-end">
                    <a :href="dish.create().url">
                        <Button
                            variant="outline"
                            class="cursor-pointer text-gray-500 hover:text-gray-700 dark:hover:text-gray-400"
                        >
                            <SquarePlusIcon class="h-6 w-6" />
                            Add
                        </Button>
                    </a>
                </div>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Category</TableHead>
                            <TableHead class="hidden md:table-cell"
                                >price</TableHead
                            >
                            <TableHead>Availability</TableHead>
                            <TableHead class="hidden md:table-cell"
                                >Created at</TableHead
                            >
                            <TableHead>Updated at</TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="dishData in dishes"
                            :key="dishData.id"
                            @dblclick="goToEdit(dishData.id)"
                            class="cursor-pointer"
                        >
                            <TableCell class="flex items-center gap-4">
                                <img
                                    :src="dishData.main_image"
                                    :alt="dishData.name"
                                    class="hidden h-8 w-8 rounded-lg md:block"
                                />
                                <span class="whitespace-nowrap">
                                    {{ dishData.name }}
                                </span>
                            </TableCell>
                            <TableCell>
                                <Badge
                                    class="text-white/80"
                                    :class="dishData.category.color"
                                    >{{ dishData.category.label }}</Badge
                                >
                            </TableCell>
                            <TableCell class="hidden text-nowrap md:table-cell"
                                >{{ dishData.price }} €</TableCell
                            >
                            <TableCell>
                                <Badge
                                    v-if="dishData.is_available === 'Available'"
                                    class="bg-green-500 text-white"
                                    >{{ dishData.is_available }}</Badge
                                >
                                <Badge v-else class="bg-red-500 text-white">
                                    {{ dishData.is_available }}
                                </Badge>
                            </TableCell>
                            <TableCell class="hidden md:table-cell"
                                ><span class="whitespace-nowrap">
                                    {{ dishData.created_at }}
                                </span>
                            </TableCell>
                            <TableCell
                                ><span class="whitespace-nowrap">
                                    {{ dishData.updated_at }}
                                </span>
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger>
                                        <EllipsisVerticalIcon />
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent>
                                        <DropdownMenuItem>
                                            <a
                                                :href="
                                                    dish.edit(dishData.id).url
                                                "
                                                class="flex gap-2"
                                            >
                                                <SquarePenIcon /> Edit
                                            </a>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="
                                                openConfirmModal(
                                                    'Are you sure you want to delete this dish?',
                                                    'destructive',
                                                    () =>
                                                        deleteDish(dishData.id),
                                                )
                                            "
                                        >
                                            <TrashIcon /> Delete
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="
                                                dishData.is_available ===
                                                'Available'
                                            "
                                            @click="
                                                openConfirmModal(
                                                    'Are you sure you want to mark this dish as unavailable?',
                                                    'info',
                                                    () =>
                                                        toggleAvailability(
                                                            dishData.id,
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
                                                    'Are you sure you want to mark this dish as available?',
                                                    'info',
                                                    () =>
                                                        toggleAvailability(
                                                            dishData.id,
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
                                Loading more dishes...
                            </td>
                        </tr>

                        <TableRow v-if="dishes.length === 0">
                            <TableCell
                                class="text-center text-gray-300"
                                :colspan="7"
                                >No dish yet</TableCell
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
