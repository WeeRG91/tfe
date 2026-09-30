<script setup lang="ts">
import HomeLandingPage from '@/components/client/home/HomeLandingPage.vue';
import type { ClientDishType } from '@/types/dish';
import type { MobileAppAvailability } from '@/types/mobile-app';
import type { HomepageReviewType } from '@/types/rating';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineProps<{
    recommendedDishes: ClientDishType[];
    homepageReviews: HomepageReviewType[];
    mobileApp: MobileAppAvailability;
}>();

const { t } = useI18n();
</script>

<template>
    <Head :title="t('home.pageTitle')" />
    <HomeLandingPage
        :recommended-dishes="recommendedDishes"
        :homepage-reviews="homepageReviews"
        :mobile-app="mobileApp"
    />
</template>

<style scoped></style>
