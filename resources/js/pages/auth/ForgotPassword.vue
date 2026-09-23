<script setup lang="ts">
import CostumedAuthLayout from '@/layouts/CostumedAuthLayout.vue';
import { login, register } from '@/routes';
import { request } from '@/routes/password';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { MoveLeft, MoveRight } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

defineProps<{
    status?: string;
}>();

const forgotForm = useForm({
    email: '',
});

const { t } = useI18n();

const submit = () => {
    forgotForm.post(request().url, {
        onSuccess: () => {
            toast.success(t('forgotPassword.messages.resetLinkSent'));
        },
        onError: (errors) => {
            console.log(errors);
            if (errors.email) {
                toast.error(errors.email);
            } else {
                toast.error(t('forgotPassword.messages.error'));
            }
        },
    });
};
</script>

<template>
    <Head :title="t('forgotPassword.title')" />

    <CostumedAuthLayout
        :title="t('forgotPassword.heading')"
        :subtitle="t('forgotPassword.subtitle')"
        :badge="t('forgotPassword.badge')"
    >
        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-success"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label
                    for="email"
                    class="mb-1.5 block text-sm font-medium text-foreground"
                >
                    {{ t('forgotPassword.fields.email') }}
                </label>
                <input
                    id="email"
                    v-model="forgotForm.email"
                    type="email"
                    placeholder="email@example.com"
                    class="w-full rounded-xl border border-border bg-background/50 px-4 py-2.5 text-sm text-foreground backdrop-blur-sm transition-all duration-200 placeholder:text-muted-foreground focus:border-ring focus:bg-background focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                    :class="{
                        'border-destructive focus:border-destructive':
                            forgotForm.errors.email,
                    }"
                    required
                />
                <p
                    v-if="forgotForm.errors.email"
                    class="mt-1.5 text-sm text-destructive"
                >
                    {{ forgotForm.errors.email }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="forgotForm.processing"
                class="group w-full rounded-2xl bg-primary px-4 py-3 text-sm font-medium text-primary-foreground transition-all duration-200 hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-primary disabled:hover:shadow-none"
            >
                <span class="flex items-center justify-center gap-2">
                    <span
                        v-if="forgotForm.processing"
                        class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-primary-foreground border-t-transparent"
                    ></span>
                    <span v-else>{{ t('auth.buttons.sendResetLink') }}</span>
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
