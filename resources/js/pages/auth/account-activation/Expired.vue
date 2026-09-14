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
    resendForm.post('/activate/resend', {
        onSuccess: (response) => {
            console.log(response);

            toast.success(t('auth.linkExpired.messages.activateSuccess'));

            resendForm.reset();
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
    <Head :title="t('auth.linkExpired.activateTitle')" />
    <CostumedAuthLayout
        :title="t('auth.linkExpired.activateTitle')"
        :badge="t('auth.linkExpired.heading.badge')"
    >
        <div class="space-y-6">
            <div class="text-center">
                <p class="text-sm text-gray-500">
                    {{ t('auth.linkExpired.heading.activateDescription') }}
                </p>
                <ul class="mt-2 space-y-1 text-sm text-gray-500">
                    <li>• {{ t('auth.linkExpired.reasons.expired') }}</li>
                    <li>
                        •
                        {{ t('auth.linkExpired.reasons.alreadyActivated') }}
                    </li>
                    <li>• {{ t('auth.linkExpired.reasons.modified') }}</li>
                </ul>
            </div>

            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="bg-white px-4 text-gray-500">
                        {{ t('auth.linkExpired.fields.requestNewLink') }}
                    </span>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label
                        for="email"
                        class="mb-1.5 block text-sm font-medium text-gray-700"
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
                            class="w-full rounded-xl border border-gray-200 bg-white/50 px-4 py-2.5 text-sm text-gray-900 backdrop-blur-sm transition-all duration-200 placeholder:text-gray-400 focus:border-gray-400 focus:bg-white focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                            :class="{
                                'border-red-300 focus:border-red-400':
                                    resendForm.errors.email,
                            }"
                            required
                            autofocus
                        />
                    </div>
                    <p
                        v-if="resendForm.errors.email"
                        class="mt-1.5 text-sm text-red-500"
                    >
                        {{ resendForm.errors.email }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="resendForm.processing"
                    class="group w-full rounded-2xl bg-gray-900 px-4 py-3 text-sm font-medium text-white transition-all duration-200 hover:bg-gray-800 hover:shadow-lg hover:shadow-gray-900/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-gray-900 disabled:hover:shadow-none"
                >
                    <span class="flex items-center justify-center gap-2">
                        <RefreshCw
                            class="h-5 w-5 transition-transform group-hover:rotate-12"
                        />
                        <span
                            v-if="resendForm.processing"
                            class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                        ></span>
                        <span v-else>
                            {{
                                t(
                                    'auth.linkExpired.fields.resendActivationLink',
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
                        :href="register()"
                        class="group flex items-center gap-1.5 text-sm text-gray-500 transition-all hover:text-gray-900"
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
