<script setup lang="ts">
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    TrashIcon,
    RotateCcwIcon,
    EllipsisVerticalIcon,
} from 'lucide-vue-next';
import { Head, useForm } from '@inertiajs/vue3';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Badge } from '@/components/ui/badge';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { TrashedType } from '@/types/trashed';
import type { BreadcrumbItem } from '@/types';
import trashed from '@/routes/trashed';
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

const trashedForm = useForm({
    id: null as number | null | undefined,
    type: '',
});

const restoreTrashed = (id: number, type: string, name: string) => {
    trashedForm.id = id;
    trashedForm.type = type;

    if (confirm('Are you sure you want to restore dish?')) {
        trashedForm.post(trashed.restore().url, {
            onSuccess: () => {
                toast.success(`${name} has been restored.`);
            },
        });
    }
};

const forceDelete = (id: number, type: string, name: string) => {
    trashedForm.id = id;
    trashedForm.type = type;

    if (confirm('Are you sure you want to permanently delete dish?')) {
        trashedForm.post(trashed.forceDelete().url, {
            onSuccess: () => {
                toast.success(`${name} has been permanently deleted.`);
            },
        });
    }
};

const getColorType = (type: string) => {
    switch (type) {
        case 'Dish':
            return 'bg-[#7F7EFF]';
        case 'Ingredient':
            return 'bg-[#A390E4]';
        case 'Allergen':
            return 'bg-[#C69DD2]';
        default:
            return 'bg-black';
    }
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
                            v-for="dishData in props.trashedItems"
                            :key="dishData.id"
                        >
                            <TableCell class="flex items-center gap-4"
                                ><img
                                    :src="dishData.image"
                                    :alt="dishData.name"
                                    class="hidden h-8 w-8 rounded-lg md:block"
                                />
                                <span class="whitespace-nowrap">{{
                                    dishData.name
                                }}</span></TableCell
                            >
                            <TableCell>
                                <Badge
                                    class="text-white/80"
                                    :class="getColorType(dishData.type)"
                                    >{{ dishData.type }}</Badge
                                >
                            </TableCell>
                            <TableCell
                                ><span class="whitespace-nowrap">{{
                                    dishData.deleted_at
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
                                                restoreTrashed(
                                                    dishData.id,
                                                    dishData.type,
                                                    dishData.name,
                                                )
                                            "
                                        >
                                            <RotateCcwIcon /> Restore
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="
                                                forceDelete(
                                                    dishData.id,
                                                    dishData.type,
                                                    dishData.name,
                                                )
                                            "
                                        >
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
