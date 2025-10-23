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
import ingredient from '@/routes/ingredient';
import type { BreadcrumbItem, Paginated } from '@/types';
import { DishType } from '@/types/dish';
import { IngredientType } from '@/types/ingredient';
import { Head, useForm } from '@inertiajs/vue3';
import {
    EllipsisVerticalIcon,
    EyeIcon,
    SquarePenIcon,
    SquarePlusIcon,
    TrashIcon,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    ingredients: Paginated<IngredientType>;
}>();

console.log(props.ingredients);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ingredients',
        href: ingredient.index().url,
    },
];

const ingredientForm = useForm({});

const deleteIngredient = (id: number) => {
    if (confirm('Are you sure you want to delete ingredient?')) {
        ingredientForm.delete(ingredient.destroy(id).url, {
            onSuccess: () => {
                toast.success('Ingredient successfully deleted.');
            },
        });
    }
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
                    <a :href="ingredient.create().url">
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
                            <TableHead>Allergen</TableHead>
                            <TableHead>Created at</TableHead>
                            <TableHead>Updated at</TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="ingredientData in props.ingredients.data"
                            :key="ingredientData.id"
                        >
                            <TableCell class="flex items-center gap-4">
                                <img
                                    :src="ingredientData.image"
                                    :alt="ingredientData.name"
                                    class="hidden h-8 w-8 rounded-lg md:block"
                                />
                                <span class="whitespace-nowrap">
                                    {{ ingredientData.name }}
                                </span>
                            </TableCell>
                            <TableCell
                                >{{ ingredientData.allergen ? ingredientData.allergen : '-' }}</TableCell
                            >
                            <TableCell
                                ><span class="whitespace-nowrap">{{
                                    ingredientData.created_at
                                }}</span></TableCell
                            >
                            <TableCell
                                ><span class="whitespace-nowrap">{{
                                    ingredientData.updated_at
                                }}</span>
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger>
                                        <EllipsisVerticalIcon />
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent>
                                        <DropdownMenuItem>
                                            <a :href="ingredient.edit(ingredientData.id).url" class="flex gap-2">
                                                <SquarePenIcon /> Edit
                                            </a>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem>
                                            <EyeIcon /> View
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="deleteIngredient(ingredientData.id)">
                                            <TrashIcon /> Delete
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
