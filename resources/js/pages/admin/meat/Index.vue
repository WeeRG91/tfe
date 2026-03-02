<script setup lang="ts">
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
import { BreadcrumbItem, Paginated } from '@/types';
import { MeatType } from '@/types/meat';
import { Head } from '@inertiajs/vue3';
import {
    EllipsisVerticalIcon,
    EyeIcon,
    SquarePenIcon,
    SquarePlusIcon,
    TrashIcon,
} from 'lucide-vue-next';
import meat from '@/routes/meat';

const props = defineProps<{
    meats: Paginated<MeatType>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Meats',
        href: meat.index().url,
    },
];
</script>

<template>
    <Head title="Meats" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <div class="m-2 flex cursor-pointer justify-end">
                    <a :href="meat.create().url">
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
                            <TableHead>Extra price</TableHead>
                            <TableHead>Created at</TableHead>
                            <TableHead>Updated at</TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="meatData in props.meats.data"
                            :key="meatData.id"
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
                                <span class="whitespace-nowrap">{{
                                    meatData.extra_price
                                }}</span>
                            </TableCell>
                            <TableCell
                                ><span class="whitespace-nowrap">{{
                                    meatData.created_at
                                }}</span></TableCell
                            >
                            <TableCell
                                ><span class="whitespace-nowrap">{{
                                    meatData.updated_at
                                }}</span>
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger>
                                        <EllipsisVerticalIcon />
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent>
                                        <DropdownMenuItem>
                                            <a
                                                href="#"
                                                class="flex gap-2"
                                            >
                                                <SquarePenIcon /> Edit
                                            </a>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem>
                                            <EyeIcon /> View
                                        </DropdownMenuItem>
                                        <DropdownMenuItem>
                                            <TrashIcon /> Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="props.meats.data.length === 0">
                            <TableCell
                                class="text-center text-gray-300"
                                :colspan="5"
                                >No meat yet</TableCell
                            >
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AdminLayout>
</template>
