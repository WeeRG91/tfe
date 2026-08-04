import { locales } from '@/lib/const';
import { useI18n } from 'vue-i18n';

const luxembourgishMonths = [
    'Januar',
    'Februar',
    'Mäerz',
    'Abrëll',
    'Mee',
    'Juni',
    'Juli',
    'August',
    'September',
    'Oktober',
    'November',
    'Dezember',
];

function formatLuxembourgishDate(
    date: Date,
    includeTime: boolean,
): string {
    const day = date.getDate();
    const month = luxembourgishMonths[date.getMonth()];
    const year = date.getFullYear();

    const formattedDate = `${day} ${month} ${year}`;

    if (!includeTime) {
        return formattedDate;
    }

    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${formattedDate} um ${hours}:${minutes}`;
}

export function useDateFormatter() {
    const { t, locale } = useI18n();

    const getLocale = (): string => {
        return locales[locale.value] ?? 'en-GB';
    };

    const isLuxembourgish = (): boolean => {
        return locale.value === 'lb';
    };

    const formatDate = (value: string | null): string => {
        if (!value) {
            return t('common.notSet');
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return t('common.invalidDate');
        }

        if (isLuxembourgish()) {
            return formatLuxembourgishDate(date, true);
        }

        return new Intl.DateTimeFormat(getLocale(), {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    };

    const formatDateOnly = (value: string | null): string => {
        if (!value) {
            return t('common.notSet');
        }

        const date = new Date(value);

        if (isLuxembourgish()) {
            return formatLuxembourgishDate(date, false);
        }

        return new Intl.DateTimeFormat(getLocale(), {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        }).format(date);
    };

    const formatDateForHumans = (
        value: string | null,
    ): string => {
        if (!value) {
            return t('common.notSet');
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return t('common.invalidDate');
        }

        const difference = Date.now() - date.getTime();

        const minutes = Math.floor(difference / 60_000);
        const hours = Math.floor(difference / 3_600_000);
        const days = Math.floor(difference / 86_400_000);

        if (minutes < 1) {
            return t('common.timeAgo.justNow');
        }

        if (minutes < 60) {
            return t(
                minutes === 1
                    ? 'common.timeAgo.minute'
                    : 'common.timeAgo.minutes',
                { count: minutes },
            );
        }

        if (hours < 24) {
            return t(
                hours === 1
                    ? 'common.timeAgo.hour'
                    : 'common.timeAgo.hours',
                { count: hours },
            );
        }

        if (days < 7) {
            return t(
                days === 1
                    ? 'common.timeAgo.day'
                    : 'common.timeAgo.days',
                { count: days },
            );
        }

        return formatDateOnly(value);
    };

    return {
        formatDate,
        formatDateOnly,
        formatDateForHumans,
    };
}
