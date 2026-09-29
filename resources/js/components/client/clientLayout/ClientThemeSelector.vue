<script setup lang="ts">
import ThemePreviewCard from '@/components/theme/ThemePreviewCard.vue';
import { useAppearance } from '@/composables/useAppearance';
import { getAvailableThemes } from '@/themes/registry';
import { AppPageProps } from '@/types';
import type { ThemeDefinition, ThemeSelection } from '@/types/theme';
import { usePage } from '@inertiajs/vue3';
import { Check, Monitor, Palette, Store, X } from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const page = usePage<AppPageProps>();
const { t } = useI18n();
const { appearance, updateAppearance } = useAppearance('client');

const open = ref(false);

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

function openModal(): void {
    open.value = true;
}

function closeModal(): void {
    open.value = false;
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        closeModal();
    }
}

watch(open, async (isOpen) => {
    if (isOpen) {
        await nextTick();
        window.addEventListener('keydown', onKeydown);
        document.body.style.overflow = 'hidden';
    } else {
        window.removeEventListener('keydown', onKeydown);
        document.body.style.overflow = '';
    }
});
</script>

<template>
    <button
        type="button"
        class="flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
        :aria-label="t('common.appearance.open')"
        :title="t('common.appearance.open')"
        @click="openModal"
    >
        <Palette class="size-5" />
    </button>

    <Teleport to="body">
        <Transition name="theme-modal" appear>
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-4 backdrop-blur-sm"
                @click.self="closeModal"
            >
                <div
                    class="theme-modal-panel relative grid h-[65vh] max-h-[48rem] w-[calc(100%-2rem)] max-w-3xl min-w-0 grid-rows-[auto_auto_minmax(0,1fr)] overflow-hidden rounded-2xl border border-border bg-popover text-popover-foreground shadow-2xl"
                >
                    <div
                        class="border-b border-border px-5 py-4 sm:px-6 sm:py-5"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold sm:text-xl">
                                    {{ t('common.appearance.title') }}
                                </h2>

                                <p class="mt-1 text-sm text-muted-foreground">
                                    {{ t('common.appearance.description') }}
                                </p>
                            </div>

                            <button
                                type="button"
                                class="rounded-full p-2 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                :aria-label="t('common.close')"
                                @click="closeModal"
                            >
                                <X class="size-5" />
                            </button>
                        </div>
                    </div>

                    <div
                        class="grid min-w-0 grid-cols-1 gap-3 border-b border-border bg-muted/30 px-5 py-4 sm:grid-cols-2 sm:px-6"
                    >
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
                                    {{
                                        t(
                                            'common.appearance.restaurantDefault.title',
                                        )
                                    }}
                                </span>

                                <span
                                    class="block text-xs text-muted-foreground"
                                >
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

                                <span
                                    class="block text-xs text-muted-foreground"
                                >
                                    {{
                                        t(
                                            'common.appearance.system.description',
                                        )
                                    }}
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

                    <div
                        class="flex min-h-0 min-w-0 flex-col px-5 py-4 sm:px-6"
                    >
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
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.theme-modal-enter-active,
.theme-modal-leave-active {
    transition: opacity 200ms ease;
}

.theme-modal-enter-active .theme-modal-panel,
.theme-modal-leave-active .theme-modal-panel {
    transition:
        opacity 200ms ease,
        transform 200ms ease;
}

.theme-modal-enter-from,
.theme-modal-leave-to {
    opacity: 0;
}

.theme-modal-enter-from .theme-modal-panel,
.theme-modal-leave-to .theme-modal-panel {
    opacity: 0;
    transform: translateY(-1rem) scale(0.95);
}

.theme-list {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.theme-list::-webkit-scrollbar {
    display: none;
}
</style>
