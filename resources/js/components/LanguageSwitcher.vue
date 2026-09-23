<script setup lang="ts">
import { supportedLocales, type SupportedLocale } from '@/i18n';
import type { LocaleMetadata } from '@/types/locale';
import { router, usePage } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const page = usePage();
const { locale: i18nLocale } = useI18n({ useScope: 'global' });

const isOpen = ref(false);
const isChangingLocale = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const currentLocale = computed(() => page.props.locale as SupportedLocale);

const availableLocales = computed(
    () =>
        page.props.availableLocales as Record<SupportedLocale, LocaleMetadata>,
);

watch(
    currentLocale,
    (locale) => {
        if (supportedLocales.includes(locale)) {
            i18nLocale.value = locale;
            document.documentElement.lang = locale;
        }
    },
    { immediate: true },
);

const changeLocale = (locale: SupportedLocale) => {
    if (locale === currentLocale.value || isChangingLocale.value) {
        isOpen.value = false;
        return;
    }

    isChangingLocale.value = true;

    router.post(
        '/locale',
        { locale },
        {
            preserveScroll: true,
            preserveState: false,

            onFinish: () => {
                isChangingLocale.value = false;
                isOpen.value = false;
            },
        },
    );
};

const toggleDropdown = () => {
    if (!isChangingLocale.value) {
        isOpen.value = !isOpen.value;
    }
};

const closeDropdown = (event: MouseEvent) => {
    if (
        dropdownRef.value &&
        !dropdownRef.value.contains(event.target as Node)
    ) {
        isOpen.value = false;
    }
};

const closeOnEscape = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
        isOpen.value = false;
    }
};

watch(isOpen, (open) => {
    if (open) {
        document.addEventListener('click', closeDropdown);
        document.addEventListener('keydown', closeOnEscape);
    } else {
        document.removeEventListener('click', closeDropdown);
        document.removeEventListener('keydown', closeOnEscape);
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('click', closeDropdown);
    document.removeEventListener('keydown', closeOnEscape);
});
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <button
            type="button"
            class="flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-60"
            :aria-label="$t('common.language')"
            :aria-expanded="isOpen"
            :disabled="isChangingLocale"
            aria-haspopup="menu"
            @click="toggleDropdown"
        >
            <img
                :src="availableLocales[currentLocale].flag"
                :alt="availableLocales[currentLocale].label"
                class="h-4 w-6 shrink-0 rounded-sm object-cover shadow-sm"
            />
        </button>

        <Transition name="dropdown">
            <div
                v-if="isOpen"
                role="menu"
                :aria-label="$t('common.language')"
                class="absolute top-full right-0 z-50 mt-2 w-48 overflow-hidden rounded-xl border border-border bg-popover text-popover-foreground shadow-xl ring-1 ring-border/50 sm:w-56"
            >
                <div class="p-1.5 sm:p-2">
                    <button
                        v-for="(metadata, locale) in availableLocales"
                        :key="locale"
                        type="button"
                        role="menuitemradio"
                        :aria-checked="locale === currentLocale"
                        :disabled="isChangingLocale"
                        class="flex w-full cursor-pointer items-center gap-2.5 rounded-lg px-3 py-2.5 text-left transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:bg-accent focus-visible:text-accent-foreground focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-60 sm:gap-3"
                        :class="{
                            'bg-primary/10 font-medium text-primary':
                                locale === currentLocale,
                            'text-popover-foreground': locale !== currentLocale,
                        }"
                        @click="changeLocale(locale)"
                    >
                        <img
                            :src="metadata.flag"
                            :alt="metadata.label"
                            class="h-5 w-7 shrink-0 rounded-sm object-cover shadow-sm"
                        />

                        <span class="min-w-0 flex-1 truncate text-sm">
                            {{ metadata.label }}
                        </span>

                        <Check
                            v-if="locale === currentLocale"
                            class="h-4 w-4 shrink-0 text-primary"
                        />
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
