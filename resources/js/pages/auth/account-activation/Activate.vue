<script setup lang="ts">
import CostumedAuthLayout from '@/layouts/CostumedAuthLayout.vue';
import { login, register } from '@/routes';
import { ActivateUserType } from '@/types/user';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, MoveLeft, MoveRight } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

defineProps<{
    user: ActivateUserType;
}>();

const { t } = useI18n();

const activateForm = useForm({
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    const activationUrl = `${window.location.pathname}${window.location.search}`;

    activateForm.post(activationUrl, {
        onSuccess: () => {
            toast.success(t('activateAccount.messages.success'));
        },
        onError: (errors) => {
            console.log(errors);
            if (errors.error) {
                toast.error(errors.error);
            } else if (errors.name || errors.email || errors.password) {
                toast.error(t('activateAccount.messages.invalidInput'));
            } else {
                toast.error(t('activateAccount.messages.error'));
            }
        },
    });
};

const passwordChecks = computed(() => ({
    length: activateForm.password.length >= 8,
    uppercase: /[A-Z]/.test(activateForm.password),
    lowercase: /[a-z]/.test(activateForm.password),
    number: /[0-9]/.test(activateForm.password),
    symbol: /[!@#$%^&*(),.?":{}|<>]/.test(activateForm.password),
}));

const passwordStrength = computed(() => {
    return Object.values(passwordChecks.value).filter(Boolean).length;
});

const getStrengthColor = (index: number) => {
    if (index < passwordStrength.value) {
        if (passwordStrength.value <= 2) return 'bg-destructive';
        if (passwordStrength.value === 3) return 'bg-warning';
        return 'bg-success';
    }
    return 'bg-muted';
};
</script>

<template>
    <Head :title="t('activateAccount.title')" />
    <CostumedAuthLayout
        :title="t('activateAccount.heading.title')"
        :subtitle="t('activateAccount.heading.subtitle')"
        :badge="t('activateAccount.heading.badge')"
    >
        <form @submit.prevent="submit" class="space-y-6">
            <div
                class="rounded-2xl border border-border bg-muted/50 p-4 backdrop-blur-sm"
            >
                <div class="space-y-1.5 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-muted-foreground">
                            {{ t('activateAccount.fields.name') }}
                        </span>
                        <span class="font-medium text-foreground">
                            {{ user.name }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-muted-foreground">
                            {{ t('activateAccount.fields.email') }}
                        </span>
                        <span class="font-medium text-foreground">
                            {{ user.email }}
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <label
                    for="password"
                    class="mb-1.5 block text-sm font-medium text-foreground"
                >
                    {{ t('activateAccount.fields.password') }}
                </label>
                <div class="group relative">
                    <input
                        id="password"
                        v-model="activateForm.password"
                        :type="showPassword ? 'text' : 'password'"
                        :placeholder="
                            t('activateAccount.fields.passwordPlaceholder')
                        "
                        class="w-full rounded-xl border border-border bg-background/50 py-2.5 pr-12 pl-4 text-sm text-foreground backdrop-blur-sm transition-all duration-200 placeholder:text-muted-foreground focus:border-ring focus:bg-background focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                        :class="{
                            'border-destructive focus:border-destructive':
                                activateForm.errors.password,
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
                    v-if="activateForm.errors.password"
                    class="mt-1.5 text-sm text-destructive"
                >
                    {{ activateForm.errors.password }}
                </p>

                <div
                    v-if="activateForm.password.length > 0"
                    class="mt-4 space-y-3"
                >
                    <div class="flex gap-1">
                        <div
                            v-for="i in 5"
                            :key="i"
                            class="h-1 flex-1 rounded-full transition-all duration-300"
                            :class="getStrengthColor(i)"
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
                            {{
                                t(
                                    'activateAccount.passwordRequirements.characters',
                                )
                            }}
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
                                    'activateAccount.passwordRequirements.uppercase',
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
                                    'activateAccount.passwordRequirements.lowercase',
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
                            {{
                                t('activateAccount.passwordRequirements.number')
                            }}
                        </span>
                        <span
                            :class="{
                                'text-success': passwordChecks.symbol,
                            }"
                            class="transition-colors duration-200"
                        >
                            {{ passwordChecks.symbol ? '✓' : '○' }}
                            {{
                                t('activateAccount.passwordRequirements.symbol')
                            }}
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <label
                    for="password_confirmation"
                    class="mb-1.5 block text-sm font-medium text-foreground"
                >
                    {{ t('activateAccount.fields.confirmPassword') }}
                </label>
                <div class="group relative">
                    <input
                        id="password_confirmation"
                        v-model="activateForm.password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        :placeholder="
                            t(
                                'activateAccount.fields.confirmPasswordPlaceholder',
                            )
                        "
                        class="w-full rounded-xl border border-border bg-background/50 py-2.5 pr-12 pl-4 text-sm text-foreground backdrop-blur-sm transition-all duration-200 placeholder:text-muted-foreground focus:border-ring focus:bg-background focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                        :class="{
                            'border-destructive focus:border-destructive':
                                activateForm.errors.password_confirmation,
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
                        <Eye v-if="showPassword" class="h-5 w-5" />
                        <EyeOff v-else class="h-5 w-5" />
                    </button>
                </div>
                <p
                    v-if="activateForm.errors.password_confirmation"
                    class="mt-1.5 text-sm text-destructive"
                >
                    {{ activateForm.errors.password_confirmation }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="activateForm.processing"
                class="group w-full rounded-2xl bg-primary px-4 py-3 text-sm font-medium text-primary-foreground transition-all duration-200 hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-primary disabled:hover:shadow-none"
            >
                <span class="flex items-center justify-center gap-2">
                    <span
                        v-if="activateForm.processing"
                        class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-primary-foreground border-t-transparent"
                    ></span>
                    <span v-else>
                        {{ t('activateAccount.activate') }}
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
