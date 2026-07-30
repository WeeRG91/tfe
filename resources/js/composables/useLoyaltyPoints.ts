import { computed, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';

export type PointOption = {
    points: number;
    discount: number;
    label: string;
};

export function useLoyaltyPoints(userPoints: Ref<number>) {
    const { t } = useI18n();

    const selectedPoints = ref<number>(0);

    const pointsOptions = computed<PointOption[]>(() => [
        {
            points: 300,
            discount: 5,
            label: t('cart.loyaltyPoints.options', {
                points: 300,
                amount: 5,
            }),
        },
        {
            points: 550,
            discount: 10,
            label: t('cart.loyaltyPoints.options', {
                points: 550,
                amount: 10,
            }),
        },
    ]);

    const selectedOption = computed(() =>
        pointsOptions.value.find((opt) => opt.points === selectedPoints.value),
    );

    const discountAmount = computed(() => {
        return selectedOption.value?.discount ?? 0;
    });

    const canUseOption = (pointsRequired: number) => {
        return userPoints.value >= pointsRequired;
    };

    const applyPoints = (points: number) => {
        selectedPoints.value = points;
    };

    const removePoints = () => {
        selectedPoints.value = 0;
    };

    return {
        pointsOptions,
        selectedPoints,
        discountAmount,
        applyPoints,
        removePoints,
        canUseOption,
    };
}
