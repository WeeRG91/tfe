import type { AppPageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

export function useLocale() {
    const page = usePage<AppPageProps>();
    const { locale } = useI18n();

    const getIntlLocale = (): string => {
        const currentLocale = locale.value as AppPageProps['locale'];

        return (
            page.props.availableLocales[currentLocale]?.intl ??
            page.props.availableLocales[page.props.fallbackLocale]?.intl ??
            currentLocale
        );
    };

    return {
        getIntlLocale,
    };
}
