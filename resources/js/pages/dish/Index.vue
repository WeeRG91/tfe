<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import dish from '@/routes/dish';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { DishType } from '@/types/dish';

const props = defineProps<{
    dishes: DishType[]
}>();

console.log(props.dishes);

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
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Category</TableHead>
                            <TableHead>price</TableHead>
                            <TableHead>Available</TableHead>
                            <TableHead>Created at</TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="dish in props.dishes.data" :key="dish.id">
                            <TableCell>{{ dish.name }}</TableCell>
                            <TableCell>{{ dish.category}}</TableCell>
                            <TableCell>{{ dish.price }}</TableCell>
                            <TableCell>{{ dish.is_available }}</TableCell>
                            <TableCell>{{ dish.created_at }}</TableCell>
                            <TableCell></TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AdminLayout>
</template>
