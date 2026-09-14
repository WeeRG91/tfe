import { useRestaurantStore } from '@/stores/restaurant';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';

function calendarKey(value: Date, timezone: string): string {
    const parts = new Intl.DateTimeFormat('en-US', {
        timeZone: timezone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).formatToParts(value);

    const year = parts.find((part) => part.type === 'year')!.value;
    const month = parts.find((part) => part.type === 'month')!.value;
    const day = parts.find((part) => part.type === 'day')!.value;

    return `${year}-${month}-${day}`;
}

export function useRestaurantClosureMessage() {
    const restaurantStore = useRestaurantStore();
    const { t, locale } = useI18n();

    const message = computed(() => {
        const status = restaurantStore.current;

        const baseMessage = status?.message?.trim() || t('restaurant.closed');

        if (!status || status.is_open || !status.next_open_at) {
            return baseMessage;
        }

        const nextOpen = new Date(status.next_open_at);
        const checkedAt = new Date(status.checked_at);

        if (
            !Number.isFinite(nextOpen.getTime()) ||
            !Number.isFinite(checkedAt.getTime())
        ) {
            return baseMessage;
        }

        const todayKey = calendarKey(checkedAt, status.timezone);
        const openingKey = calendarKey(nextOpen, status.timezone);

        // Advance the restaurant's calendar date, not
        // the timestamp by a fixed 24-hour duration.
        const tomorrow = new Date(`${todayKey}T12:00:00Z`);
        tomorrow.setUTCDate(tomorrow.getUTCDate() + 1);

        const tomorrowKey = tomorrow.toISOString().slice(0, 10);

        const time = new Intl.DateTimeFormat(locale.value, {
            timeZone: status.timezone,
            hour: '2-digit',
            minute: '2-digit',
            hourCycle: 'h23',
        }).format(nextOpen);

        let reopeningMessage: string;

        if (openingKey === todayKey) {
            reopeningMessage = t('restaurant.reopensToday', {
                time,
            });
        } else if (openingKey === tomorrowKey) {
            reopeningMessage = t('restaurant.reopensTomorrow', {
                time,
            });
        } else {
            const differentYear =
                openingKey.slice(0, 4) !== todayKey.slice(0, 4);

            const date = new Intl.DateTimeFormat(locale.value, {
                timeZone: status.timezone,
                day: 'numeric',
                month: 'long',
                year: differentYear ? 'numeric' : undefined,
            }).format(nextOpen);

            reopeningMessage = t('restaurant.reopensOn', {
                date,
                time,
            });
        }

        return `${baseMessage} ${reopeningMessage}`;
    });

    return { message };
}
