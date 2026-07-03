<script setup lang="ts">
import ConfirmModal from '@/components/ConfirmModal.vue';
import { useClickOutside } from '@/composables/useClickOutside';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { formatDate } from '@/lib/utils';
import role from '@/routes/admin/role';
import type { BreadcrumbItem } from '@/types';
import { PermissionType } from '@/types/permission';
import { RoleType } from '@/types/role';
import { Head, Link } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import axios from 'axios';
import {
    ChevronRight,
    EllipsisVertical,
    Loader,
    Plus,
    Search,
    SquarePen,
    Trash2,
    X,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: role.index().url,
    },
];

const roles = ref<RoleType[]>([]);
const searchQuery = ref<string>('');
const confirmModalOpen = ref<boolean>(false);
const confirmModalMessage = ref<string>('');
const confirmModalType = ref<'destructive' | 'info'>('info');
const confirmModalAction = ref<() => void>(() => {});
const isDeleting = ref<boolean>(false);
const isLoading = ref<boolean>(false);
const expandedRoleId = ref<number | null>(null);
const dropdownOpenId = ref<number | null>(null);
const dropdownStyle = ref<{ top: string; left: string }>({
    top: '0px',
    left: '0px',
});
const dropdownRef = ref<HTMLElement | null>(null);
const searchInputRef = ref<HTMLInputElement | null>(null);

const loadRoles = async () => {
    isLoading.value = true;

    try {
        const { data } = await axios.get<RoleType[]>(role.getRoles().url, {
            params: {
                search: searchQuery.value,
            },
        });

        roles.value.push(...data);
    } catch (error) {
        console.log(error);
        toast.error('Failed to load roles');
    } finally {
        isLoading.value = false;
    }
};

const deleteRole = async (roleId: number) => {
    isDeleting.value = true;

    try {
        const { data } = await axios.delete(role.destroy(roleId).url);

        roles.value = roles.value.filter((r) => r.id !== roleId);

        closeConfirmModal();

        toast.success(data.message);
    } catch (error) {
        console.log(error);
        toast.error('Failed to delete role');
    } finally {
        isDeleting.value = false;
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

const toggleExpansion = (roleId: number) => {
    expandedRoleId.value = expandedRoleId.value === roleId ? null : roleId;
};

const toggleDropdown = (event: MouseEvent, roleId: number) => {
    if (dropdownOpenId.value === roleId) {
        dropdownOpenId.value = null;
        return;
    }

    const rect = (event.currentTarget as HTMLElement).getBoundingClientRect();

    dropdownStyle.value = {
        top: `${rect.bottom + 8}px`,
        left: `${rect.right - 160}px`,
    };

    dropdownOpenId.value = roleId;
};

const applySearch = () => {
    roles.value = [];

    loadRoles();
};

const clearSearch = () => {
    searchQuery.value = '';
    searchInputRef.value?.focus();
};

watchDebounced(
    searchQuery,
    () => {
        applySearch();
    },
    { debounce: 400 },
);

useClickOutside(dropdownRef, () => {
    dropdownOpenId.value = null;
});

const getGroupedPermissions = (permissions: PermissionType[]) =>
    permissions.reduce(
        (acc, permission) => {
            const category = permission.category ?? 'Uncategorized';

            (acc[category] ??= []).push(permission);

            return acc;
        },
        {} as Record<string, PermissionType[]>,
    );

onMounted(() => {
    loadRoles();
});
</script>

<template>
    <Head title="Roles" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Roles</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage roles and their permissions
                    </p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div
                        class="relative flex-1 sm:min-w-[200px] md:min-w-[250px]"
                    >
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <input
                            ref="searchInputRef"
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search roles..."
                            class="w-full rounded-md border border-sidebar-border/70 bg-background py-2 pr-8 pl-9 text-sm shadow-sm transition-colors outline-none placeholder:text-muted-foreground"
                        />
                        <button
                            v-if="searchQuery"
                            @click="clearSearch"
                            class="absolute top-1/2 right-2 -translate-y-1/2 rounded-full p-0.5 text-muted-foreground transition-colors hover:text-foreground"
                            type="button"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <Link
                        :href="role.create().url"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Add
                    </Link>
                </div>
            </div>

            <div
                v-if="isLoading"
                class="flex h-24 items-center justify-center sm:h-32"
            >
                <Loader class="mx-auto animate-spin text-muted-foreground" />
            </div>

            <div
                v-else-if="searchQuery && roles.length === 0"
                class="py-8 text-center"
            >
                <p class="text-muted-foreground">
                    No roles found matching "<span class="font-medium">{{
                        searchQuery
                    }}</span
                    >"
                </p>
            </div>

            <div v-else-if="roles.length > 0">
                <div v-for="r in roles" :key="r.id" class="mb-2">
                    <div
                        class="overflow-hidden rounded-xl border border-sidebar-border/70 transition-all duration-200 hover:border-primary/50 dark:border-sidebar-border"
                    >
                        <div
                            class="flex cursor-pointer items-center justify-between p-4 transition-colors hover:bg-accent/5"
                            @click="toggleExpansion(r.id)"
                        >
                            <div class="flex flex-1 items-center gap-4">
                                <ChevronRight
                                    class="h-5 w-5 text-muted-foreground transition-transform duration-200"
                                    :class="{
                                        'rotate-90': expandedRoleId === r.id,
                                    }"
                                />

                                <div>
                                    <h3 class="font-semibold">{{ r.name }}</h3>
                                    <div
                                        class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground sm:gap-4"
                                    >
                                        <span
                                            >{{
                                                r.permissions?.length
                                            }}
                                            permissions</span
                                        >
                                        <span class="hidden sm:inline">•</span>
                                        <span
                                            >Updated:
                                            {{ formatDate(r.updated_at) }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="relative">
                                <button
                                    class="rounded-md p-1.5 transition-colors hover:bg-accent"
                                    @click.stop="toggleDropdown($event, r.id)"
                                >
                                    <EllipsisVertical
                                        class="h-5 w-5 text-muted-foreground"
                                    />
                                </button>
                            </div>
                        </div>

                        <div
                            class="overflow-hidden transition-all duration-300"
                            :class="
                                expandedRoleId === r.id
                                    ? 'max-h-[1000px] opacity-100'
                                    : 'max-h-0 opacity-0'
                            "
                        >
                            <div class="border-t border-sidebar-border/70 p-4">
                                <div
                                    class="mb-3 text-sm font-medium text-muted-foreground"
                                >
                                    Permissions
                                </div>

                                <div v-if="r.permissions?.length > 0">
                                    <div
                                        v-for="(
                                            permissions, category
                                        ) in getGroupedPermissions(
                                            r.permissions,
                                        )"
                                        :key="category"
                                        class="mb-4 last:mb-0"
                                    >
                                        <div
                                            class="mb-2 flex items-center gap-2"
                                        >
                                            <span
                                                class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                            >
                                                {{ category }}
                                            </span>
                                            <span
                                                class="flex-1 border-t border-sidebar-border/50"
                                            ></span>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <span
                                                v-for="permission in permissions"
                                                :key="permission.id"
                                                class="inline-flex items-center rounded-md bg-gray-500/10 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-500/20 dark:text-gray-400"
                                            >
                                                {{ permission.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <span
                                    v-else
                                    class="text-sm text-muted-foreground"
                                >
                                    No permissions assigned
                                </span>
                            </div>
                        </div>
                    </div>

                    <Teleport to="body">
                        <div
                            v-if="dropdownOpenId === r.id"
                            :ref="
                                (el) => (dropdownRef = el as HTMLElement | null)
                            "
                            :style="dropdownStyle"
                            class="fixed z-50 min-w-[160px] rounded-md border border-sidebar-border/70 bg-popover p-1 shadow-lg"
                        >
                            <Link
                                :href="role.edit(r.id).url"
                                class="flex w-full cursor-pointer items-center gap-2 rounded-sm px-3 py-1.5 text-sm hover:bg-accent"
                            >
                                <SquarePen class="h-4 w-4" />
                                Edit
                            </Link>
                            <button
                                @click="
                                    openConfirmModal(
                                        'Are you sure you want to delete this role?',
                                        'destructive',
                                        () => deleteRole(r.id),
                                    )
                                "
                                class="flex w-full items-center gap-2 rounded-sm px-3 py-1.5 text-sm"
                                :class="
                                    r.name === 'Admin'
                                        ? 'cursor-not-allowed text-gray-300'
                                        : 'cursor-pointer text-destructive hover:bg-destructive/10'
                                "
                            >
                                <Trash2 class="h-4 w-4" />
                                Delete
                            </button>
                        </div>
                    </Teleport>
                </div>
            </div>

            <ConfirmModal
                :open="confirmModalOpen"
                :onClose="closeConfirmModal"
                :message="confirmModalMessage"
                :type="confirmModalType"
                :isLoading="isDeleting"
                @confirm="confirmModalAction"
            />
        </div>
    </AdminLayout>
</template>
