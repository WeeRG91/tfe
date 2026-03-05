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
import ingredient from '@/routes/ingredient';
import type { BreadcrumbItem, Paginated } from '@/types';
import { IngredientType } from '@/types/ingredient';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    EllipsisVerticalIcon,
    SquarePenIcon,
    SquarePlusIcon,
    TrashIcon,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    ingredients: Paginated<IngredientType>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ingredients',
        href: ingredient.index().url,
    },
];

const confirmModalOpen = ref<boolean>(false);
const confirmModalMessage = ref<string>('');
const confirmModalType = ref<'destructive' | 'info'>('info');
const confirmModalAction = ref<() => void>(() => {});

const ingredientForm = useForm({});

const deleteIngredient = (id: number) => {
    ingredientForm.delete(ingredient.destroy(id).url, {
        onSuccess: () => {
            closeConfirmModal();
            toast.success('Ingredient successfully deleted.');
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
    router.visit(ingredient.edit(id).url);
};
</script>

<template>
    <Head title="Ingredients" />

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
                            @dblclick="goToEdit(ingredientData.id)"
                            class="cursor-pointer"
                        >
                            <TableCell class="flex items-center gap-4">
                                <img
                                    :src="ingredientData.main_image"
                                    :alt="ingredientData.name"
                                    class="hidden h-8 w-8 rounded-lg md:block"
                                />
                                <span class="whitespace-nowrap">
                                    {{ ingredientData.name }}
                                </span>
                            </TableCell>
                            <TableCell class="group relative">
                                <img
                                    v-if="ingredientData.allergen?.main_image"
                                    :src="ingredientData.allergen?.main_image"
                                    :alt="ingredientData.allergen?.name"
                                    class="h-8 w-8 rounded-full"
                                />
                                <div
                                    v-if="ingredientData.allergen?.name"
                                    class="absolute top-5 left-13 mb-1 w-max rounded bg-gray-800 px-2 py-1 text-xs text-white opacity-0 transition group-hover:opacity-100"
                                >
                                    {{ ingredientData.allergen.name }}
                                </div>
                            </TableCell>
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
                                            <a
                                                :href="
                                                    ingredient.edit(
                                                        ingredientData.id,
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
                                                    'Are you sure you want to delete this ingredient?',
                                                    'destructive',
                                                    () =>
                                                        deleteIngredient(
                                                            ingredientData.id,
                                                        ),
                                                )
                                            "
                                        >
                                            <TrashIcon /> Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="props.ingredients.data.length === 0">
                            <TableCell
                                class="text-center text-gray-300"
                                :colspan="5"
                                >No ingredient yet</TableCell
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
