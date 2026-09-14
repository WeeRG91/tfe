<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AdminLayout from '@/layouts/AdminLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: t('setting.appearance.title'),
        href: edit().url,
    },
]);
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head :title="t('setting.appearance.title')" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    :title="t('setting.appearance.heading.title')"
                    :description="t('setting.appearance.heading.description')"
                />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AdminLayout>
</template>
