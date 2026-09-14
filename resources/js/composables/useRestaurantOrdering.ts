import { useRestaurantStore } from '@/stores/restaurant';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';
import { ref } from 'vue';
import axios from 'axios';

type RestaurantClosedResponse = {
    message?: string;
    code?: string;
    status?: string;
}

export function useRestaurantOrdering() {
    const restaurantStore = useRestaurantStore();
    const { t } = useI18n();

    const isChecking = ref<boolean>(false);

    const canStartOrdering = async (): Promise<boolean> => {
        if (isChecking.value) {
            return false;
        }

        isChecking.value = true;

        try {
            const refreshed = await restaurantStore.refresh();

            if (!refreshed) {
                toast.error(t('restaurant.statusUnavailable'));

                return false;
            }

            if (!restaurantStore.acceptingOrders) {
                restaurantStore.showClosedModal();

                return false;
            }

            return true;
        } finally {
            isChecking.value = false;
        }
    };

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

        restaurantStore.showClosedModal(
            error.response.data.message || t('restaurant.closed'),
        );

        void restaurantStore.refresh();

        return true;
    }

    return { canStartOrdering, handleClosureError };
}
