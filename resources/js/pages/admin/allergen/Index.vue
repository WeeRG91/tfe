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
import { useDateFormatter } from '@/composables/useDateFormatter';
import { usePermission } from '@/composables/usePermission';
import AdminLayout from '@/layouts/AdminLayout.vue';
import allergen from '@/routes/admin/allergen';
import type { BreadcrumbItem, CursorPaginated, FilterType } from '@/types';
import { AllergenType } from '@/types/allergen';
import { AllergenPermissionEnum } from '@/types/permission';
import { Head, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import axios from 'axios';
import {
    EllipsisVerticalIcon,
    Loader,
    Plus,
    RotateCcwIcon,
    SearchIcon,
    SquarePenIcon,
    TrashIcon,
    XIcon,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const { t } = useI18n();
const { formatDateOnly } = useDateFormatter();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('allergen.title'),
        href: allergen.index().url,
    },
]);

const { can } = usePermission();

const confirmModalOpen = ref<boolean>(false);
const confirmModalMessage = ref<string>('');
const confirmModalType = ref<'destructive' | 'info'>('info');
const confirmModalAction = ref<() => void>(() => {});
const allergens = ref<AllergenType[]>([]);
const nextCursor = ref<string | null>(null);
const isLoading = ref<boolean>(false);
const sentinel = ref<HTMLElement | null>(null);
const observer = ref<IntersectionObserver | null>(null);
const filter = ref<FilterType>('all');
const search = ref<string | null>(null);

const moveToBin = async (id: number) => {
    isLoading.value = true;

    try {
        const response = await axios.delete(allergen.destroy(id).url);

        allergens.value = allergens.value.filter((d) => d.id !== id);

        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error(t('allergen.errors.binFailed'));
        }
    } finally {
        isLoading.value = false;
        closeConfirmModal();
    }
};

const restoreAllergen = async (id: number) => {
    isLoading.value = true;

    try {
        const response = await axios.post(allergen.restore(id).url);

        allergens.value = allergens.value.filter((d) => d.id !== id);

        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error(t('allergen.errors.restoreFailed'));
        }
    } finally {
        isLoading.value = false;
        closeConfirmModal();
    }
};

const deleteAllergen = async (id: number) => {
    isLoading.value = true;

    try {
        const response = await axios.delete(allergen.forceDelete(id).url);

        allergens.value = allergens.value.filter((d) => d.id !== id);

        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error(t('allergen.errors.deleteFailed'));
        }
    } finally {
        isLoading.value = false;
        closeConfirmModal();
    }
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
    if (can(AllergenPermissionEnum.ALLERGEN_UPDATE)) {
        router.visit(allergen.edit(id).url);
    }
};

const loadAllergens = async () => {
    if (isLoading.value) return;

    isLoading.value = true;

    try {
        const response = await axios.get<CursorPaginated<AllergenType>>(
            allergen.getAllergens().url,
            {
                params: {
                    cursor: nextCursor.value,
                    filter: filter.value,
                    search: search.value,
                },
            },
        );
        const newAllergen = response.data;
        allergens.value.push(...newAllergen.data);
        nextCursor.value = newAllergen.next_cursor;
    } catch (error) {
        console.log(error);
        toast.error(t('allergen.errors.loadFailed'));
    } finally {
        isLoading.value = false;
    }
};

const applyFilters = () => {
    allergens.value = [];
    nextCursor.value = null;

    observer.value?.disconnect();

    loadAllergens();

    if (sentinel.value) {
        observer.value?.observe(sentinel.value);
    }
};

const changeFilter = (value: FilterType) => {
    filter.value = value;
    applyFilters();
};

const resetAllFilters = () => {
    filter.value = 'all';
    search.value = null;
    applyFilters();
};

watchDebounced(
    search,
    () => {
        applyFilters();
    },
    { debounce: 400 },
);

onMounted(() => {
    loadAllergens();

    observer.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    loadAllergens();
                }
            });
        },
        {
            root: null,
            rootMargin: '100px',
            threshold: 0.1,
        },
    );

    watch(sentinel, (element) => {
        if (element) observer.value?.observe(element);
    });
});

onBeforeUnmount(() => {
    if (observer.value && sentinel.value)
        observer.value.unobserve(sentinel.value);
});
</script>

<template>
    <Head title="Allergens" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-hidden rounded-t-xl p-4"
        >
            <div
                ref="scrollContainer"
                class="relative h-full flex-1 overflow-hidden rounded-t-xl border border-border"
            >
                <div
                    class="flex flex-col gap-2 border-b border-border bg-gradient-to-b from-card to-muted/50 p-4 md:flex-row md:items-center md:justify-between"
                >
                    <div
                        class="flex w-full flex-wrap items-center gap-2 sm:w-auto"
                    >
                        <div
                            class="flex w-full flex-row items-center gap-2 sm:flex-1"
                        >
                            <div
                                class="flex flex-nowrap justify-around gap-1.5"
                            >
                                <Button
                                    size="sm"
                                    :variant="
                                        filter === 'all' ? 'default' : 'outline'
                                    "
                                    @click="changeFilter('all')"
                                    class="cursor-pointer text-xs shadow-sm transition-all duration-200 sm:h-9 sm:text-sm"
                                >
                                    {{ t('admin.filters.all') }}
                                </Button>

                                <Button
                                    size="sm"
                                    :variant="
                                        filter === 'deleted'
                                            ? 'destructive'
                                            : 'outline'
                                    "
                                    @click="changeFilter('deleted')"
                                    class="cursor-pointer text-xs shadow-sm transition-all duration-200 sm:h-9 sm:text-sm"
                                >
                                    {{ t('admin.filters.deleted') }}
                                </Button>
                            </div>

                            <div class="relative flex-1 sm:w-52 sm:flex-none">
                                <SearchIcon
                                    class="absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <input
                                    v-model="search"
                                    type="text"
                                    :placeholder="
                                        t('admin.filters.searchPlaceholder')
                                    "
                                    class="h-8 w-full rounded-md border border-border bg-background pr-3 pl-8 text-sm text-foreground shadow-sm transition-all placeholder:text-muted-foreground hover:border-primary/50 focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none sm:h-9"
                                    @keyup.enter="applyFilters"
                                />
                                <button
                                    v-if="search"
                                    @click="
                                        search = '';
                                        applyFilters();
                                    "
                                    class="absolute top-1/2 right-2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                >
                                    <XIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="can(AllergenPermissionEnum.ALLERGEN_CREATE)"
                        class="ml-auto flex"
                    >
                        <a :href="allergen.create().url" class="inline-block">
                            <Button
                                variant="outline"
                                size="sm"
                                class="group cursor-pointer shadow-sm transition-all duration-300 hover:shadow-md sm:h-9"
                            >
                                <Plus
                                    class="mr-2 h-5 w-5 transition-transform duration-300 group-hover:scale-110"
                                />
                                {{ t('admin.buttons.add') }}
                            </Button>
                        </a>
                    </div>
                </div>

                <div
                    v-if="filter !== 'all' || search"
                    class="flex flex-wrap items-center gap-2 border-t border-border bg-muted/50 px-4 py-2 text-sm"
                >
                    <span
                        class="hidden text-muted-foreground sm:inline-block"
                        >{{ t('admin.filters.activeFilters') }}</span
                    >
                    <span
                        v-if="filter !== 'all'"
                        class="inline-flex items-center rounded-full bg-info px-2.5 py-0.5 text-xs font-medium text-info-foreground"
                    >
                        {{ t(`admin.filters.${filter}`) }}
                        <button
                            @click="changeFilter('all')"
                            class="ml-1 cursor-pointer rounded-full hover:bg-info-foreground/20"
                        >
                            ×
                        </button>
                    </span>
                    <span
                        v-if="search"
                        class="inline-flex items-center rounded-full bg-warning px-2.5 py-0.5 text-xs font-medium text-warning-foreground"
                    >
                        "{{ search }}"
                        <button
                            @click="
                                search = '';
                                applyFilters();
                            "
                            class="ml-1 cursor-pointer rounded-full hover:bg-warning-foreground/20"
                        >
                            ×
                        </button>
                    </span>
                    <button
                        @click="resetAllFilters"
                        class="ml-auto hidden cursor-pointer text-xs text-muted-foreground hover:text-foreground sm:inline-block"
                    >
                        {{ t('admin.filters.clearAll') }}
                    </button>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>
                                {{ t('allergen.table.name') }}
                            </TableHead>
                            <TableHead>
                                {{ t('allergen.table.createdAt') }}
                            </TableHead>
                            <TableHead v-if="filter !== 'deleted'">
                                {{ t('allergen.table.updatedAt') }}
                            </TableHead>
                            <TableHead v-else>
                                {{ t('allergen.table.deletedAt') }}
                            </TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="allergenData in allergens"
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
                                    formatDateOnly(allergenData.created_at)
                                }}</span></TableCell
                            >
                            <TableCell v-if="filter !== 'deleted'"
                                ><span class="whitespace-nowrap">{{
                                    formatDateOnly(allergenData.updated_at)
                                }}</span> </TableCell
                            ><TableCell v-else
                                ><span class="whitespace-nowrap">{{
                                    formatDateOnly(allergenData.deleted_at)
                                }}</span>
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger
                                        v-if="
                                            can(
                                                AllergenPermissionEnum.ALLERGEN_UPDATE,
                                            )
                                        "
                                    >
                                        <EllipsisVerticalIcon />
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        v-if="filter === 'deleted'"
                                    >
                                        <DropdownMenuItem
                                            v-if="
                                                can(
                                                    AllergenPermissionEnum.ALLERGEN_RESTORE,
                                                )
                                            "
                                            @click="
                                                openConfirmModal(
                                                    t(
                                                        'allergen.messages.confirmRestore',
                                                    ),
                                                    'info',
                                                    () =>
                                                        restoreAllergen(
                                                            allergenData.id,
                                                        ),
                                                )
                                            "
                                        >
                                            <RotateCcwIcon />
                                            {{ t('admin.buttons.restore') }}
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="
                                                can(
                                                    AllergenPermissionEnum.ALLERGEN_DELETE,
                                                )
                                            "
                                            @click="
                                                openConfirmModal(
                                                    t(
                                                        'allergen.messages.confirmDelete',
                                                    ),
                                                    'destructive',
                                                    () =>
                                                        deleteAllergen(
                                                            allergenData.id,
                                                        ),
                                                )
                                            "
                                        >
                                            <TrashIcon />
                                            {{ t('admin.buttons.delete') }}
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                    <DropdownMenuContent v-else>
                                        <DropdownMenuItem>
                                            <a
                                                :href="
                                                    allergen.edit(
                                                        allergenData.id,
                                                    ).url
                                                "
                                                class="flex gap-2"
                                            >
                                                <SquarePenIcon />
                                                {{ t('admin.buttons.edit') }}
                                            </a>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="
                                                can(
                                                    AllergenPermissionEnum.ALLERGEN_DELETE,
                                                )
                                            "
                                            @click="
                                                openConfirmModal(
                                                    t(
                                                        'allergen.messages.confirmMoveToBin',
                                                    ),
                                                    'destructive',
                                                    () =>
                                                        moveToBin(
                                                            allergenData.id,
                                                        ),
                                                )
                                            "
                                        >
                                            <TrashIcon />
                                            {{ t('admin.buttons.moveToBin') }}
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>

                        <tr ref="sentinel" v-if="nextCursor">
                            <td
                                :colspan="5"
                                class="py-2 text-center text-muted-foreground"
                            >
                                <Loader
                                    class="mx-auto animate-spin text-muted-foreground"
                                />
                            </td>
                        </tr>
                    </TableBody>
                </Table>

                <div
                    v-if="isLoading"
                    class="flex h-24 items-center justify-center sm:h-32"
                >
                    <Loader
                        class="mx-auto animate-spin text-muted-foreground"
                    />
                </div>

                <div
                    v-if="allergens.length === 0 && !isLoading"
                    class="flex h-24 items-center justify-center text-muted-foreground sm:h-32"
                >
                    {{ t('allergen.messages.noAllergen') }}
                </div>

                <ConfirmModal
                    :open="confirmModalOpen"
                    :onClose="closeConfirmModal"
                    :message="confirmModalMessage"
                    :type="confirmModalType"
                    :isLoading="isLoading"
                    @confirm="confirmModalAction"
                />
            </div>
        </div>
    </AdminLayout>
</template>
