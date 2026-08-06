<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AdminLayout from '@/layouts/AdminLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { disable, enable, show } from '@/routes/two-factor';
import { BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { ShieldBan, ShieldCheck } from 'lucide-vue-next';
import { computed, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

interface Props {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
}

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('setting.twoFactorAuth.title'),
        href: show.url(),
    },
]);

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbs">
        <Head :title="t('setting.twoFactorAuth.title')" />
        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    :title="t('setting.twoFactorAuth.heading.title')"
                    :description="
                        t('setting.twoFactorAuth.heading.description')
                    "
                />

                <div
                    v-if="!twoFactorEnabled"
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <Badge variant="destructive">
                        {{ t('setting.twoFactorAuth.status.disabled') }}
                    </Badge>

                    <p class="text-muted-foreground">
                        {{ t('setting.twoFactorAuth.descriptions.disabled') }}
                    </p>

                    <div>
                        <Button
                            v-if="hasSetupData"
                            @click="showSetupModal = true"
                        >
                            <ShieldCheck />{
                            t('setting.twoFactorAuth.buttons.continueSetup') }}
                        </Button>
                        <Form
                            v-else
                            v-bind="enable.form()"
                            @success="showSetupModal = true"
                            #default="{ processing }"
                        >
                            <Button type="submit" :disabled="processing">
                                <ShieldCheck />
                                {{ t('setting.twoFactorAuth.buttons.enable') }}
                            </Button>
                        </Form>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <Badge variant="default">
                        {{ t('setting.twoFactorAuth.status.enabled') }}
                    </Badge>

                    <p class="text-muted-foreground">
                        {{ t('setting.twoFactorAuth.descriptions.enabled') }}
                    </p>

                    <TwoFactorRecoveryCodes />

                    <div class="relative inline">
                        <Form v-bind="disable.form()" #default="{ processing }">
                            <Button
                                variant="destructive"
                                type="submit"
                                :disabled="processing"
                            >
                                <ShieldBan />
                                {{ t('setting.twoFactorAuth.buttons.disable') }}
                            </Button>
                        </Form>
                    </div>
                </div>

                <TwoFactorSetupModal
                    v-model:isOpen="showSetupModal"
                    :requiresConfirmation="requiresConfirmation!"
                    :twoFactorEnabled="twoFactorEnabled!"
                />
            </div>
        </SettingsLayout>
    </AdminLayout>
</template>
