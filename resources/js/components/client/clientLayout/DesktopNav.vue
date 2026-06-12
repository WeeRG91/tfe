<script setup lang="ts">
import menu from '@/routes/menu';

defineProps<{
    currentPath: string;
}>();
</script>

<template>
    <nav class="hidden items-center gap-1 md:flex lg:gap-2">
        <a
            :href="menu.dish().url"
            :class="[
                'relative rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200',
                currentPath.startsWith(menu.dish().url)
                    ? 'bg-red-50 text-red-600'
                    : 'text-gray-700 hover:bg-red-50 hover:text-red-600',
            ]"
        >
            Menu
            <span
                v-if="currentPath.startsWith(menu.dish().url)"
                class="absolute bottom-0 left-1/2 h-0.5 w-6 -translate-x-1/2 transform rounded-full bg-red-500"
            ></span>
        </a>
        <a
            :href="menu.drink().url"
            :class="[
                'relative rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200',
                currentPath.startsWith(menu.drink().url)
                    ? 'bg-red-50 text-red-600'
                    : 'text-gray-700 hover:bg-red-50 hover:text-red-600',
            ]"
        >
            Drinks
            <span
                v-if="currentPath.startsWith(menu.drink().url)"
                class="absolute bottom-0 left-1/2 h-0.5 w-6 -translate-x-1/2 transform rounded-full bg-red-500"
            ></span>
        </a>
        <a
            href="#"
            class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-red-50 hover:text-red-600"
        >
            About
        </a>
        <a
            href="#"
            class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-red-50 hover:text-red-600"
        >
            Contact
        </a>
    </nav>
</template>
