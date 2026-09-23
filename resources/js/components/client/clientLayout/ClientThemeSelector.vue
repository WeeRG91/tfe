<script setup lang="ts">
import ThemePreviewCard from '@/components/theme/ThemePreviewCard.vue';
import {
    Dialog,
    DialogDescription,
    DialogHeader,
    DialogScrollContent,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { useAppearance } from '@/composables/useAppearance';
import { getAvailableThemes } from '@/themes/registry';
import { AppPageProps } from '@/types';
import type { ThemeDefinition, ThemeSelection } from '@/types/theme';
import { usePage } from '@inertiajs/vue3';
import { Check, Monitor, Palette, Store } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const page = usePage<AppPageProps>();
const { t } = useI18n();
const { appearance, updateAppearance } = useAppearance('client');

const availableThemes = computed(() =>
    getAvailableThemes(page.props.theme.customThemes).filter(
        (theme) => theme.published,
    ),
);

function selectPreference(selection: ThemeSelection): void {
    updateAppearance(selection);
}

function selectTheme(theme: ThemeDefinition): void {
    updateAppearance(theme.key);
}
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <button
                type="button"
                class="flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                :aria-label="t('common.appearance.open')"
                :title="t('common.appearance.open')"
            >
                <Palette class="size-5" />
            </button>
        </DialogTrigger>

        <DialogScrollContent
            class="h-[65vh] max-h-[48rem] w-[calc(100%-2rem)] min-w-0 grid-rows-[auto_auto_minmax(0,1fr)] overflow-hidden sm:max-w-3xl"
        >
            <DialogHeader>
                <DialogTitle>
                    {{ t('common.appearance.title') }}
                </DialogTitle>

                <DialogDescription>
                    {{ t('common.appearance.description') }}
                </DialogDescription>
            </DialogHeader>

            <div class="grid min-w-0 grid-cols-1 gap-3 sm:grid-cols-2">
                <button
                    type="button"
                    :aria-pressed="appearance === 'restaurant-default'"
                    class="flex items-center gap-3 rounded-lg border bg-card p-3 text-left transition-colors hover:border-primary/50"
                    :class="
                        appearance === 'restaurant-default'
                            ? 'border-primary ring-2 ring-primary/30'
                            : 'border-border'
                    "
                    @click="selectPreference('restaurant-default')"
                >
                    <span
                        class="flex size-9 shrink-0 items-center justify-center rounded-md bg-muted text-muted-foreground"
                    >
                        <Store class="size-4" />
                    </span>

                    <span class="min-w-0 flex-1">
                        <span
                            class="block text-sm font-medium text-card-foreground"
                        >
                            {{ t('common.appearance.restaurantDefault.title') }}
                        </span>

                        <span class="block text-xs text-muted-foreground">
                            {{
                                t(
                                    'common.appearance.restaurantDefault.description',
                                )
                            }}
                        </span>
                    </span>

                    <span
                        v-if="appearance === 'restaurant-default'"
                        class="flex size-5 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground"
                    >
                        <Check class="size-3.5" />
                    </span>
                </button>

                <button
                    type="button"
                    :aria-pressed="appearance === 'system'"
                    class="flex items-center gap-3 rounded-lg border bg-card p-3 text-left transition-colors hover:border-primary/50"
                    :class="
                        appearance === 'system'
                            ? 'border-primary ring-2 ring-primary/30'
                            : 'border-border'
                    "
                    @click="selectPreference('system')"
                >
                    <span
                        class="flex size-9 shrink-0 items-center justify-center rounded-md bg-muted text-muted-foreground"
                    >
                        <Monitor class="size-4" />
                    </span>

                    <span class="min-w-0 flex-1">
                        <span
                            class="block text-sm font-medium text-card-foreground"
                        >
                            {{ t('common.appearance.system.title') }}
                        </span>

                        <span class="block text-xs text-muted-foreground">
                            {{ t('common.appearance.system.description') }}
                        </span>
                    </span>

                    <span
                        v-if="appearance === 'system'"
                        class="flex size-5 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground"
                    >
                        <Check class="size-3.5" />
                    </span>
                </button>
            </div>

            <div class="flex min-h-0 min-w-0 flex-col">
                <p
                    class="mb-2 shrink-0 text-sm font-medium text-muted-foreground"
                >
                    {{ t('common.appearance.themes') }}
                </p>

                <div
                    class="theme-list min-h-0 flex-1 overflow-y-auto overscroll-contain p-1"
                >
                    <div
                        class="grid min-w-0 grid-cols-3 gap-2 sm:grid-cols-4 lg:grid-cols-5"
                    >
                        <ThemePreviewCard
                            v-for="theme in availableThemes"
                            :key="theme.key"
                            :theme="theme"
                            :selected="appearance === theme.key"
                            @select="selectTheme"
                        />
                    </div>
                </div>
            </div>
        </DialogScrollContent>
    </Dialog>
</template>

<style scoped>
.theme-list {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.theme-list::-webkit-scrollbar {
    display: none;
}
</style>
