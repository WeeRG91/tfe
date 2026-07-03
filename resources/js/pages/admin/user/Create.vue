<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import user from '@/routes/admin/user';
import { type BreadcrumbItem } from '@/types';
import { PermissionType } from '@/types/permission';
import { RoleType } from '@/types/role';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    roles: RoleType[];
    permissions: PermissionType[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: user.index().url,
    },
    {
        title: 'Create',
        href: user.create().url,
    },
];

const rolePermissions = ref<number[]>([]);
const selectedPermissions = ref<number[]>([]);

const userForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: null as number | null,
    permissions: [] as number[],
});

const expandedCategories = ref<string[]>([
    ...new Set(props.permissions.map((p) => p.category)),
]);

const groupedPermissions = computed(() => {
    const groups: Record<string, PermissionType[]> = {};
    props.permissions.forEach((permission) => {
        if (!groups[permission.category]) {
            groups[permission.category] = [];
        }
        groups[permission.category].push(permission);
    });
    return groups;
});

watch(
    () => userForm.role,
    (newRoleId) => {
        if (!newRoleId) {
            rolePermissions.value = [];
            return;
        }

        const currentRole = props.roles.find((r) => r.id === newRoleId);

        rolePermissions.value = currentRole?.permissions.map((p) => p.id) ?? [];
    },
);

const toggleCategory = (category: string) => {
    if (expandedCategories.value.includes(category)) {
        expandedCategories.value = expandedCategories.value.filter(
            (c) => c !== category,
        );
    } else {
        expandedCategories.value.push(category);
    }
};

const isExpanded = (category: string) => {
    return expandedCategories.value.includes(category);
};

const isAllSelected = (category: string) => {
    const categoryPermissionIds = props.permissions
        .filter((p) => p.category === category)
        .map((p) => p.id);

    return categoryPermissionIds.every((id) => isSelected(id));
};

const toggleAllPermissions = (category: string) => {
    const categoryPermissionIds = props.permissions
        .filter((p) => p.category === category)
        .map((p) => p.id);

    const allSelected = isAllSelected(category);

    if (allSelected) {
        selectedPermissions.value = selectedPermissions.value.filter(
            (id) => !categoryPermissionIds.includes(id),
        );
    } else {
        const newPermissions = categoryPermissionIds.filter(
            (id) =>
                !rolePermissions.value.includes(id) &&
                !selectedPermissions.value.includes(id),
        );

        selectedPermissions.value.push(...newPermissions);
    }
};

const handlePermissionToggle = (permissionId: number) => {
    const index = selectedPermissions.value.indexOf(permissionId);

    if (index > -1) {
        selectedPermissions.value.splice(index, 1);
    } else {
        selectedPermissions.value.push(permissionId);
    }
};

const isSelected = (permissionId: number) => {
    return (
        rolePermissions.value.includes(permissionId) ||
        selectedPermissions.value.includes(permissionId)
    );
};

const isCategoryLocked = (category: string) => {
    const categoryPermissionIds = props.permissions
        .filter((p) => p.category === category)
        .map((p) => p.id);

    return categoryPermissionIds.every((id) =>
        rolePermissions.value.includes(id),
    );
};

const getSelectedCount = (category: string) => {
    const categoryPermissionIds = props.permissions
        .filter((p) => p.category === category)
        .map((p) => p.id);

    return categoryPermissionIds.filter((id) => isSelected(id)).length;
};

const submit = () => {
    userForm.permissions = selectedPermissions.value;

    userForm.post(user.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            userForm.reset();
            toast.success('User successfully created!');
        },
        onError: (error) => {
            console.log(error);
            if (error.error) {
                toast.error(error.error);
            } else if (error.name || error.email || error.password) {
                toast.error('Invalid input.');
            } else {
                toast.error('Something went wrong. Please check the form.');
            }
        },
    });
};
</script>

<template>
    <Head title="Create User" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <form @submit.prevent="submit" class="space-y-6">
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h3 class="mb-4 text-lg font-semibold text-foreground">
                        User Information
                    </h3>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label
                                for="user-name"
                                class="mb-2 block text-sm font-medium text-foreground"
                            >
                                Full Name
                            </label>
                            <input
                                id="user-name"
                                v-model="userForm.name"
                                type="text"
                                class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-foreground transition-all duration-200 focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none dark:border-sidebar-border"
                                placeholder="Enter full name"
                                required
                            />
                            <p
                                v-if="userForm.errors.name"
                                class="mt-2 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ userForm.errors.name }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="user-email"
                                class="mb-2 block text-sm font-medium text-foreground"
                            >
                                Email Address
                            </label>
                            <input
                                id="user-email"
                                v-model="userForm.email"
                                type="email"
                                class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-foreground transition-all duration-200 focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none dark:border-sidebar-border"
                                placeholder="Enter email address"
                                required
                            />
                            <p
                                v-if="userForm.errors.email"
                                class="mt-2 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ userForm.errors.email }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="user-password"
                                class="mb-2 block text-sm font-medium text-foreground"
                            >
                                Password
                            </label>
                            <input
                                id="user-password"
                                v-model="userForm.password"
                                type="password"
                                class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-foreground transition-all duration-200 focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none dark:border-sidebar-border"
                                placeholder="Enter password"
                                required
                            />
                            <p
                                v-if="userForm.errors.password"
                                class="mt-2 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ userForm.errors.password }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="user-password-confirmation"
                                class="mb-2 block text-sm font-medium text-foreground"
                            >
                                Confirm Password
                            </label>
                            <input
                                id="user-password-confirmation"
                                v-model="userForm.password_confirmation"
                                type="password"
                                class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-foreground transition-all duration-200 focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none dark:border-sidebar-border"
                                placeholder="Confirm password"
                                required
                            />
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-foreground">
                            Role Assignment
                        </h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Select a role for this user. Permissions will be
                            automatically assigned based on the role.
                        </p>
                    </div>

                    <div class="max-w-md">
                        <label
                            for="user-role"
                            class="mb-2 block text-sm font-medium text-foreground"
                        >
                            Role
                        </label>
                        <select
                            id="user-role"
                            v-model="userForm.role"
                            class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-foreground transition-all duration-200 focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none dark:border-sidebar-border"
                            required
                        >
                            <option :value="null">Select a role</option>
                            <option
                                v-for="role in roles"
                                :key="role.id"
                                :value="role.id"
                            >
                                {{ role.name }}
                            </option>
                        </select>
                        <p
                            v-if="userForm.errors.role"
                            class="mt-2 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ userForm.errors.role }}
                        </p>
                    </div>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-foreground">
                                Permissions
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Fine-tune permissions for this user
                            </p>
                        </div>
                        <div class="text-sm text-muted-foreground">
                            {{ userForm.permissions.length }} selected
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(
                                permissions, category
                            ) in groupedPermissions"
                            :key="category"
                            class="overflow-hidden rounded-lg border border-sidebar-border/50 transition-all duration-200 hover:border-sidebar-border/70 dark:border-sidebar-border"
                        >
                            <div
                                @click="toggleCategory(category)"
                                class="flex cursor-pointer items-center justify-between bg-muted/30 px-4 py-3 transition-colors hover:bg-muted/50"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            :checked="isAllSelected(category)"
                                            :disabled="isCategoryLocked(category)"
                                            @click.stop
                                            @change="
                                                toggleAllPermissions(category)
                                            "
                                            class="h-4 w-4 cursor-pointer rounded border-sidebar-border/70 text-primary transition-colors focus:ring-2 focus:ring-primary/20"
                                        />
                                        <span
                                            class="text-sm font-medium text-foreground capitalize"
                                        >
                                            {{ category }}
                                        </span>
                                    </div>
                                    <span
                                        class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary"
                                    >
                                        {{ getSelectedCount(category) }}/{{
                                            permissions.length
                                        }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <ChevronDown
                                        v-if="isExpanded(category)"
                                        class="h-4 w-4 text-muted-foreground transition-transform"
                                    />
                                    <ChevronUp
                                        v-else
                                        class="h-4 w-4 text-muted-foreground transition-transform"
                                    />
                                </div>
                            </div>

                            <div
                                v-show="isExpanded(category)"
                                class="grid gap-1.5 p-4 md:grid-cols-2 lg:grid-cols-3"
                            >
                                <label
                                    v-for="permission in permissions"
                                    :key="permission.id"
                                    class="group flex cursor-pointer items-center gap-3 rounded-lg p-2 transition-all duration-200 hover:bg-muted/30"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="isSelected(permission.id)"
                                        :disabled="
                                            rolePermissions.includes(
                                                permission.id,
                                            )
                                        "
                                        @change="
                                            handlePermissionToggle(
                                                permission.id,
                                            )
                                        "
                                        class="h-4 w-4 cursor-pointer rounded border-sidebar-border/70 text-primary transition-colors focus:ring-2 focus:ring-primary/20"
                                    />
                                    <div
                                        class="flex flex-1 items-center justify-between"
                                    >
                                        <span
                                            class="text-sm text-foreground transition-colors group-hover:text-primary"
                                        >
                                            {{ permission.name }}
                                        </span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <p
                        v-if="userForm.errors.permissions"
                        class="mt-4 text-sm text-red-600 dark:text-red-400"
                    >
                        {{ userForm.errors.permissions }}
                    </p>
                </div>

                <div class="flex justify-end gap-4">
                    <Link
                        :href="user.index().url"
                        class="rounded-lg flex justify-center items-center sm:w-32 border border-sidebar-border/70 px-6 py-2.5 text-foreground transition-all duration-200 hover:border-sidebar-border hover:bg-muted/50 dark:border-sidebar-border"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="userForm.processing"
                        class="flex items-center justify-center sm:w-32 gap-2 rounded-lg bg-primary px-6 py-2.5 text-primary-foreground shadow-sm transition-all duration-200 hover:bg-primary/90 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span
                            v-if="userForm.processing"
                            class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                        ></span>
                        <span v-else>Create</span>
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
input[type='checkbox'] {
    transition: all 0.15s ease-in-out;
}

input[type='checkbox']:checked {
    animation: checkPop 0.2s ease-in-out;
}

@keyframes checkPop {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.15);
    }
    100% {
        transform: scale(1);
    }
}

select {
    appearance: auto;
    -webkit-appearance: auto;
}
</style>
