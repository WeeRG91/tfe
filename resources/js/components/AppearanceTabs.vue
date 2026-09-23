<script setup lang="ts">
import ThemePreviewCard from '@/components/theme/ThemePreviewCard.vue';
import { useAppearance } from '@/composables/useAppearance';
import type { ThemeDefinition } from '@/types/theme';
import { Check, Monitor } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

defineProps<{
    themes: ThemeDefinition[];
}>();

const { t } = useI18n();
const { appearance, updateAppearance } = useAppearance('admin');

function selectTheme(theme: ThemeDefinition): void {
    updateAppearance(theme.key);
}
</script>

<template>
    <div class="space-y-5">
        <button
            type="button"
            :aria-pressed="appearance === 'system'"
            class="flex w-full items-center gap-3 rounded-lg border bg-card p-3 text-left transition-all hover:border-primary/50 hover:shadow-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
            :class="
                appearance === 'system'
                    ? 'border-primary ring-2 ring-primary/30'
                    : 'border-border'
            "
            @click="updateAppearance('system')"
        >
            <span
                class="flex size-9 shrink-0 items-center justify-center rounded-md bg-muted text-muted-foreground"
            >
                <Monitor class="size-4" />
            </span>

            <span class="min-w-0 flex-1">
                <span class="block font-medium text-card-foreground">
                    {{ t('setting.appearance.options.system') }}
                </span>

                <span class="block text-sm text-muted-foreground">
                    {{ t('setting.appearance.options.systemDescription') }}
                </span>
            </span>

            <span
                v-if="appearance === 'system'"
                class="flex size-6 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground"
            >
                <Check class="size-4" />
            </span>
        </button>

        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-4">
            <ThemePreviewCard
                v-for="theme in themes"
                :key="theme.key"
                :theme="theme"
                :selected="appearance === theme.key"
                @select="selectTheme"
            />
        </div>
    </div>
</template>
