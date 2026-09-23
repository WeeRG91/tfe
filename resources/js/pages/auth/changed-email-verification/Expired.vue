<script setup lang="ts">
import CostumedAuthLayout from '@/layouts/CostumedAuthLayout.vue';
import { login, register } from '@/routes';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { MoveLeft, MoveRight, RefreshCw } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const { t } = useI18n();

const resendForm = useForm({
    email: '',
});

const submit = () => {
    resendForm.post('/verify-changed-email/resend', {
        onSuccess: (response) => {
            console.log(response);

            toast.success(t('auth.linkExpired.messages.verificationSuccess'));
        },
        onError: (errors) => {
            console.log(errors);
            if (errors.email) {
                toast.error(errors.email);
            } else if (errors.error) {
                toast.error(errors.error);
            } else {
                toast.error(t('auth.linkExpired.messages.error'));
            }
        },
    });
};
</script>

<template>
    <Head :title="t('auth.linkExpired.verificationTitle')" />
    <CostumedAuthLayout
        :title="t('auth.linkExpired.verificationTitle')"
        :badge="t('auth.linkExpired.heading.badge')"
    >
        <div class="space-y-6">
            <div class="text-center">
                <p class="text-sm text-muted-foreground">
                    {{ t('auth.linkExpired.heading.verificationDescription') }}
                </p>
                <ul class="mt-2 space-y-1 text-sm text-muted-foreground">
                    <li>• {{ t('auth.linkExpired.reasons.expired') }}</li>
                    <li>
                        • {{ t('auth.linkExpired.reasons.alreadyVerified') }}
                    </li>
                    <li>• {{ t('auth.linkExpired.reasons.modified') }}</li>
                </ul>
            </div>

            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-border"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="bg-card px-4 text-muted-foreground">
                        {{ t('auth.linkExpired.fields.requestNewLink') }}
                    </span>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label
                        for="email"
                        class="mb-1.5 block text-sm font-medium text-foreground"
                    >
                        {{ t('auth.linkExpired.fields.emailAddress') }}
                    </label>
                    <div class="group">
                        <input
                            id="email"
                            v-model="resendForm.email"
                            type="email"
                            :placeholder="
                                t('auth.linkExpired.fields.emailPlaceholder')
                            "
                            class="w-full rounded-xl border border-border bg-background/50 px-4 py-2.5 text-sm text-foreground backdrop-blur-sm transition-all duration-200 placeholder:text-muted-foreground focus:border-ring focus:bg-background focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                            :class="{
                                'border-destructive focus:border-destructive':
                                    resendForm.errors.email,
                            }"
                            required
                            autofocus
                        />
                    </div>
                    <p
                        v-if="resendForm.errors.email"
                        class="mt-1.5 text-sm text-destructive"
                    >
                        {{ resendForm.errors.email }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="resendForm.processing"
                    class="group w-full rounded-2xl bg-primary px-4 py-3 text-sm font-medium text-primary-foreground transition-all duration-200 hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-primary disabled:hover:shadow-none"
                >
                    <span class="flex items-center justify-center gap-2">
                        <RefreshCw
                            class="h-5 w-5 transition-transform group-hover:rotate-12"
                        />
                        <span
                            v-if="resendForm.processing"
                            class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-primary-foreground border-t-transparent"
                        ></span>
                        <span v-else>
                            {{
                                t(
                                    'auth.linkExpired.fields.resendVerificationLink',
                                )
                            }}
                        </span>
                    </span>
                </button>
            </form>
        </div>

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
                        :href="register()"
                        class="group flex items-center gap-1.5 text-sm text-muted-foreground transition-all hover:text-foreground"
                    >
                        {{ t('auth.links.createAccount') }}
                        <MoveRight
                            class="h-4 w-4 transition-transform group-hover:translate-x-0.5"
                        />
                    </Link>
                </div>
            </div>
        </template>
    </CostumedAuthLayout>
</template>
