import { useRestaurantStore } from '@/stores/restaurant';
import axios from 'axios';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

type RestaurantClosedResponse = {
    message?: string;
    code?: string;
    status?: string;
};

export function useRestaurantOrdering() {
    const restaurantStore = useRestaurantStore();
    const { t } = useI18n();

    const handleClosureError = (error: unknown): boolean => {
        if (!axios.isAxiosError<RestaurantClosedResponse>(error)) {
            return false;
        }

        if (
            error.response?.status !== 409 ||
            error.response.data.code !== 'restaurant_closed'
        ) {
            return false;
        }

        toast.error(error.response.data.message || t('restaurant.closed'));

        void restaurantStore.refresh();

        return true;
    };

    return { handleClosureError };
}
