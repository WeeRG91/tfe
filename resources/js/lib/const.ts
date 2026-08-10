import type { ComposerTranslation } from 'vue-i18n';

export const getSpicyLevelLabels = (t: ComposerTranslation) => [
    t('common.spicyLevel.noSpicy'),
    t('common.spicyLevel.mild'),
    t('common.spicyLevel.spicy'),
    t('common.spicyLevel.hot'),
];
