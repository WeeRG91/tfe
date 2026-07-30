<script setup lang="ts">
import HomeLandingPage from '@/components/client/home/HomeLandingPage.vue';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <Head :title="t('home.pageTitle')" />
    <HomeLandingPage />
</template>

<style scoped></style>
