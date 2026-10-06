<script setup lang="ts">
import { about, home } from '@/routes';
import menu from '@/routes/menu';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    currentPath: string;
}>();
</script>

<template>
    <nav class="hidden items-center gap-1 md:flex lg:gap-2">
        <Link
            :href="home().url"
            :class="[
                'relative rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                currentPath === home().url
                    ? 'bg-primary/10 text-primary'
                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
            ]"
        >
            {{ t('navigation.home') }}

            <span
                v-if="currentPath === home().url"
                class="absolute bottom-0 left-1/2 h-0.5 w-6 -translate-x-1/2 rounded-full bg-primary"
            />
        </Link>

        <Link
            :href="menu.dish().url"
            :class="[
                'relative rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                currentPath.startsWith(menu.dish().url)
                    ? 'bg-primary/10 text-primary'
                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
            ]"
        >
            {{ t('navigation.menu') }}

            <span
                v-if="currentPath.startsWith(menu.dish().url)"
                class="absolute bottom-0 left-1/2 h-0.5 w-6 -translate-x-1/2 rounded-full bg-primary"
            />
        </Link>

        <Link
            :href="menu.drink().url"
            :class="[
                'relative rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                currentPath.startsWith(menu.drink().url)
                    ? 'bg-primary/10 text-primary'
                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
            ]"
        >
            {{ t('navigation.drinks') }}

            <span
                v-if="currentPath.startsWith(menu.drink().url)"
                class="absolute bottom-0 left-1/2 h-0.5 w-6 -translate-x-1/2 rounded-full bg-primary"
            />
        </Link>

        <Link
            :href="about().url"
            :class="[
                'relative rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                currentPath === about().url
                    ? 'bg-primary/10 text-primary'
                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
            ]"
        >
            {{ t('navigation.about') }}

            <span
                v-if="currentPath === about().url"
                class="absolute bottom-0 left-1/2 h-0.5 w-6 -translate-x-1/2 rounded-full bg-primary"
            />
        </Link>
    </nav>
</template>
