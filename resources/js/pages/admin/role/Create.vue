<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import role from '@/routes/admin/role';
import { type BreadcrumbItem } from '@/types';
import { PermissionType } from '@/types/permission';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    permissions: PermissionType[];
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('role.title'),
        href: role.index().url,
    },
    {
        title: t('admin.buttons.create'),
        href: role.create().url,
    },
]);

const roleForm = useForm({
    name: '',
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

    return categoryPermissionIds.every((id) =>
        roleForm.permissions.includes(id),
    );
};

const toggleAllPermissions = (category: string) => {
    const categoryPermissionIds = props.permissions
        .filter((p) => p.category === category)
        .map((p) => p.id);

    const allSelected = isAllSelected(category);

    if (allSelected) {
        roleForm.permissions = roleForm.permissions.filter(
            (id) => !categoryPermissionIds.includes(id),
        );
    } else {
        const newPermissions = categoryPermissionIds.filter(
            (id) => !roleForm.permissions.includes(id),
        );
        roleForm.permissions = [...roleForm.permissions, ...newPermissions];
    }
};

const handlePermissionToggle = (permissionId: number) => {
    const index = roleForm.permissions.indexOf(permissionId);
    if (index > -1) {
        roleForm.permissions.splice(index, 1);
    } else {
        roleForm.permissions.push(permissionId);
    }
};

const isSelected = (permissionId: number) => {
    return roleForm.permissions.includes(permissionId);
};

const getSelectedCount = (category: string) => {
    const categoryPermissionIds = props.permissions
        .filter((p) => p.category === category)
        .map((p) => p.id);

    return categoryPermissionIds.filter((id) =>
        roleForm.permissions.includes(id),
    ).length;
};

const submit = () => {
    roleForm.post(role.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            roleForm.reset();
            toast.success(t('role.form.messages.created'));
        },
        onError: (error) => {
            console.log(error);
            if (error.error) {
                toast.error(error.error);
            } else if (error.name || error.permissions) {
                toast.error(t('role.form.messages.invalidInput'));
            } else {
                toast.error(t('role.form.messages.error'));
            }
        },
    });
};
</script>

<template>
    <Head :title="t('role.form.createTitle')" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <form @submit.prevent="submit" class="space-y-6">
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h3 class="mb-4 text-lg font-semibold text-foreground">
                        {{ t('role.form.sections.roleInformation') }}
                    </h3>

                    <div class="max-w-md">
                        <label
                            for="role-name"
                            class="mb-2 block text-sm font-medium text-foreground"
                        >
                            {{ t('role.form.fields.roleName') }}
                        </label>
                        <input
                            id="role-name"
                            v-model="roleForm.name"
                            type="text"
                            class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2.5 text-foreground transition-all duration-200 focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none dark:border-sidebar-border"
                            :placeholder="t('role.form.placeholders.roleName')"
                            required
                        />
                        <p
                            v-if="roleForm.errors.name"
                            class="mt-2 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ roleForm.errors.name }}
                        </p>
                    </div>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-foreground">
                                {{ t('role.form.sections.permissions') }}
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ t('role.form.descriptions.permissions') }}
                            </p>
                        </div>
                        <div class="text-sm text-muted-foreground">
                            {{
                                t('role.form.labels.selected', {
                                    count: roleForm.permissions.length,
                                })
                            }}
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
                                            :ref="`checkbox_${category}`"
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
                                        :value="permission.id"
                                        :checked="isSelected(permission.id)"
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
                        v-if="roleForm.errors.permissions"
                        class="mt-4 text-sm text-red-600 dark:text-red-400"
                    >
                        {{ roleForm.errors.permissions }}
                    </p>
                </div>

                <div class="flex justify-end gap-4">
                    <Link
                        :href="role.index().url"
                        class="flex items-center justify-center rounded-lg border border-sidebar-border/70 px-6 py-2.5 text-foreground transition-all duration-200 hover:border-sidebar-border hover:bg-muted/50 sm:w-32 dark:border-sidebar-border"
                    >
                        {{ t('admin.buttons.cancel') }}
                    </Link>
                    <button
                        type="submit"
                        :disabled="roleForm.processing"
                        class="flex items-center justify-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-primary-foreground shadow-sm transition-all duration-200 hover:bg-primary/90 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50 sm:w-32"
                    >
                        <span
                            v-if="roleForm.processing"
                            class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                        ></span>
                        <span v-else>{{ t('admin.buttons.create') }}</span>
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
</style>
