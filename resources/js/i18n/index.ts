import en from '@/i18n/locales/en/index';
import fr from '@/i18n/locales/fr/index';
import lb from '@/i18n/locales/lb/index';
import th from '@/i18n/locales/th/index';
import { createI18n } from 'vue-i18n';

export const supportedLocales = ['en', 'fr', 'lb', 'th'] as const;

export type SupportedLocale = (typeof supportedLocales)[number];

const messages = {
    en,
    fr,
    lb,
    th,
};

function isSupportedLocale(locale: unknown): locale is SupportedLocale {
    return (
        typeof locale === 'string' &&
        supportedLocales.includes(locale as SupportedLocale)
    );
}

export function createI18nInstance(locale: unknown) {
    const selectedLocale: SupportedLocale = isSupportedLocale(locale)
        ? locale
        : 'en';

    return createI18n({
        legacy: false,
        globalInjection: true,
        locale: selectedLocale,
        fallbackLocale: 'en',
        messages,
    });
}
