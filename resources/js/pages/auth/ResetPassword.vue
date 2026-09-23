<script setup lang="ts">
import { usePasswordStrength } from '@/composables/usePasswordStrength';
import CostumedAuthLayout from '@/layouts/CostumedAuthLayout.vue';
import { login } from '@/routes';
import password from '@/routes/password';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, MoveLeft, MoveRight } from 'lucide-vue-next';
import { ref, toRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    token: string;
    email: string;
}>();

const showPassword = ref<boolean>(false);
const showPasswordConfirmation = ref<boolean>(false);

const { t } = useI18n();

const resetForm = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const { passwordChecks, getPasswordStrengthColor } = usePasswordStrength(
    toRef(resetForm, 'password'),
);

const submit = () => {
    resetForm.post(password.stores().url, {
        onSuccess: () => {
            toast.success(t('resetPassword.messages.resetSuccess'));
        },
        onError: (errors) => {
            console.log(errors);
            if (errors.error) {
                toast.error(errors.error);
            } else if (
                errors.email ||
                errors.password ||
                errors.password_confirmation
            ) {
                toast.error(t('resetPassword.messages.invalidInput'));
            } else {
                toast.error('resetPassword.messages.error');
            }
        },
    });
};
</script>

<template>
    <Head :title="t('resetPassword.title')" />

    <CostumedAuthLayout
        :title="t('resetPassword.heading')"
        :subtitle="t('resetPassword.subtitle')"
        :badge="t('resetPassword.badge')"
    >
        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label
                    for="email"
                    class="mb-1.5 block text-sm font-medium text-foreground"
                >
                    {{ t('resetPassword.fields.email') }}
                </label>
                <input
                    id="email"
                    v-model="resetForm.email"
                    type="email"
                    placeholder="email@example.com"
                    class="w-full rounded-xl border border-border bg-background/50 px-4 py-2.5 text-sm text-foreground backdrop-blur-sm transition-all duration-200 placeholder:text-muted-foreground focus:border-ring focus:bg-background focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                    :class="{
                        'border-destructive focus:border-destructive':
                            resetForm.errors.email,
                    }"
                    required
                />
                <p
                    v-if="resetForm.errors.email"
                    class="mt-1.5 text-sm text-destructive"
                >
                    {{ resetForm.errors.email }}
                </p>
            </div>

            <div>
                <label
                    for="password"
                    class="mb-1.5 block text-sm font-medium text-foreground"
                >
                    {{ t('resetPassword.fields.password') }}
                </label>
                <div class="group relative">
                    <input
                        id="password"
                        v-model="resetForm.password"
                        :type="showPassword ? 'text' : 'password'"
                        :placeholder="t('resetPassword.placeholders.password')"
                        class="w-full rounded-xl border border-border bg-background/50 py-2.5 pr-12 pl-4 text-sm text-foreground backdrop-blur-sm transition-all duration-200 placeholder:text-muted-foreground focus:border-ring focus:bg-background focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                        :class="{
                            'border-destructive focus:border-destructive':
                                resetForm.errors.password,
                        }"
                        required
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute top-1/2 right-3 -translate-y-1/2 p-1 text-muted-foreground transition-colors hover:text-foreground"
                        aria-label="Toggle password visibility"
                    >
                        <Eye v-if="showPassword" class="h-5 w-5" />
                        <EyeOff v-else class="h-5 w-5" />
                    </button>
                </div>
                <p
                    v-if="resetForm.errors.password"
                    class="mt-1.5 text-sm text-destructive"
                >
                    {{ resetForm.errors.password }}
                </p>

                <div
                    v-if="resetForm.password.length > 0"
                    class="mt-4 space-y-3"
                >
                    <div class="flex gap-1">
                        <div
                            v-for="i in 4"
                            :key="i"
                            class="h-1 flex-1 rounded-full transition-all duration-300"
                            :class="getPasswordStrengthColor(i)"
                        />
                    </div>
                    <div
                        class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-muted-foreground"
                    >
                        <span
                            :class="{
                                'text-success': passwordChecks.length,
                            }"
                            class="transition-colors duration-200"
                        >
                            {{ passwordChecks.length ? '✓' : '○' }}
                            {{ t('resetPassword.passwordRequirements.length') }}
                        </span>
                        <span
                            :class="{
                                'text-success': passwordChecks.uppercase,
                            }"
                            class="transition-colors duration-200"
                        >
                            {{ passwordChecks.uppercase ? '✓' : '○' }}
                            {{
                                t(
                                    'resetPassword.passwordRequirements.uppercase',
                                )
                            }}
                        </span>
                        <span
                            :class="{
                                'text-success': passwordChecks.lowercase,
                            }"
                            class="transition-colors duration-200"
                        >
                            {{ passwordChecks.lowercase ? '✓' : '○' }}
                            {{
                                t(
                                    'resetPassword.passwordRequirements.lowercase',
                                )
                            }}
                        </span>
                        <span
                            :class="{
                                'text-success': passwordChecks.number,
                            }"
                            class="transition-colors duration-200"
                        >
                            {{ passwordChecks.number ? '✓' : '○' }}
                            {{ t('resetPassword.passwordRequirements.number') }}
                        </span>
                        <span
                            :class="{
                                'text-success': passwordChecks.symbol,
                            }"
                            class="transition-colors duration-200"
                        >
                            {{ passwordChecks.symbol ? '✓' : '○' }}
                            {{ t('resetPassword.passwordRequirements.symbol') }}
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <label
                    for="password_confirmation"
                    class="mb-1.5 block text-sm font-medium text-foreground"
                >
                    {{ t('resetPassword.fields.confirmPassword') }}
                </label>
                <div class="group relative">
                    <input
                        id="password_confirmation"
                        v-model="resetForm.password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        :placeholder="
                            t('resetPassword.placeholders.confirmPassword')
                        "
                        class="w-full rounded-xl border border-border bg-background/50 py-2.5 pr-12 pl-4 text-sm text-foreground backdrop-blur-sm transition-all duration-200 placeholder:text-muted-foreground focus:border-ring focus:bg-background focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                        :class="{
                            'border-destructive focus:border-destructive':
                                resetForm.errors.password_confirmation,
                        }"
                        required
                    />
                    <button
                        type="button"
                        @click="
                            showPasswordConfirmation = !showPasswordConfirmation
                        "
                        class="absolute top-1/2 right-3 -translate-y-1/2 p-1 text-muted-foreground transition-colors hover:text-foreground"
                        aria-label="Toggle password confirmation visibility"
                    >
                        <Eye v-if="showPasswordConfirmation" class="h-5 w-5" />
                        <EyeOff v-else class="h-5 w-5" />
                    </button>
                </div>
                <p
                    v-if="resetForm.errors.password_confirmation"
                    class="mt-1.5 text-sm text-destructive"
                >
                    {{ resetForm.errors.password_confirmation }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="resetForm.processing"
                class="group w-full rounded-2xl bg-primary px-4 py-3 text-sm font-medium text-primary-foreground transition-all duration-200 hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-primary disabled:hover:shadow-none"
            >
                <span class="flex items-center justify-center gap-2">
                    <span
                        v-if="resetForm.processing"
                        class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-primary-foreground border-t-transparent"
                    ></span>
                    <span v-else>{{ t('auth.buttons.resetPassword') }}</span>
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
