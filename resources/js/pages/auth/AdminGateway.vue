<script setup lang="ts">
import CostumedAuthLayout from '@/layouts/CostumedAuthLayout.vue';
import { home } from '@/routes';
import { dashboard } from '@/routes/admin';
import { Head, Link } from '@inertiajs/vue3';
import { HandPlatter, LayoutDashboard } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const navigationItems = computed(() => [
    {
        label: t('adminGateway.navigation.administrationPanel.label'),
        route: dashboard,
        icon: LayoutDashboard,
        iconClass: 'group-hover:-translate-x-0.5',
        description: t(
            'adminGateway.navigation.administrationPanel.description',
        ),
    },
    {
        label: t('adminGateway.navigation.restaurant.label'),
        route: home,
        icon: HandPlatter,
        iconClass: 'group-hover:translate-x-0.5',
        description: t('adminGateway.navigation.restaurant.description'),
    },
]);
</script>

<template>
    <Head :title="t('adminGateway.title')" />

    <CostumedAuthLayout
        :title="t('adminGateway.title')"
        :badge="t('adminGateway.badge')"
    >
        <div class="flex flex-col gap-4">
            <Link
                v-for="item in navigationItems"
                :key="item.label"
                :href="item.route()"
                class="group flex items-center gap-4 rounded-lg border border-border bg-card p-5 text-card-foreground transition-all hover:scale-[1.02] hover:border-primary/50 hover:shadow-md focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
            >
                <div
                    class="rounded-lg bg-primary p-3 text-primary-foreground transition-colors group-hover:bg-primary/90"
                >
                    <component
                        :is="item.icon"
                        class="h-5 w-5 transition-transform"
                        :class="item.iconClass"
                    />
                </div>
                <div class="flex flex-1 flex-col">
                    <span class="text-base font-medium text-card-foreground">
                        {{ item.label }}
                    </span>
                    <span class="text-sm text-muted-foreground">
                        {{ item.description }}
                    </span>
                </div>
                <div
                    class="text-muted-foreground transition-transform group-hover:translate-x-1"
                >
                    →
                </div>
            </Link>
        </div>

        <template #footer>
            <div class="text-sm text-muted-foreground">
                <p class="font-medium text-card-foreground">
                    {{ t('adminGateway.footer.title') }}
                </p>
                <p class="mt-1">
                    {{ t('adminGateway.footer.description') }}
                </p>
            </div>
        </template>
    </CostumedAuthLayout>
</template>

<style scoped>
.group {
    transition: all 0.2s ease-in-out;
}

:focus-visible {
    outline: 2px solid var(--ring);
    outline-offset: 2px;
}
</style>
