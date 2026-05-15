import { computed, ref, Ref } from 'vue';

export type PointsOptions = {
    points: number;
    discount: number;
    label: string;
};

export function useLoyaltyPoints(userPoints: Ref<number>) {
    const selectedPoints = ref<number>(0);

    const pointsOptions: PointsOptions[] = [
        { points: 300, discount: 5, label: '300 points -> €5 off' },
        { points: 550, discount: 10, label: '550 points -> €5 off' },
    ];

    const selectedOption = computed(() =>
        pointsOptions.find((opt) => opt.points === selectedPoints.value),
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
