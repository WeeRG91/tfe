<script setup lang="ts">
import ConfirmModal, { ModalType } from '@/components/ConfirmModal.vue';
import { Badge } from '@/components/ui/badge';
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
import trashed from '@/routes/trashed';
import type { BreadcrumbItem } from '@/types';
import { TrashedModelType, TrashedType } from '@/types/trashed';
import { Head, useForm } from '@inertiajs/vue3';
import {
    EllipsisVerticalIcon,
    RotateCcwIcon,
    TrashIcon,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    trashedItems: TrashedType[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Trashed',
        href: trashed.index().url,
    },
];

const confirmModalOpen = ref<boolean>(false);
const confirmModalMessage = ref<string>('');
const confirmModalType = ref<ModalType>('info');
const confirmModalAction = ref<() => void>(() => {});

const trashedForm = useForm({
    id: null as number | null | undefined,
    type: '' as TrashedModelType,
});

const restoreTrashed = (id: number, type: TrashedModelType, name: string) => {
    trashedForm.id = id;
    trashedForm.type = type;

    trashedForm.post(trashed.restore().url, {
        onSuccess: () => {
            closeConfirmModal();
            toast.success(`${name} has been restored.`);
        },
    });
};

const forceDelete = (id: number, type: TrashedModelType, name: string) => {
    trashedForm.id = id;
    trashedForm.type = type;

    trashedForm.post(trashed.forceDelete().url, {
        onSuccess: () => {
            closeConfirmModal();
            toast.success(`${name} has been permanently deleted.`);
        },
    });
};

const getColorType = (type: TrashedModelType) => {
    switch (type) {
        case 'Dish':
            return 'bg-[#7F7EFF]';
        case 'Drink':
            return 'bg-[#256EFF]';
        case 'Ingredient':
            return 'bg-[#A390E4]';
        case 'Allergen':
            return 'bg-[#C69DD2]';
        default:
            return 'bg-black';
    }
};

const openConfirmModal = (
    message: string,
    type: ModalType,
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
    <Head title="Trashed items" />

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
                            <TableHead>Type</TableHead>
                            <TableHead>Deleted at</TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="trashedData in props.trashedItems"
                            :key="trashedData.id"
                        >
                            <TableCell class="flex items-center gap-4"
                                ><img
                                    :src="trashedData.image"
                                    :alt="trashedData.name"
                                    class="hidden h-8 w-8 rounded-lg md:block"
                                />
                                <span class="whitespace-nowrap">{{
                                    trashedData.name
                                }}</span></TableCell
                            >
                            <TableCell>
                                <Badge
                                    class="text-white/80"
                                    :class="
                                        getColorType(
                                            trashedData.type,
                                        )
                                    "
                                    >{{ trashedData.type }}</Badge
                                >
                            </TableCell>
                            <TableCell
                                ><span class="whitespace-nowrap">{{
                                    trashedData.deleted_at
                                }}</span></TableCell
                            >
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger>
                                        <EllipsisVerticalIcon />
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent>
                                        <DropdownMenuItem
                                            @click="
                                                openConfirmModal(
                                                    `Are you sure you want to restore this ${trashedData.type.toLowerCase()}?`,
                                                    'info',
                                                    () =>
                                                        restoreTrashed(
                                                            trashedData.id,
                                                            trashedData.type,
                                                            trashedData.name,
                                                        ),
                                                )
                                            "
                                        >
                                            <RotateCcwIcon /> Restore
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="
                                                openConfirmModal(
                                                    `Are you sure you want to permanently delete this ${trashedData.type.toLowerCase()}?`,
                                                    'destructive',
                                                    () =>
                                                        forceDelete(
                                                            trashedData.id,
                                                            trashedData.type,
                                                            trashedData.name,
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
                        <TableRow v-if="props.trashedItems.length === 0">
                            <TableCell
                                class="text-center text-gray-300"
                                :colspan="4"
                                >No trashed item yet</TableCell
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
