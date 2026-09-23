<script setup lang="ts">
import InfoTab from '@/components/client/profile/InfoTab.vue';
import PasswordTab from '@/components/client/profile/PasswordTab.vue';
import TwoFactorTab from '@/components/client/profile/TwoFactorTab.vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Key, QrCode, UserCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    twoFactorAuthEnabled: boolean;
    mustVerifyEmail: boolean;
}>();

const page = usePage();

const { t } = useI18n();

const user = computed(() => page.props.auth?.user);
type TabType = 'info' | 'password' | 'two-factor';

const activeTab = ref<TabType>('info');

const tabs = computed(() => [
    {
        id: 'info' as TabType,
        label: t('profile.tabs.info.label'),
        icon: UserCircle,
        description: t('profile.tabs.info.description'),
    },
    {
        id: 'password' as TabType,
        label: t('profile.tabs.password.label'),
        icon: Key,
        description: t('profile.tabs.password.description'),
    },
    {
        id: 'two-factor' as TabType,
        label: t('profile.tabs.twoFactor.label'),
        icon: QrCode,
        description: t('profile.tabs.twoFactor.description'),
    },
]);
</script>

<template>
    <Head :title="t('profile.pageTitle')" />

    <ClientLayout>
        <section class="mx-auto max-w-7xl px-4 py-3 md:px-6 md:py-4">
            <div class="mb-6 md:mb-8">
                <p
                    class="text-xs tracking-widest text-primary uppercase md:text-sm"
                >
                    [ {{ t('profile.eyebrow') }} ]
                </p>
                <h1 class="text-4xl font-semibold uppercase md:text-5xl">
                    {{ t('profile.title') }}
                </h1>
                <p
                    class="mt-0.5 text-xs text-muted-foreground md:mt-1 md:text-sm"
                >
                    {{ t('profile.description') }}
                </p>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row lg:gap-8">
                <div class="lg:w-72 lg:flex-shrink-0">
                    <div
                        class="sticky top-6 rounded-2xl border border-border bg-card p-2 text-card-foreground shadow-sm"
                    >
                        <nav class="space-y-1">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                class="group flex w-full items-start gap-3 rounded-xl px-4 py-3 text-left transition-all duration-200"
                                :class="
                                    activeTab === tab.id
                                        ? 'bg-gradient-to-r from-primary/10 to-primary/5 text-primary shadow-sm'
                                        : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                                "
                            >
                                <div
                                    class="mt-0.5 rounded-lg p-1.5 transition-colors"
                                    :class="
                                        activeTab === tab.id
                                            ? 'bg-primary text-primary-foreground'
                                            : 'bg-muted text-muted-foreground group-hover:bg-accent group-hover:text-accent-foreground'
                                    "
                                >
                                    <component :is="tab.icon" class="h-5 w-5" />
                                </div>
                                <div class="flex-1">
                                    <p
                                        class="text-sm font-medium"
                                        :class="
                                            activeTab === tab.id
                                                ? 'text-primary'
                                                : 'text-card-foreground'
                                        "
                                    >
                                        {{ tab.label }}
                                    </p>
                                    <p
                                        class="text-xs"
                                        :class="
                                            activeTab === tab.id
                                                ? 'text-primary/80'
                                                : 'text-muted-foreground'
                                        "
                                    >
                                        {{ tab.description }}
                                    </p>
                                </div>
                            </button>
                        </nav>
                    </div>
                </div>

                <div class="min-w-0 flex-1">
                    <div
                        class="max-w-2xl rounded-2xl border border-border bg-card p-4 text-card-foreground shadow-sm md:p-6 lg:p-8"
                    >
                        <InfoTab
                            v-if="activeTab === 'info' && user"
                            :user="user"
                            :must-verify-email="props.mustVerifyEmail"
                        />

                        <PasswordTab v-else-if="activeTab === 'password'" />

                        <TwoFactorTab
                            v-else-if="activeTab === 'two-factor' && user"
                            :two-factor-auth-enabled="twoFactorAuthEnabled"
                            :user="user"
                        />
                    </div>
                </div>
            </div>
        </section>
    </ClientLayout>
</template>

<style scoped>
.group {
    transition: all 0.2s ease;
}

.group:hover {
    transform: translateX(2px);
}

@media (max-width: 1023px) {
    .sticky {
        position: relative;
        top: 0;
    }
}
</style>
