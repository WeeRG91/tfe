import axios from 'axios';
import { Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

type ValidationErrorResponse = {
    message?: string;
    errors?: Record<string, string[]>;
};

export function usePickupErrorHandling(
    pickupTime: Ref<string>,
    refreshAvailability: () => Promise<boolean>,
) {
    const { t } = useI18n();

    const handlePickupError = async (error: unknown): Promise<boolean> => {
        if (!axios.isAxiosError<ValidationErrorResponse>(error)) {
            return false;
        }

        if (error.response?.status !== 422) {
            return false;
        }

        const pickupErrors = error.response.data.errors?.pickup_time;

        if (!Array.isArray(pickupErrors) || pickupErrors.length === 0) {
            return false;
        }

        pickupTime.value = '';

        toast.error(t('restaurant.pickupUnavailable'), {
            duration: 8_000,
        });

        await refreshAvailability();

        return true;
    };

    return { handlePickupError };
}
