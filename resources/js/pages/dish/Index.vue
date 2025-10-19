<script setup lang="ts">
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
import { Head } from '@inertiajs/vue3';
import {
    CircleCheckBigIcon,
    CircleXIcon,
    EllipsisVerticalIcon,
    SquarePenIcon,
    SquarePlusIcon,
    TrashIcon,
} from 'lucide-vue-next';

const props = defineProps<{
    dishes: Paginated<DishType>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dishes',
        href: dish.index().url,
    },
];
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
                            <TableHead class="hidden md:table-cell">price</TableHead>
                            <TableHead>Availability</TableHead>
                            <TableHead class="hidden md:table-cell">Created at</TableHead>
                            <TableHead>Updated at</TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="dishData in props.dishes.data"
                            :key="dishData.id"
                        >
                            <TableCell class="flex items-center gap-4"
                                ><img
                                    :src="dishData.image"
                                    :alt="dishData.name"
                                    class="hidden h-8 w-8 rounded-full md:block"
                                />
                                <span class="whitespace-nowrap">{{ dishData.name }}</span></TableCell
                            >
                            <TableCell>
                                <Badge
                                    class="text-white/80"
                                    :class="
                                        dishData.category.color
                                    "
                                    >{{ dishData.category.label }}</Badge
                                >
                            </TableCell>
                            <TableCell class="hidden md:table-cell">{{ dishData.price }} €</TableCell>
                            <TableCell>
                                <Badge
                                    v-if="dishData.is_available === 'Available'"
                                    class="bg-green-500 text-white"
                                    >{{ dishData.is_available }}</Badge
                                >
                                <Badge v-else class="bg-red-500 text-white">{{
                                    dishData.is_available
                                }}</Badge>
                            </TableCell>
                            <TableCell class="hidden md:table-cell"><span class="whitespace-nowrap">{{ dishData.created_at }}</span></TableCell>
                            <TableCell><span class="whitespace-nowrap">{{ dishData.updated_at }}</span></TableCell>
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
                                        <DropdownMenuItem>
                                            <TrashIcon /> Delete
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="
                                                dishData.is_available ===
                                                'Available'
                                            "
                                        >
                                            <CircleXIcon /> Unavailable
                                        </DropdownMenuItem>
                                        <DropdownMenuItem v-else>
                                            <CircleCheckBigIcon /> Available
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AdminLayout>
</template>
