<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import GlobalSearchModal from '@/components/admin/GlobalSearchModal.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { usePermission } from '@/composables/usePermission';
import type { BreadcrumbItemType } from '@/types';
import { Search } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const { canSearch } = usePermission();
const { t } = useI18n();

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
        class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-border px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div class="flex flex-row gap-2">
            <LanguageSwitcher />

            <button
                v-if="canSearch()"
                @click="openModal"
                class="flex h-11 w-11 items-center justify-center gap-1 rounded-md px-3 py-2 text-sm text-card-foreground transition hover:bg-accent hover:text-accent-foreground sm:w-44 sm:justify-start sm:border sm:shadow-sm"
            >
                <Search class="h-4 w-4" />
                <span class="hidden text-sm sm:inline">{{
                    t('layout.search')
                }}</span>
                <kbd
                    class="ml-1 hidden rounded border border-border bg-muted px-1.5 py-0.5 text-xs text-nowrap text-muted-foreground sm:inline-block"
                >
                    Ctrl K
                </kbd>
            </button>
        </div>
    </header>
    <GlobalSearchModal :open="openSearch" :onClose="closeModal" />
</template>
