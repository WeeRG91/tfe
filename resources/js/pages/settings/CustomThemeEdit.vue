<script setup lang="ts">
import ConfirmModal from '@/components/ConfirmModal.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/AdminLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit as editAppearance } from '@/routes/appearance';
import {
    destroy as destroyCustomTheme,
    publish as publishCustomTheme,
    update as updateCustomTheme,
} from '@/routes/appearance/custom-themes';
import type { BreadcrumbItem } from '@/types';
import type { ManagedCustomTheme, ThemeColors } from '@/types/theme';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    customTheme: ManagedCustomTheme;
}>();

const { t } = useI18n();

const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: t('setting.appearance.title'),
        href: editAppearance().url,
    },
]);

const form = useForm<{ name: string; colors: ThemeColors }>({
    name: props.customTheme.name,
    colors: { ...props.customTheme.colors },
});

const publishForm = useForm({});

const deleteForm = useForm({});
const deleteConfirmationOpen = ref<boolean>(false);

const brandColorKeys = [
    'primary',
    'primaryForeground',
    'secondary',
    'secondaryForeground',
    'accent',
    'accentForeground',
] as const satisfies readonly (keyof ThemeColors)[];

const surfaceColorKeys = [
    'background',
    'foreground',
    'card',
    'cardForeground',
    'popover',
    'popoverForeground',
    'muted',
    'mutedForeground',
    'border',
    'input',
    'ring',
] as const satisfies readonly (keyof ThemeColors)[];

const feedbackColorKeys = [
    'destructive',
    'destructiveForeground',
    'success',
    'successForeground',
    'warning',
    'warningForeground',
    'info',
    'infoForeground',
] as const satisfies readonly (keyof ThemeColors)[];

const sidebarColorKeys = [
    'sidebarBackground',
    'sidebarForeground',
    'sidebarPrimary',
    'sidebarPrimaryForeground',
    'sidebarAccent',
    'sidebarAccentForeground',
    'sidebarBorder',
    'sidebarRing',
] as const satisfies readonly (keyof ThemeColors)[];

const chartColorKeys = [
    'chart1',
    'chart2',
    'chart3',
    'chart4',
    'chart5',
] as const satisfies readonly (keyof ThemeColors)[];

const advancedColorGroups = [
    { titleKey: 'surfaceColors', keys: surfaceColorKeys },
    { titleKey: 'feedbackColors', keys: feedbackColorKeys },
    { titleKey: 'sidebarColors', keys: sidebarColorKeys },
    { titleKey: 'chartColors', keys: chartColorKeys },
] as const;

function colorError(key: keyof ThemeColors): string | undefined {
    const field = `colors.${key}`;

    return (
        (form.errors as Record<string, string | undefined>)[field] ??
        (publishForm.errors as Record<string, string | undefined>)[field]
    );
}

function paletteError(): string | undefined {
    return (
        form.errors.colors ??
        (publishForm.errors as Record<string, string | undefined>).colors
    );
}

function hasGroupColorError(keys: readonly (keyof ThemeColors)[]): boolean {
    return keys.some((key) => Boolean(colorError(key)));
}

function saveTheme(): void {
    publishForm.clearErrors();

    form.put(updateCustomTheme(props.customTheme.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            form.defaults();
            toast.success(t('setting.appearance.customThemes.saved'));
        },
    });
}

function publishTheme(): void {
    if (form.isDirty || form.processing || publishForm.processing) {
        return;
    }

    publishForm.post(publishCustomTheme(props.customTheme.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(
                t('setting.appearance.customThemes.publishedSuccess'),
            );
        },
    });
}

function closeDeleteConfirmation(): void {
    if (!deleteForm.processing) {
        deleteConfirmationOpen.value = false;
    }
}

function deleteTheme(): void {
    if (deleteForm.processing) {
        return;
    }

    deleteForm.delete(destroyCustomTheme(props.customTheme.id).url, {
        onSuccess: () => {
            deleteConfirmationOpen.value = false;
            toast.success(t('setting.appearance.customThemes.deleted'));
        },
    });
}
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head :title="customTheme.name" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    :title="customTheme.name"
                    :description="
                        customTheme.published
                            ? t('setting.appearance.customThemes.published')
                            : t('setting.appearance.customThemes.draft')
                    "
                />

                <form class="space-y-6" @submit.prevent="saveTheme">
                    <div class="space-y-2">
                        <Label for="theme-name">
                            {{ t('setting.appearance.customThemes.name') }}
                        </Label>
                        <Input
                            id="theme-name"
                            v-model="form.name"
                            maxlength="100"
                            required
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div
                            v-for="colorKey in brandColorKeys"
                            :key="colorKey"
                            class="space-y-2"
                        >
                            <Label :for="`theme-${colorKey}`">
                                {{
                                    t(
                                        `setting.appearance.customThemes.colors.${colorKey}`,
                                    )
                                }}
                            </Label>

                            <div class="flex gap-2">
                                <input
                                    :id="`theme-${colorKey}`"
                                    v-model="form.colors[colorKey]"
                                    type="color"
                                    class="h-9 w-12 shrink-0 cursor-pointer rounded-md border border-input bg-background p-1"
                                    :disabled="form.processing"
                                />
                                <Input
                                    v-model="form.colors[colorKey]"
                                    type="text"
                                    maxlength="7"
                                    class="font-mono"
                                    :aria-label="`${t(`setting.appearance.customThemes.colors.${colorKey}`)} HEX`"
                                    :disabled="form.processing"
                                />
                            </div>

                            <InputError :message="colorError(colorKey)" />
                        </div>
                    </div>

                    <details
                        v-for="group in advancedColorGroups"
                        :key="group.titleKey"
                        class="rounded-lg border border-border bg-card"
                        :open="hasGroupColorError(group.keys)"
                    >
                        <summary class="cursor-pointer p-4 font-medium">
                            {{
                                t(
                                    `setting.appearance.customThemes.${group.titleKey}`,
                                )
                            }}
                        </summary>

                        <div
                            class="grid gap-4 border-t border-border p-4 sm:grid-cols-2"
                        >
                            <div
                                v-for="colorKey in group.keys"
                                :key="colorKey"
                                class="space-y-2"
                            >
                                <Label :for="`theme-${colorKey}`">
                                    {{
                                        t(
                                            `setting.appearance.customThemes.colors.${colorKey}`,
                                        )
                                    }}
                                </Label>

                                <div class="flex gap-2">
                                    <input
                                        :id="`theme-${colorKey}`"
                                        v-model="form.colors[colorKey]"
                                        type="color"
                                        class="h-9 w-12 shrink-0 cursor-pointer rounded-md border border-input bg-background p-1"
                                        :disabled="form.processing"
                                    />
                                    <Input
                                        v-model="form.colors[colorKey]"
                                        type="text"
                                        maxlength="7"
                                        class="font-mono"
                                        :aria-label="`${t(`setting.appearance.customThemes.colors.${colorKey}`)} HEX`"
                                        :disabled="form.processing"
                                    />
                                </div>

                                <InputError :message="colorError(colorKey)" />
                            </div>
                        </div>
                    </details>

                    <InputError :message="paletteError()" />

                    <div
                        class="rounded-lg border p-4"
                        :style="{
                            backgroundColor: form.colors.background,
                            color: form.colors.foreground,
                            borderColor: form.colors.border,
                            borderRadius: customTheme.radius,
                        }"
                    >
                        <p class="font-medium">
                            {{ form.name || customTheme.name }}
                        </p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span
                                class="rounded-md px-3 py-1.5 text-sm"
                                :style="{
                                    backgroundColor: form.colors.primary,
                                    color: form.colors.primaryForeground,
                                }"
                            >
                                {{
                                    t(
                                        'setting.appearance.customThemes.colors.primary',
                                    )
                                }}
                            </span>
                            <span
                                class="rounded-md px-3 py-1.5 text-sm"
                                :style="{
                                    backgroundColor: form.colors.secondary,
                                    color: form.colors.secondaryForeground,
                                }"
                            >
                                {{
                                    t(
                                        'setting.appearance.customThemes.colors.secondary',
                                    )
                                }}
                            </span>
                            <span
                                class="rounded-md px-3 py-1.5 text-sm"
                                :style="{
                                    backgroundColor: form.colors.accent,
                                    color: form.colors.accentForeground,
                                }"
                            >
                                {{
                                    t(
                                        'setting.appearance.customThemes.colors.accent',
                                    )
                                }}
                            </span>
                        </div>
                    </div>

                    <p
                        v-if="!customTheme.published && publishForm.hasErrors"
                        class="text-sm text-destructive"
                    >
                        {{ t('setting.appearance.customThemes.publishFailed') }}
                    </p>

                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <p
                            v-if="!customTheme.published && form.isDirty"
                            class="mr-auto text-sm text-muted-foreground"
                        >
                            {{
                                t(
                                    'setting.appearance.customThemes.saveBeforePublish',
                                )
                            }}
                        </p>

                        <Button
                            type="submit"
                            :disabled="
                                form.processing ||
                                publishForm.processing ||
                                !form.isDirty
                            "
                        >
                            {{ t('admin.buttons.save') }}
                        </Button>

                        <Button
                            v-if="!customTheme.published"
                            type="button"
                            variant="secondary"
                            :disabled="
                                form.processing ||
                                publishForm.processing ||
                                form.isDirty
                            "
                            @click="publishTheme"
                        >
                            {{ t('setting.appearance.customThemes.publish') }}
                        </Button>
                    </div>
                </form>

                <div class="border-t border-border pt-6">
                    <Button
                        type="button"
                        variant="destructive"
                        :disabled="
                            form.processing ||
                            publishForm.processing ||
                            deleteForm.processing
                        "
                        @click="deleteConfirmationOpen = true"
                    >
                        {{ t('setting.appearance.customThemes.delete') }}
                    </Button>
                </div>
            </div>
        </SettingsLayout>

        <ConfirmModal
            :open="deleteConfirmationOpen"
            :on-close="closeDeleteConfirmation"
            :message="
                t('setting.appearance.customThemes.deleteConfirmation', {
                    name: customTheme.name,
                })
            "
            type="destructive"
            :is-loading="deleteForm.processing"
            @confirm="deleteTheme"
        />
    </AdminLayout>
</template>
