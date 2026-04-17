<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import GlobalSearchModal from '@/components/admin/GlobalSearchModal.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { Search } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const openSearch = ref<boolean>(false);

const openModal = () => {
    openSearch.value = true;
};

const closeModal = () => {
    openSearch.value = false;
};

const handleSearchShortcut = (e: KeyboardEvent) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        openModal();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleSearchShortcut);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleSearchShortcut);
});
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        <div>
            <button
                @click="openModal"
                class="flex items-center gap-2 rounded-md border px-3 py-2 text-sm text-muted-foreground shadow-sm transition hover:bg-muted"
            >
                <Search class="h-4 w-4" />
                <span>Search</span>

                <kbd class="ml-4 rounded border bg-muted px-1.5 py-0.5 text-xs">
                    Ctrl K
                </kbd>
            </button>
        </div>
    </header>
    <GlobalSearchModal :open="openSearch" :onClose="closeModal" />
</template>
