<script setup lang="ts">
import DishDetailModal from '@/components/dish/DishDetailModal.vue';
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
import type { BreadcrumbItem } from '@/types';
import { DishType, Paginated } from '@/types/dish';
import { Head, useForm } from '@inertiajs/vue3';
import {
    CircleCheckBigIcon,
    CircleXIcon,
    EllipsisVerticalIcon,
    EyeIcon,
    SquarePenIcon,
    SquarePlusIcon,
    TrashIcon,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    dishes: Paginated<DishType>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dishes',
        href: dish.index().url,
    },
];

const selectedDish = ref<DishType | null>(null);
const modalOpen = ref<boolean>(false);

const dishForm = useForm({});

const deleteDish = (id: number) => {
    if (confirm('Are you sure you want to delete dish?')) {
        dishForm.delete(dish.destroy(id).url, {
            onSuccess: () => {
                toast.success('Dish successfully deleted.');
            },
        });
    }
};

const toggleAvailability = (id: number) => {
    if (confirm('Change availability of this dish?')) {
        dishForm.patch(dish.available(id).url, {
            onSuccess: () => {
                toast.success('Dish availability updated successfully.');
            },
        });
    }
};

const openDishDetail = (dish: DishType) => {
    selectedDish.value = dish;
    modalOpen.value = true;
};

const closeDishDetail = () => {
    modalOpen.value = false;
};
</script>

<template>
    <Head title="Dishes" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <div class="m-2 flex cursor-pointer justify-end">
                    <a :href="dish.create().url">
                        <Button
                            variant="outline"
                            class="cursor-pointer text-gray-500 hover:text-gray-800"
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
                            v-for="dishData in props.dishes.data"
                            :key="dishData.id"
                            @dblclick="openDishDetail(dishData)"
                        >
                            <TableCell class="flex items-center gap-4">
                                <img
                                    :src="dishData.image"
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
                            <TableCell class="hidden md:table-cell"
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
                                            @click="openDishDetail(dishData)"
                                        >
                                            <EyeIcon /> View
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="deleteDish(dishData.id)"
                                        >
                                            <TrashIcon /> Delete
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="
                                                dishData.is_available ===
                                                'Available'
                                            "
                                            @click="
                                                toggleAvailability(dishData.id)
                                            "
                                        >
                                            <CircleXIcon /> Unavailable
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-else
                                            @click="
                                                toggleAvailability(dishData.id)
                                            "
                                        >
                                            <CircleCheckBigIcon /> Available
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <DishDetailModal
                    :open="modalOpen"
                    :selectedDish="selectedDish"
                    :onClose="closeDishDetail"
                />
            </div>
        </div>
    </AdminLayout>
</template>
