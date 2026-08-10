import type { SupportedLocale } from '@/i18n';

export type LocaleType = SupportedLocale;

export type LocaleMetadata = {
    label: string;
    intl: string;
    flag: string;
};
