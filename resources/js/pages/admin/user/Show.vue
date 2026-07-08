<script setup lang="ts">
import ConfirmModal from '@/components/ConfirmModal.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {
    formatDate,
    getInitials,
    getOrderStatusIcon,
    getOrderStatusVariant,
    getTotalPoints,
    getUserAvatarColor,
} from '@/lib/utils';
import user from '@/routes/admin/user';
import type { BreadcrumbItem } from '@/types';
import { PermissionType } from '@/types/permission';
import { UserDetailType } from '@/types/user';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    ArrowLeft,
    Award,
    Calendar,
    CircleAlert,
    Key,
    Mail,
    PlusCircle,
    Shield,
    ShieldCheck,
    ShieldX,
    ShoppingBag,
    SquarePen,
    Star,
    Tag,
    Trash2,
    Users,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { PointTypeEnum } from '@/types/point';

const props = defineProps<{
    currentUser: UserDetailType;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: user.index().url,
    },
    {
        title: props.currentUser.name,
        href: user.show(props.currentUser.id).url,
    },
];

const confirmModalOpen = ref<boolean>(false);
const confirmModalMessage = ref<string>('');
const confirmModalType = ref<'destructive' | 'info'>('info');
const confirmModalAction = ref<() => void>(() => {});
const isPending = ref<boolean>(false);

const getGroupedPermissions = (permissions: PermissionType[]) => {
    if (!permissions || permissions.length === 0) return {};

    return permissions.reduce(
        (acc, permission) => {
            const category = permission.category ?? 'Uncategorized';
            if (!acc[category]) {
                acc[category] = [];
            }
            acc[category].push(permission);
            return acc;
        },
        {} as Record<string, PermissionType[]>,
    );
};

const getAllPermissionsWithSource = () => {
    const rolePerms = new Map<number, PermissionType>();
    const directPerms = new Map<number, PermissionType>();

    if (props.currentUser.roles) {
        props.currentUser.roles.forEach((role) => {
            if (role.permissions) {
                role.permissions.forEach((perm) => {
                    rolePerms.set(perm.id, perm);
                });
            }
        });
    }

    if (props.currentUser.permissions) {
        props.currentUser.permissions.forEach((perm) => {
            directPerms.set(perm.id, perm);
        });
    }

    const allPerms = new Map<
        number,
        { permission: PermissionType; source: 'role' | 'direct' | 'both' }
    >();

    rolePerms.forEach((perm, id) => {
        const isDirect = directPerms.has(id);
        allPerms.set(id, {
            permission: perm,
            source: isDirect ? 'both' : 'role',
        });
    });

    directPerms.forEach((perm, id) => {
        if (!allPerms.has(id)) {
            allPerms.set(id, {
                permission: perm,
                source: 'direct',
            });
        }
    });

    return Array.from(allPerms.values());
};

const getTotalPermissionCount = () => {
    const rolePerms = new Set<number>();
    const directPerms = new Set<number>();

    if (props.currentUser.roles) {
        props.currentUser.roles.forEach((role) => {
            if (role.permissions) {
                role.permissions.forEach((perm) => rolePerms.add(perm.id));
            }
        });
    }

    if (props.currentUser.permissions) {
        props.currentUser.permissions.forEach((perm) =>
            directPerms.add(perm.id),
        );
    }

    const allPerms = new Set([...rolePerms, ...directPerms]);
    return allPerms.size;
};
const inactivateUser = async (userId: number) => {
    isPending.value = true;

    try {
        const { data } = await axios.post(user.inactivate(userId).url);

        closeConfirmModal();

        toast.success(data.message);

        router.visit(user.index().url);
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

        closeConfirmModal();

        toast.success(data.message);

        router.visit(user.index().url);
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
</script>

<template>
    <Head :title="currentUser.name" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <Link
                :href="user.index().url"
                class="inline-flex w-fit items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <ArrowLeft class="h-4 w-4" />
                Back to Users
            </Link>

            <div
                class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-6 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full text-xl font-semibold text-white"
                        :class="getUserAvatarColor(currentUser.id)"
                    >
                        {{ getInitials(currentUser.name) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ currentUser.name }}
                        </h1>
                        <div
                            class="flex flex-wrap items-center gap-3 text-sm text-muted-foreground"
                        >
                            <span>
                                <Mail class="mr-1 inline h-3 w-3" />
                                {{ currentUser.email }}
                                <ShieldCheck
                                    v-if="currentUser.email_verified_at"
                                    class="ml-1 inline h-3.5 w-3.5 text-green-500"
                                />
                                <CircleAlert
                                    v-else
                                    class="ml-1 inline h-3.5 w-3.5 text-yellow-500"
                                />
                            </span>
                            <span>•</span>
                            <span>
                                <Calendar class="mr-1 inline h-3 w-3" />
                                Joined {{ formatDate(currentUser.created_at) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <Link
                        v-if="!currentUser.deleted_at"
                        :href="user.edit(currentUser.id).url"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none sm:w-32"
                    >
                        <SquarePen class="mr-2 h-4 w-4" />
                        Edit
                    </Link>
                    <button
                        v-if="
                            currentUser.roles[0]?.name !== 'Admin' &&
                            !currentUser.deleted_at
                        "
                        @click="
                            openConfirmModal(
                                'Are you sure you want to inactivate this user?',
                                'info',
                                () => inactivateUser(currentUser.id),
                            )
                        "
                        class="inline-flex items-center justify-center rounded-md bg-red-500 px-4 py-2 text-sm font-medium text-white shadow hover:bg-red-600 focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none sm:w-32"
                    >
                        <ShieldX class="mr-2 h-4 w-4" />
                        Inactivate
                    </button>
                    <button
                        v-if="
                            currentUser.roles[0]?.name !== 'Admin' &&
                            currentUser.deleted_at
                        "
                        @click="
                            openConfirmModal(
                                'Are you sure you want to reactivate this user?',
                                'info',
                                () => reactivateUser(currentUser.id),
                            )
                        "
                        class="inline-flex items-center justify-center rounded-md bg-blue-500 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-600 focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none sm:w-32"
                    >
                        <ShieldCheck class="mr-2 h-4 w-4" />
                        Reactivate
                    </button>
                    <button
                        v-if="
                            currentUser.roles[0]?.name !== 'Admin' &&
                            currentUser.deleted_at
                        "
                        @click="
                            openConfirmModal(
                                'Are you sure you want to delete this user?',
                                'destructive',
                                () => deleteUser(currentUser.id),
                            )
                        "
                        class="inline-flex items-center justify-center rounded-md bg-red-500 px-4 py-2 text-sm font-medium text-white shadow hover:bg-red-600 focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none sm:w-32"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete
                    </button>
                </div>
            </div>

            <div
                v-if="
                    currentUser.roles.length > 0 ||
                    currentUser.permissions.length > 0
                "
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
            >
                <div
                    v-if="currentUser.roles.length > 0"
                    class="rounded-xl border border-sidebar-border/70 p-4"
                >
                    <div
                        class="flex items-center gap-2 text-sm text-muted-foreground"
                    >
                        <Users class="h-4 w-4" />
                        Roles
                    </div>
                    <p class="mt-2 text-2xl font-bold">
                        {{ currentUser.roles?.length || 0 }}
                    </p>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 p-4">
                    <div
                        class="flex items-center gap-2 text-sm text-muted-foreground"
                    >
                        <Award class="h-4 w-4" />
                        Total Points
                    </div>
                    <p class="mt-2 text-2xl font-bold">
                        {{ getTotalPoints(currentUser.loyalty_points) }}
                    </p>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 p-4">
                    <div
                        class="flex items-center gap-2 text-sm text-muted-foreground"
                    >
                        <ShoppingBag class="h-4 w-4" />
                        Orders
                    </div>
                    <p class="mt-2 text-2xl font-bold">
                        {{ currentUser.orders?.length || 0 }}
                    </p>
                </div>
                <div
                    v-if="
                        currentUser.roles.length > 0 ||
                        currentUser.permissions.length > 0
                    "
                    class="rounded-xl border border-sidebar-border/70 p-4"
                >
                    <div
                        class="flex items-center gap-2 text-sm text-muted-foreground"
                    >
                        <Shield class="h-4 w-4" />
                        Permissions
                    </div>
                    <p class="mt-2 text-2xl font-bold">
                        {{ getTotalPermissionCount() }}
                    </p>
                </div>
            </div>

            <div v-else class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-sidebar-border/70 p-4">
                    <div
                        class="flex items-center gap-2 text-sm text-muted-foreground"
                    >
                        <Award class="h-4 w-4" />
                        Total Points
                    </div>
                    <p class="mt-2 text-2xl font-bold">
                        {{ getTotalPoints(currentUser.loyalty_points) }}
                    </p>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 p-4">
                    <div
                        class="flex items-center gap-2 text-sm text-muted-foreground"
                    >
                        <ShoppingBag class="h-4 w-4" />
                        Orders
                    </div>
                    <p class="mt-2 text-2xl font-bold">
                        {{ currentUser.orders?.length || 0 }}
                    </p>
                </div>
            </div>

            <div
                v-if="
                    currentUser.roles.length > 0 ||
                    currentUser.permissions.length > 0
                "
                class="rounded-xl border border-sidebar-border/70 p-6"
            >
                <h2 class="mb-4 text-lg font-semibold">Roles & Permissions</h2>

                <div
                    v-if="currentUser.roles && currentUser.roles.length > 0"
                    class="space-y-4"
                >
                    <div>
                        <h3
                            class="mb-2 text-sm font-medium text-muted-foreground"
                        >
                            Assigned Roles
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="role in currentUser.roles"
                                :key="role.id"
                                class="inline-flex items-center rounded-md bg-blue-500/10 px-3 py-1.5 text-sm font-medium text-blue-600 dark:bg-blue-500/20 dark:text-blue-400"
                            >
                                <Shield class="mr-1.5 h-3.5 w-3.5" />
                                {{ role.name }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <h3
                            class="mb-2 text-sm font-medium text-muted-foreground"
                        >
                            All Permissions
                        </h3>
                        <div
                            v-if="getAllPermissionsWithSource().length > 0"
                            class="space-y-3"
                        >
                            <div
                                v-for="(
                                    group, category
                                ) in getGroupedPermissions(
                                    getAllPermissionsWithSource().map(
                                        (item) => item.permission,
                                    ),
                                )"
                                :key="category"
                            >
                                <div class="mb-1.5 flex items-center gap-2">
                                    <span
                                        class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        {{ category }}
                                    </span>
                                    <span
                                        class="flex-1 border-t border-sidebar-border/30"
                                    ></span>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <span
                                        v-for="permission in group"
                                        :key="permission.id"
                                        class="inline-flex items-center rounded px-2 py-1 text-xs font-medium"
                                        :class="{
                                            'bg-green-500/10 text-green-600 dark:bg-green-500/20 dark:text-green-400':
                                                getAllPermissionsWithSource().find(
                                                    (p) =>
                                                        p.permission.id ===
                                                        permission.id,
                                                )?.source === 'direct',
                                            'bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400':
                                                getAllPermissionsWithSource().find(
                                                    (p) =>
                                                        p.permission.id ===
                                                        permission.id,
                                                )?.source === 'role',
                                            'bg-purple-500/10 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400':
                                                getAllPermissionsWithSource().find(
                                                    (p) =>
                                                        p.permission.id ===
                                                        permission.id,
                                                )?.source === 'both',
                                        }"
                                    >
                                        <Key class="mr-1 h-2.5 w-2.5" />
                                        {{ permission.name }}
                                        <span
                                            v-if="
                                                getAllPermissionsWithSource().find(
                                                    (p) =>
                                                        p.permission.id ===
                                                        permission.id,
                                                )?.source === 'direct'
                                            "
                                            class="ml-1 flex items-center gap-0.5 text-[8px] font-bold uppercase"
                                        >
                                            <PlusCircle class="h-2.5 w-2.5" />
                                            extra
                                        </span>
                                        <span
                                            v-if="
                                                getAllPermissionsWithSource().find(
                                                    (p) =>
                                                        p.permission.id ===
                                                        permission.id,
                                                )?.source === 'both'
                                            "
                                            class="ml-1 flex items-center gap-0.5 text-[8px] font-bold text-purple-500 uppercase"
                                        >
                                            <Star class="h-2.5 w-2.5" />
                                            both
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">
                            No permissions assigned
                        </p>
                    </div>
                </div>
                <p v-else class="text-sm text-muted-foreground">
                    No roles assigned to this user
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-xl border border-sidebar-border/70 p-6">
                    <h2 class="mb-4 text-lg font-semibold">Order History</h2>

                    <div
                        v-if="
                            currentUser.orders && currentUser.orders.length > 0
                        "
                        class="overflow-x-auto"
                    >
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-sidebar-border/50">
                                    <th
                                        class="px-4 py-2 text-left font-medium text-muted-foreground"
                                    >
                                        Order #
                                    </th>
                                    <th
                                        class="px-4 py-2 text-left font-medium text-muted-foreground"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-4 py-2 text-left font-medium text-muted-foreground"
                                    >
                                        Created
                                    </th>
                                    <th
                                        class="px-4 py-2 text-left font-medium text-muted-foreground"
                                    >
                                        Updated
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="order in currentUser.orders"
                                    :key="order.id"
                                    class="border-b border-sidebar-border/30 transition-colors hover:bg-accent/5"
                                >
                                    <td class="px-4 py-3 font-medium">
                                        <div class="flex items-center gap-2">
                                            <Tag
                                                class="h-3.5 w-3.5 text-muted-foreground"
                                            />
                                            {{ order.order_number }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium"
                                            :class="
                                                getOrderStatusVariant(
                                                    order.status.value,
                                                )
                                            "
                                        >
                                            <component
                                                :is="
                                                    getOrderStatusIcon(
                                                        order.status.value,
                                                    )
                                                "
                                                class="h-3 w-3"
                                            />
                                            {{ order.status.label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ formatDate(order.created_at) }}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ formatDate(order.updated_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">
                        No orders found for this user
                    </p>
                </div>

                <div class="rounded-xl border border-sidebar-border/70 p-6">
                    <h2 class="mb-4 text-lg font-semibold">
                        Loyalty Points History
                    </h2>

                    <div
                        v-if="
                            currentUser.loyalty_points &&
                            currentUser.loyalty_points.length > 0
                        "
                        class="space-y-1"
                    >
                        <div
                            v-for="transaction in currentUser.loyalty_points"
                            :key="transaction.id"
                            class="flex items-center justify-between rounded-lg bg-accent/5 px-4 py-2"
                        >
                            <div class="flex flex-col">
                                <span>{{ transaction.description }}</span>
                                <span class="text-xs text-muted-foreground">
                                    {{ formatDate(transaction.created_at) }}
                                </span>
                            </div>
                            <span
                                class="font-semibold"
                                :class="
                                    transaction.type === PointTypeEnum.EARNED
                                        ? 'text-green-600 dark:text-green-400'
                                        : transaction.type ===
                                            PointTypeEnum.REFUNDED
                                          ? 'text-yellow-600 dark:text-yellow-400'
                                          : 'text-red-600 dark:text-red-400'
                                "
                            >
                                {{
                                    transaction.type === PointTypeEnum.EARNED ||
                                    transaction.type === PointTypeEnum.REFUNDED
                                        ? '+'
                                        : '-'
                                }}{{ transaction.points }}
                            </span>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">
                        No loyalty points history
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 p-6">
                <div class="grid gap-2 text-sm sm:grid-cols-2">
                    <div>
                        <span class="text-muted-foreground">Created at:</span>
                        <span class="ml-2 font-medium">{{
                            formatDate(currentUser.created_at)
                        }}</span>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Last updated:</span>
                        <span class="ml-2 font-medium">{{
                            formatDate(currentUser.updated_at)
                        }}</span>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmModal
            :open="confirmModalOpen"
            :onClose="closeConfirmModal"
            :message="confirmModalMessage"
            :type="confirmModalType"
            :isLoading="isPending"
            @confirm="confirmModalAction"
        />
    </AdminLayout>
</template>
