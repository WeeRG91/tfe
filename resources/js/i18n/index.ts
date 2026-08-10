import en from '@/i18n/locales/en/index';
import fr from '@/i18n/locales/fr/index';
import lb from '@/i18n/locales/lb/index';
import th from '@/i18n/locales/th/index';
import { createI18n } from 'vue-i18n';

const messages = {
    en,
    fr,
    lb,
    th,
};

export type SupportedLocale = keyof typeof messages;

export const supportedLocales = Object.keys(messages) as SupportedLocale[];

function isSupportedLocale(locale: unknown): locale is SupportedLocale {
    return (
        typeof locale === 'string' &&
        supportedLocales.includes(locale as SupportedLocale)
    );
}

export function createI18nInstance(locale: unknown, fallbackLocale: unknown) {
    const selectedLocale: SupportedLocale = isSupportedLocale(locale)
        ? locale
        : 'en';

    const selectedFallbackLocale: SupportedLocale = isSupportedLocale(
        fallbackLocale,
    )
        ? fallbackLocale
        : 'en';

    return createI18n({
        legacy: false,
        globalInjection: true,
        locale: selectedLocale,
        fallbackLocale: selectedFallbackLocale,
        messages,
    });
}
