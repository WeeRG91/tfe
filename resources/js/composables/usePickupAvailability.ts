import {
    PickupAvailability,
    PickupAvailabilityResponse,
} from '@/types/restaurant';
import axios from 'axios';
import { onMounted, onScopeDispose, ref } from 'vue';

export function usePickupAvailability() {
    const availability = ref<PickupAvailability | null>(null);
    const isLoading = ref<boolean>(true);
    const hasError = ref<boolean>(false);

    let requestId = 0;

    const refresh = async (): Promise<boolean> => {
        const id = ++requestId;

        isLoading.value = true;
        hasError.value = false;
        availability.value = null;

        try {
            const response = await axios.get<PickupAvailabilityResponse>(
                '/restaurant/availability',
                {
                    params: { days: 7 },
                },
            );

            if (id !== requestId) {
                return false;
            }

            availability.value = response.data.data.pickup;

            return true;
        } catch (error) {
            if (id === requestId) {
                hasError.value = true;
            }

            console.error('Failed to fetch pickup availability:', error);

            return false;
        } finally {
            if (id === requestId) {
                isLoading.value = false;
            }
        }
    };

    const isAvailableSlot = (value: string): boolean => {
        if (
            !value ||
            isLoading.value ||
            hasError.value ||
            !availability.value
        ) {
            return false;
        }

        const timestamp = new Date(value).getTime();

        if (!Number.isFinite(timestamp) || timestamp <= Date.now()) {
            return false;
        }

        return availability.value.dates.some((date) =>
            date.slots.some((slot) => slot.value === value),
        );
    };

    onMounted(() => {
        void refresh();
    })

    onScopeDispose(() => {
        requestId += 1
    })

    return {
        availability,
        isLoading,
        hasError,
        refresh,
        isAvailableSlot,
    }
}
