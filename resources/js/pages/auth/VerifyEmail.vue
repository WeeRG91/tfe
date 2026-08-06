<script setup lang="ts">
import { login } from '@/routes';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { MoveLeft, MoveRight } from 'lucide-vue-next';
import CostumedAuthLayout from '@/layouts/CostumedAuthLayout.vue';
import { toast } from 'vue-sonner';
import verification from '@/routes/verification';
import { useI18n } from 'vue-i18n';

defineProps<{
    status?: string;
}>();

const { t } = useI18n();

const verifyForm = useForm({});

const submit = () => {
    verifyForm.post(verification.send().url, {
        onSuccess: () => {
            toast.success(t('verifyEmail.messages.verificationEmailSent'));
        },
        onError: () => {
            toast.error(t('verifyEmail.messages.error'));
        },
    });
};
</script>

<template>
    <Head :title="t('verifyEmail.title')" />

    <CostumedAuthLayout
        :title="t('verifyEmail.heading')"
        :description="t('verifyEmail.description')"
        :badge="t('verifyEmail.badge')"
    >
        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ t('verifyEmail.messages.verificationLinkSent') }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <button
                type="submit"
                :disabled="verifyForm.processing"
                class="group w-full rounded-2xl bg-gray-900 px-4 py-3 text-sm font-medium text-white transition-all duration-200 hover:bg-gray-800 hover:shadow-lg hover:shadow-gray-900/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-gray-900 disabled:hover:shadow-none"
            >
                <span class="flex items-center justify-center gap-2">
                    <span
                        v-if="verifyForm.processing"
                        class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                    ></span>
                    <span v-else>
                        {{ t('auth.buttons.resendVerificationEmail') }}
                    </span>
                </span>
            </button>
        </form>

        <template #footer>
            <div class="space-y-6">
                <div
                    class="rounded-2xl border border-gray-100 bg-white/50 p-4 backdrop-blur-sm"
                >
                    <p class="text-center text-sm text-gray-500">
                        {{ t('auth.links.needHelp') }}
                        <Link
                            href="/contact"
                            class="font-medium text-gray-900 transition-colors hover:text-gray-600"
                        >
                            {{ t('auth.links.contactSupport') }}
                        </Link>
                    </p>
                </div>

                <div class="flex items-center justify-center gap-8">
                    <Link
                        :href="login()"
                        class="group flex items-center gap-1.5 text-sm text-gray-500 transition-all hover:text-gray-900"
                    >
                        <MoveLeft
                            class="h-4 w-4 transition-transform group-hover:-translate-x-0.5"
                        />
                        {{ t('auth.links.backToSignIn') }}
                    </Link>

                    <div class="h-4 w-px bg-gray-200"></div>

                    <Link
                        href="/"
                        class="group flex items-center gap-1.5 text-sm text-gray-500 transition-all hover:text-gray-900"
                    >
                        {{ t('auth.links.goToHome') }}
                        <MoveRight
                            class="h-4 w-4 transition-transform group-hover:translate-x-0.5"
                        />
                    </Link>
                </div>
            </div>
        </template>
    </CostumedAuthLayout>
</template>
