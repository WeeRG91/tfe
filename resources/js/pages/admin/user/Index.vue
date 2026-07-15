<script setup lang="ts">
import ConfirmModal from '@/components/ConfirmModal.vue';
import { Button } from '@/components/ui/button';
import { useClickOutside } from '@/composables/useClickOutside';
import { usePermission } from '@/composables/usePermission';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { formatDate, getInitials, getUserAvatarColor } from '@/lib/utils';
import user from '@/routes/admin/user';
import type { BreadcrumbItem, CursorPaginated } from '@/types';
import { UserPermissionEnum } from '@/types/permission';
import { UserFilterEnum, UserType } from '@/types/user';
import { Head, Link, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import axios from 'axios';
import {
    ChevronRight,
    CircleAlert,
    EllipsisVertical,
    Loader,
    Mail,
    Plus,
    Search,
    ShieldCheck,
    ShieldX,
    SquarePen,
    Trash2,
    X,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: user.index().url,
    },
];

const { can } = usePermission();

const confirmModalOpen = ref<boolean>(false);
const confirmModalMessage = ref<string>('');
const confirmModalType = ref<'destructive' | 'info'>('info');
const confirmModalAction = ref<() => void>(() => {});
const users = ref<UserType[]>([]);
const currentUser = ref<UserType | null>(null);
const searchQuery = ref<string>('');
const isLoading = ref<boolean>(false);
const isPending = ref<boolean>(false);
const dropdownOpenId = ref<number | null>(null);
const dropdownStyle = ref<{ top: string; left: string }>({
    top: '0px',
    left: '0px',
});
const dropdownRef = ref<HTMLElement | null>(null);
const searchInputRef = ref<HTMLInputElement | null>(null);
const filter = ref<UserFilterEnum>(UserFilterEnum.ACTIVE);
const nextCursor = ref<string>('');

const loadUsers = async () => {
    isLoading.value = true;

    try {
        const { data } = await axios.get<CursorPaginated<UserType>>(
            user.getUsers().url,
            {
                params: {
                    cursor: nextCursor.value,
                    search: searchQuery.value,
                    filter: filter.value,
                },
            },
        );

        if (data) {
            const newUsers = data.data as UserType[];
            users.value.push(...newUsers);
            nextCursor.value = data.next_cursor ?? '';
        }
    } catch (error) {
        console.log(error);
        toast.error('Failed to load users');
    } finally {
        isLoading.value = false;
    }
};

const loadMoreUsers = async () => {
    await loadUsers();
};

const inactivateUser = async (userId: number) => {
    isPending.value = true;

    try {
        const { data } = await axios.post(user.inactivate(userId).url);

        users.value = users.value.filter((u) => u.id !== userId);

        closeConfirmModal();

        toast.success(data.message);
    } catch (error) {
        console.log(error);
        toast.error('Failed to inactivate user');
    } finally {
        isPending.value = false;
    }
};

const reactivateUser = async (userId: number) => {
    isPending.value = true;

    try {
        const { data } = await axios.post(user.reactivate(userId).url);

        closeConfirmModal();

        toast.success(data.message);
    } catch (error) {
        console.log(error);
        toast.error('Failed to reactivate user');
    } finally {
        isPending.value = false;
    }
};

const deleteUser = async (userId: number) => {
    isPending.value = true;

    try {
        const { data } = await axios.delete(user.destroy(userId).url);

        users.value = users.value.filter((u) => u.id !== userId);

        closeConfirmModal();

        toast.success(data.message);
    } catch (error) {
        console.log(error);
        toast.error('Failed to delete user');
    } finally {
        isPending.value = false;
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

const toggleDropdown = (event: MouseEvent, userId: number) => {
    if (dropdownOpenId.value === userId) {
        dropdownOpenId.value = null;
        currentUser.value = null;
        return;
    }

    currentUser.value = users.value.find((u) => u.id === userId) ?? null;

    const rect = (event.currentTarget as HTMLElement).getBoundingClientRect();

    dropdownStyle.value = {
        top: `${rect.bottom + 8}px`,
        left: `${rect.right - 160}px`,
    };

    dropdownOpenId.value = userId;
};

const applyFilters = () => {
    users.value = [];
    nextCursor.value = '';

    loadUsers();
};

const changeFilter = (value: UserFilterEnum) => {
    filter.value = value;
    applyFilters();
};

const clearSearch = () => {
    searchQuery.value = '';
    searchInputRef.value?.focus();
};

const showUser = (userId: number) => {
    if (can(UserPermissionEnum.USER_VIEW)) {
        router.visit(user.show(userId).url);
    }
};

watchDebounced(
    searchQuery,
    () => {
        applyFilters();
    },
    { debounce: 400 },
);

useClickOutside(dropdownRef, () => {
    dropdownOpenId.value = null;
});

onMounted(() => {
    loadUsers();
});
</script>

<template>
    <Head title="Users" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Users</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage users and their roles
                    </p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="flex w-full flex-nowrap gap-1.5 sm:w-auto">
                        <Button
                            size="sm"
                            :variant="
                                filter === UserFilterEnum.ACTIVE
                                    ? 'default'
                                    : 'outline'
                            "
                            @click="changeFilter(UserFilterEnum.ACTIVE)"
                            class="flex-1 cursor-pointer text-xs shadow-sm transition-all duration-200 sm:h-9 sm:w-25 sm:flex-none sm:text-sm"
                            :class="
                                filter !== UserFilterEnum.ACTIVE &&
                                'hover:bg-gray-100 dark:hover:bg-gray-800'
                            "
                        >
                            Active
                        </Button>

                        <Button
                            size="sm"
                            :variant="
                                filter === UserFilterEnum.INACTIVE
                                    ? 'default'
                                    : 'outline'
                            "
                            @click="changeFilter(UserFilterEnum.INACTIVE)"
                            class="flex-1 cursor-pointer text-xs shadow-sm transition-all duration-200 sm:h-9 sm:w-25 sm:flex-none sm:text-sm"
                            :class="[
                                filter === UserFilterEnum.INACTIVE
                                    ? 'bg-rose-600 hover:bg-rose-700 dark:bg-rose-600'
                                    : 'hover:bg-gray-100 dark:hover:bg-gray-800',
                            ]"
                        >
                            Inactive
                        </Button>
                    </div>

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
                            placeholder="Search users..."
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
                        v-if="can(UserPermissionEnum.USER_CREATE)"
                        :href="user.create().url"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Add
                    </Link>
                </div>
            </div>

            <div
                v-if="isLoading && users.length === 0"
                class="flex h-32 flex-col items-center justify-center gap-4"
            >
                <Loader class="h-6 w-6 animate-spin text-primary" />
            </div>

            <div
                v-if="!isLoading && searchQuery && users.length === 0"
                class="py-8 text-center"
            >
                <p class="text-muted-foreground">
                    No users found matching "<span class="font-medium">{{
                        searchQuery
                    }}</span
                    >"
                </p>
            </div>

            <div
                v-else-if="!isLoading && !searchQuery && users.length === 0"
                class="py-8 text-center"
            >
                <p class="text-muted-foreground">No users found</p>
            </div>

            <div v-else-if="users.length > 0">
                <div
                    v-for="u in users"
                    :key="u.id"
                    class="mb-2 overflow-hidden rounded-xl border border-sidebar-border/70 transition-all duration-200 hover:border-primary/50 dark:border-sidebar-border"
                >
                    <div
                        @click="showUser(u.id)"
                        class="flex cursor-pointer items-center justify-between p-4 transition-colors hover:bg-accent/5"
                    >
                        <div class="flex flex-1 items-center gap-4">
                            <img
                                v-if="u.avatar"
                                :src="u.avatar"
                                :alt="u.name"
                                class="h-10 w-10 rounded-full object-cover"
                            />

                            <div
                                v-else
                                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full text-sm font-semibold text-white"
                                :class="getUserAvatarColor(u.id)"
                            >
                                {{ getInitials(u.name) }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <h3 class="truncate font-semibold">
                                    {{ u.name }}
                                </h3>
                                <div
                                    class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground sm:gap-4"
                                >
                                    <span class="truncate">
                                        <Mail class="mr-1 inline h-3 w-3" />
                                        {{ u.email }}
                                        <ShieldCheck
                                            v-if="u.email_verified_at"
                                            class="ml-1 inline h-3.5 w-3.5 text-green-500"
                                        />
                                        <CircleAlert
                                            v-else
                                            class="ml-1 inline h-3.5 w-3.5 text-yellow-500"
                                        />
                                    </span>
                                </div>
                            </div>

                            <div
                                class="flex flex-shrink-0 items-center gap-3 text-sm text-muted-foreground"
                            >
                                <span class="hidden md:inline">
                                    Joined: {{ formatDate(u.created_at) }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="can(UserPermissionEnum.USER_UPDATE)"
                            class="relative ml-2 flex-shrink-0"
                        >
                            <button
                                class="rounded-md p-1.5 transition-colors hover:bg-accent"
                                @click.stop="toggleDropdown($event, u.id)"
                            >
                                <EllipsisVertical
                                    class="h-5 w-5 text-muted-foreground"
                                />
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    class="mt-8 flex flex-col items-center justify-center gap-3"
                >
                    <div
                        v-if="isLoading && users.length > 0"
                        class="flex items-center gap-3"
                    >
                        <Loader class="h-5 w-5 animate-spin text-primary" />
                    </div>

                    <button
                        v-if="nextCursor && !isLoading"
                        @click="loadMoreUsers"
                        class="group flex items-center gap-2 text-sm font-medium text-muted-foreground transition-all hover:text-foreground"
                    >
                        <span>Load more roles</span>
                        <ChevronRight
                            class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-0.5"
                        />
                    </button>
                </div>
            </div>

            <Teleport to="body">
                <div
                    v-if="dropdownOpenId !== null"
                    :ref="(el) => (dropdownRef = el as HTMLElement | null)"
                    :style="dropdownStyle"
                    class="fixed z-50 min-w-[160px] rounded-md border border-sidebar-border/70 bg-popover p-1 shadow-lg"
                >
                    <Link
                        v-if="!currentUser?.deleted_at"
                        :href="user.edit(currentUser?.id!).url"
                        class="flex w-full cursor-pointer items-center gap-2 rounded-sm px-3 py-1.5 text-sm hover:bg-accent"
                    >
                        <SquarePen class="h-4 w-4" />
                        Edit
                    </Link>
                    <button
                        v-if="!currentUser?.deleted_at"
                        @click="
                            openConfirmModal(
                                `Are you sure you want to inactivate this user?`,
                                'info',
                                () => inactivateUser(dropdownOpenId!),
                            )
                        "
                        class="flex w-full items-center gap-2 rounded-sm px-3 py-1.5 text-sm"
                        :class="
                            currentUser?.roles[0]?.name === 'Admin'
                                ? 'cursor-not-allowed text-gray-300'
                                : 'cursor-pointer text-destructive hover:bg-destructive/10'
                        "
                    >
                        <ShieldX class="h-4 w-4" />
                        Inactivate
                    </button>
                    <button
                        v-if="currentUser?.deleted_at"
                        @click="
                            openConfirmModal(
                                `Are you sure you want to reactivate this user?`,
                                'info',
                                () => reactivateUser(dropdownOpenId!),
                            )
                        "
                        class="flex w-full items-center gap-2 rounded-sm px-3 py-1.5 text-sm"
                        :class="
                            currentUser?.roles[0]?.name === 'Admin'
                                ? 'cursor-not-allowed text-gray-300'
                                : 'cursor-pointer hover:bg-accent'
                        "
                    >
                        <ShieldCheck class="h-4 w-4" />
                        Reactivate
                    </button>
                    <button
                        v-if="
                            currentUser?.deleted_at &&
                            can(UserPermissionEnum.USER_DELETE)
                        "
                        @click="
                            openConfirmModal(
                                'Are you sure you want to delete this user?',
                                'destructive',
                                () => deleteUser(dropdownOpenId!),
                            )
                        "
                        class="flex w-full items-center gap-2 rounded-sm px-3 py-1.5 text-sm"
                        :class="
                            currentUser?.roles[0]?.name === 'Admin'
                                ? 'cursor-not-allowed text-gray-300'
                                : 'cursor-pointer text-destructive hover:bg-destructive/10'
                        "
                    >
                        <Trash2 class="h-4 w-4" />
                        Delete
                    </button>
                </div>
            </Teleport>

            <ConfirmModal
                :open="confirmModalOpen"
                :onClose="closeConfirmModal"
                :message="confirmModalMessage"
                :type="confirmModalType"
                :isLoading="isPending"
                @confirm="confirmModalAction"
            />
        </div>
    </AdminLayout>
</template>
