<script setup lang="ts">
import CostumedAuthLayout from '@/layouts/CostumedAuthLayout.vue';
import { home } from '@/routes';
import { dashboard } from '@/routes/admin';
import { Head, Link } from '@inertiajs/vue3';
import { HandPlatter, LayoutDashboard } from 'lucide-vue-next';

const navigationItems = [
    {
        label: 'Administration Panel',
        route: dashboard,
        icon: LayoutDashboard,
        iconClass: 'group-hover:-translate-x-0.5',
        description: 'Manage system settings and users',
    },
    {
        label: 'Restaurant',
        route: home,
        icon: HandPlatter,
        iconClass: 'group-hover:translate-x-0.5',
        description: 'View restaurant interface',
    },
] as const;
</script>

<template>
    <Head title="Admin gateway" />

    <CostumedAuthLayout title="Admin gateway" badge="Administration">
        <div class="flex flex-col gap-4">
            <Link
                v-for="item in navigationItems"
                :key="item.label"
                :href="item.route()"
                class="group flex items-center gap-4 rounded-lg border border-gray-200 bg-white p-5 transition-all hover:scale-[1.02] hover:border-gray-300 hover:shadow-md focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
            >
                <div
                    class="rounded-lg bg-blue-50 p-3 transition-colors group-hover:bg-blue-100"
                >
                    <component
                        :is="item.icon"
                        class="h-5 w-5 text-blue-600 transition-transform"
                        :class="item.iconClass"
                    />
                </div>
                <div class="flex flex-1 flex-col">
                    <span class="text-base font-medium text-gray-900">
                        {{ item.label }}
                    </span>
                    <span class="text-sm text-gray-500">
                        {{ item.description }}
                    </span>
                </div>
                <div
                    class="text-gray-400 transition-transform group-hover:translate-x-1"
                >
                    →
                </div>
            </Link>
        </div>

        <template #footer>
            <div class="text-sm text-gray-500">
                <p class="font-medium text-gray-700">Admin Gateway</p>
                <p class="mt-1">
                    Select an option above to access the administrative
                    dashboard or return to the restaurant interface.
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
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}
</style>
