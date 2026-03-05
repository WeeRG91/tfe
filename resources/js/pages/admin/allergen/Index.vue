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
import type { BreadcrumbItem, Paginated } from '@/types';
import { AllergenType } from '@/types/allergen';
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
    allergens: Paginated<AllergenType>;
}>();

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

const allergenForm = useForm({});

const deleteAllergen = (id: number) => {
    allergenForm.delete(allergen.destroy(id).url, {
        onSuccess: () => {
            closeConfirmModal();
            toast.success('Allergen successfully deleted.');
        },
        onError: (error) => {
            toast.error(error.message);
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
    router.visit(allergen.edit(id).url);
};
</script>

<template>
    <Head title="Allergens" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <div class="m-2 flex cursor-pointer justify-end">
                    <a :href="allergen.create().url">
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
                            <TableHead>Created at</TableHead>
                            <TableHead>Updated at</TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="allergenData in props.allergens.data"
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
                            <TableCell
                                ><span class="whitespace-nowrap">{{
                                    allergenData.updated_at
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
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="props.allergens.data.length === 0">
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
