<script setup lang="ts">
import HomeLandingPage from '@/components/client/home/HomeLandingPage.vue';
import { ClientDishType } from '@/types/dish';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineProps<{
    recommendedDishes: ClientDishType[];
}>();

const { t } = useI18n();
</script>

<template>
    <Head :title="t('home.pageTitle')" />
    <HomeLandingPage :recommended-dishes="recommendedDishes" />
</template>

<style scoped></style>
