<script setup lang="ts">
import CostumedAuthLayout from '@/layouts/CostumedAuthLayout.vue';
import { login } from '@/routes';
import verification from '@/routes/verification';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { MoveLeft, MoveRight } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

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
            class="mb-4 text-center text-sm font-medium text-success"
        >
            {{ t('verifyEmail.messages.verificationLinkSent') }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <button
                type="submit"
                :disabled="verifyForm.processing"
                class="group w-full rounded-2xl bg-primary px-4 py-3 text-sm font-medium text-primary-foreground transition-all duration-200 hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-primary disabled:hover:shadow-none"
            >
                <span class="flex items-center justify-center gap-2">
                    <span
                        v-if="verifyForm.processing"
                        class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-primary-foreground border-t-transparent"
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
                    class="rounded-2xl border border-border bg-muted/50 p-4 backdrop-blur-sm"
                >
                    <p class="text-center text-sm text-muted-foreground">
                        {{ t('auth.links.needHelp') }}
                        <Link
                            href="/contact"
                            class="font-medium text-foreground transition-colors hover:text-foreground"
                        >
                            {{ t('auth.links.contactSupport') }}
                        </Link>
                    </p>
                </div>

                <div class="flex items-center justify-center gap-8">
                    <Link
                        :href="login()"
                        class="group flex items-center gap-1.5 text-sm text-muted-foreground transition-all hover:text-foreground"
                    >
                        <MoveLeft
                            class="h-4 w-4 transition-transform group-hover:-translate-x-0.5"
                        />
                        {{ t('auth.links.backToSignIn') }}
                    </Link>

                    <div class="h-4 w-px bg-border"></div>

                    <Link
                        href="/"
                        class="group flex items-center gap-1.5 text-sm text-muted-foreground transition-all hover:text-foreground"
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
