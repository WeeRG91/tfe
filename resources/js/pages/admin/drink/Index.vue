<script setup lang="ts">
import DrinkDetailModal from '@/components/admin/drink/DrinkDetailModal.vue';
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
import drink from '@/routes/drink';
import type { BreadcrumbItem, Paginated } from '@/types';
import { DrinkType } from '@/types/drink';
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
    drinks: Paginated<DrinkType>;
}>();

console.log(props.drinks);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Drinks',
        href: drink.index().url,
    },
];

const selectedDrink = ref<DrinkType | null>(null);
const detailModalOpen = ref<boolean>(false);
const confirmModalOpen = ref<boolean>(false);
const confirmModalMessage = ref<string>('');
const confirmModalType = ref<'destructive' | 'info'>('info');
const confirmModalAction = ref<() => void>(() => {});

const drinkForm = useForm({});

const deleteDrink = (id: number) => {
    drinkForm.delete(drink.destroy(id).url, {
        onSuccess: () => {
            closeConfirmModal();
            toast.success('Drink successfully deleted.');
        },
    });
};

const toggleAvailability = (id: number) => {
    drinkForm.patch(drink.available(id).url, {
        onSuccess: () => {
            closeConfirmModal();
            toast.success('Drink availability updated successfully.');
        },
    });
};

const openDrinkDetailModal = (drink: DrinkType) => {
    selectedDrink.value = drink;
    detailModalOpen.value = true;
};

const closeDrinkDetailModal = () => {
    detailModalOpen.value = false;
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
</script>

<template>
    <Head title="Drinks" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <div class="m-2 flex cursor-pointer justify-end">
                    <a :href="drink.create().url">
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
                            v-for="drinkData in props.drinks.data"
                            :key="drinkData.id"
                            @dblclick="openDrinkDetailModal(drinkData)"
                        >
                            <TableCell class="flex items-center gap-4">
                                <img
                                    :src="drinkData.image"
                                    :alt="drinkData.name"
                                    class="hidden h-8 w-8 rounded-lg md:block"
                                />
                                <span class="whitespace-nowrap">
                                    {{ drinkData.name }}
                                </span>
                            </TableCell>
                            <TableCell>
                                <Badge
                                    class="text-white/80"
                                    :class="drinkData.category.color"
                                    >{{ drinkData.category.label }}</Badge
                                >
                            </TableCell>
                            <TableCell class="hidden md:table-cell"
                                >{{ drinkData.price }} €</TableCell
                            >
                            <TableCell>
                                <Badge
                                    v-if="
                                        drinkData.is_available === 'Available'
                                    "
                                    class="bg-green-500 text-white"
                                    >{{ drinkData.is_available }}</Badge
                                >
                                <Badge v-else class="bg-red-500 text-white">
                                    {{ drinkData.is_available }}
                                </Badge>
                            </TableCell>
                            <TableCell class="hidden md:table-cell"
                                ><span class="whitespace-nowrap">
                                    {{ drinkData.created_at }}
                                </span>
                            </TableCell>
                            <TableCell
                                ><span class="whitespace-nowrap">
                                    {{ drinkData.updated_at }}
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
                                                    drink.edit(drinkData.id).url
                                                "
                                                class="flex gap-2"
                                            >
                                                <SquarePenIcon /> Edit
                                            </a>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="openDrinkDetailModal(drinkData)">
                                            <EyeIcon /> View
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="
                                                openConfirmModal(
                                                    'Are you sure you want to delete this dish?',
                                                    'destructive',
                                                    () =>
                                                        deleteDrink(
                                                            drinkData.id,
                                                        ),
                                                )
                                            "
                                        >
                                            <TrashIcon /> Delete
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="
                                                drinkData.is_available ===
                                                'Available'
                                            "
                                            @click="
                                                openConfirmModal(
                                                    'Are you sure you want to mark this dish as unavailable?',
                                                    'info',
                                                    () =>
                                                        toggleAvailability(
                                                            drinkData.id,
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
                                                            drinkData.id,
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
                        <TableRow v-if="props.drinks.data.length === 0">
                            <TableCell
                                class="text-center text-gray-300"
                                :colspan="7"
                                >No drink yet</TableCell
                            >
                        </TableRow>
                    </TableBody>
                </Table>

                <DrinkDetailModal
                    :open="detailModalOpen"
                    :selectedDrink="selectedDrink"
                    :onClose="closeDrinkDetailModal"
                />

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
