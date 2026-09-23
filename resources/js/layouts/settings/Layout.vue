<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editPassword } from '@/routes/password';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const sidebarNavItems = computed<NavItem[]>(() => [
    {
        title: t('setting.layout.navigation.profile'),
        href: editProfile(),
    },
    {
        title: t('setting.layout.navigation.password'),
        href: editPassword(),
    },
    {
        title: t('setting.layout.navigation.twoFactorAuth'),
        href: show(),
    },
    {
        title: t('setting.layout.navigation.appearance'),
        href: editAppearance(),
    },
]);

const page = usePage();
const currentPath = computed(() => page.url.split('?')[0]);

function isSidebarItemActive(item: NavItem): boolean {
    const target = toUrl(item.href);

    return (
        currentPath.value === target ||
        (target === editAppearance().url &&
            currentPath.value.startsWith(`${target}/`))
    );
}
</script>

<template>
    <div class="px-4 py-6">
        <Heading
            :title="t('setting.layout.title')"
            :description="t('setting.layout.description')"
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'w-full justify-start',
                            { 'bg-muted': isSidebarItemActive(item) },
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" class="h-4 w-4" />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
