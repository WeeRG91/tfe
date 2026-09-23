<script setup lang="ts">
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import ThemePreviewCard from '@/components/theme/ThemePreviewCard.vue';
import { Button } from '@/components/ui/button';
import { usePermission } from '@/composables/usePermission';
import AdminLayout from '@/layouts/AdminLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';
import { update as updateRestaurantDefault } from '@/routes/appearance/restaurant-default';
import type { BreadcrumbItem } from '@/types';
import { AdminPermissionEnum } from '@/types/permission';
import type {
    ManagedCustomTheme,
    ThemeDefinition,
    ThemeKey,
} from '@/types/theme';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';
import {
    store as storeCustomTheme,
    edit as editCustomTheme,
} from '@/routes/appearance/custom-themes';
import { builtInThemes } from '@/themes/registry';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    availableThemes: ThemeDefinition[];
    defaultClientThemeKey: ThemeKey;
    managedCustomThemes: ManagedCustomTheme[];
}>();

const { t } = useI18n();
const { can } = usePermission();

const canManageRestaurantTheme = computed(() =>
    can(AdminPermissionEnum.THEME_MANAGE),
);

const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: t('setting.appearance.title'),
        href: edit().url,
    },
]);

const defaultThemeForm = useForm<{
    default_client_theme_key: ThemeKey;
}>({
    default_client_theme_key: props.defaultClientThemeKey,
});

const createThemeForm = useForm({
    name: '',
    base_theme_key: 'builtin/light',
});

function createCustomTheme(): void {
    createThemeForm.post(storeCustomTheme().url, {
        preserveScroll: true,
        onSuccess: () => {
            createThemeForm.reset('name');
            toast.success(t('setting.appearance.customThemes.created'));
        },
    });
}

function selectRestaurantDefault(theme: ThemeDefinition): void {
    defaultThemeForm.default_client_theme_key = theme.key;
}

function saveRestaurantDefault(): void {
    defaultThemeForm.put(updateRestaurantDefault().url, {
        preserveScroll: true,
        onSuccess: () => {
            defaultThemeForm.defaults();
            toast.success(t('setting.appearance.restaurantDefault.saved'));
        },
    });
}
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head :title="t('setting.appearance.title')" />

        <SettingsLayout>
            <div class="space-y-10">
                <section class="space-y-6">
                    <HeadingSmall
                        :title="t('setting.appearance.heading.title')"
                        :description="
                            t('setting.appearance.heading.description')
                        "
                    />

                    <AppearanceTabs :themes="availableThemes" />
                </section>

                <section
                    v-if="canManageRestaurantTheme"
                    class="space-y-6 border-t border-border pt-8"
                >
                    <HeadingSmall
                        :title="t('setting.appearance.restaurantDefault.title')"
                        :description="
                            t(
                                'setting.appearance.restaurantDefault.description',
                            )
                        "
                    />

                    <form
                        class="space-y-5"
                        @submit.prevent="saveRestaurantDefault"
                    >
                        <div
                            class="grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-4"
                        >
                            <ThemePreviewCard
                                v-for="theme in availableThemes"
                                :key="theme.key"
                                :theme="theme"
                                :selected="
                                    defaultThemeForm.default_client_theme_key ===
                                    theme.key
                                "
                                :disabled="defaultThemeForm.processing"
                                @select="selectRestaurantDefault"
                            />
                        </div>

                        <InputError
                            :message="
                                defaultThemeForm.errors.default_client_theme_key
                            "
                        />

                        <div class="flex justify-end">
                            <Button
                                type="submit"
                                :disabled="
                                    defaultThemeForm.processing ||
                                    !defaultThemeForm.isDirty
                                "
                            >
                                {{
                                    t(
                                        'setting.appearance.restaurantDefault.save',
                                    )
                                }}
                            </Button>
                        </div>
                    </form>
                </section>

                <section
                    v-if="canManageRestaurantTheme"
                    class="space-y-6 border-t border-border pt-8"
                >
                    <HeadingSmall
                        :title="t('setting.appearance.customThemes.title')"
                        :description="
                            t('setting.appearance.customThemes.description')
                        "
                    />

                    <form
                        class="space-y-4 rounded-lg border border-border bg-card p-4"
                        @submit.prevent="createCustomTheme"
                    >
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="custom-theme-name">
                                    {{
                                        t(
                                            'setting.appearance.customThemes.name',
                                        )
                                    }}
                                </Label>
                                <Input
                                    id="custom-theme-name"
                                    v-model="createThemeForm.name"
                                    type="text"
                                    maxlength="100"
                                    required
                                    :disabled="createThemeForm.processing"
                                />
                                <InputError
                                    :message="createThemeForm.errors.name"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="custom-theme-base">
                                    {{
                                        t(
                                            'setting.appearance.customThemes.baseTheme',
                                        )
                                    }}
                                </Label>
                                <select
                                    id="custom-theme-base"
                                    v-model="createThemeForm.base_theme_key"
                                    class="flex h-9 w-full rounded-md border border-input bg-background px-3 text-sm text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    :disabled="createThemeForm.processing"
                                >
                                    <option
                                        v-for="theme in builtInThemes"
                                        :key="theme.key"
                                        :value="theme.key"
                                    >
                                        {{ theme.name }}
                                    </option>
                                </select>
                                <InputError
                                    :message="
                                        createThemeForm.errors.base_theme_key
                                    "
                                />
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <Button
                                type="submit"
                                :disabled="createThemeForm.processing"
                            >
                                {{
                                    t('setting.appearance.customThemes.create')
                                }}
                            </Button>
                        </div>
                    </form>

                    <div
                        v-if="managedCustomThemes.length"
                        class="grid gap-3 sm:grid-cols-2"
                    >
                        <div
                            v-for="theme in managedCustomThemes"
                            :key="theme.key"
                            class="flex min-w-0 items-center gap-3 rounded-lg border border-border bg-card p-3"
                        >
                            <div
                                class="flex shrink-0 gap-1 rounded-md border border-border p-2"
                            >
                                <span
                                    class="size-5 rounded-full"
                                    :style="{
                                        backgroundColor: theme.colors.primary,
                                    }"
                                />
                                <span
                                    class="size-5 rounded-full"
                                    :style="{
                                        backgroundColor: theme.colors.secondary,
                                    }"
                                />
                                <span
                                    class="size-5 rounded-full"
                                    :style="{
                                        backgroundColor: theme.colors.accent,
                                    }"
                                />
                            </div>

                            <span class="min-w-0 flex-1 truncate font-medium">
                                {{ theme.name }}
                            </span>

                            <span
                                class="shrink-0 rounded-full bg-muted px-2 py-1 text-xs text-muted-foreground"
                            >
                                {{
                                    theme.published
                                        ? t(
                                              'setting.appearance.customThemes.published',
                                          )
                                        : t(
                                              'setting.appearance.customThemes.draft',
                                          )
                                }}
                            </span>

                            <Link
                                :href="editCustomTheme(theme.id).url"
                                class="shrink-0 rounded-md border border-border px-2.5 py-1 text-xs font-medium text-foreground hover:bg-accent hover:text-accent-foreground"
                            >
                                {{ t('admin.buttons.edit') }}
                            </Link>
                        </div>
                    </div>

                    <p v-else class="text-sm text-muted-foreground">
                        {{ t('setting.appearance.customThemes.empty') }}
                    </p>
                </section>
            </div>
        </SettingsLayout>
    </AdminLayout>
</template>
