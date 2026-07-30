import type { ComposerTranslation } from 'vue-i18n';

export const getSpicyLevelLabels = (t: ComposerTranslation) => [
    t('common.spicyLevel.noSpicy'),
    t('common.spicyLevel.mild'),
    t('common.spicyLevel.spicy'),
    t('common.spicyLevel.hot'),
];

export const locales: Record<string, string> = {
    en: 'en-GB',
    fr: 'fr-FR',
    lb: 'lb-LU',
    th: 'th-TH',
};
