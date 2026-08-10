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
            class="flex min-h-10 items-center gap-2 rounded-lg px-2.5 py-2 text-gray-600 transition-colors hover:bg-red-50 hover:text-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 sm:min-h-11 sm:px-3"
            :aria-label="$t('common.language')"
            :aria-expanded="isOpen"
            :disabled="isChangingLocale"
            aria-haspopup="menu"
            @click="toggleDropdown"
        >
            <img
                :src="availableLocales[currentLocale].flag"
                :alt="availableLocales[currentLocale].label"
                class="h-5 w-7 shrink-0 rounded-sm object-cover shadow-sm"
            />
        </button>

        <Transition name="dropdown">
            <div
                v-if="isOpen"
                role="menu"
                :aria-label="$t('common.language')"
                class="absolute top-full right-0 z-50 mt-2 w-48 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl ring-1 ring-black/5 sm:w-56"
            >
                <div class="p-1.5 sm:p-2">
                    <button
                        v-for="(metadata, locale) in availableLocales"
                        :key="locale"
                        type="button"
                        role="menuitemradio"
                        :aria-checked="locale === currentLocale"
                        :disabled="isChangingLocale"
                        class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2.5 text-left transition-colors hover:bg-red-50 hover:text-red-600 focus:outline-none focus-visible:bg-red-50 focus-visible:text-red-600 disabled:cursor-not-allowed disabled:opacity-60 sm:gap-3"
                        :class="{
                            'bg-red-50 font-medium text-red-600':
                                locale === currentLocale,
                            'text-gray-700': locale !== currentLocale,
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
                            class="h-4 w-4 shrink-0 text-red-500"
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
