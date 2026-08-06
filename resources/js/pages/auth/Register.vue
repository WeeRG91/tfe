<script setup lang="ts">
import { usePasswordStrength } from '@/composables/usePasswordStrength';
import CostumedAuthLayout from '@/layouts/CostumedAuthLayout.vue';
import { login, register } from '@/routes';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, MoveLeft, MoveRight } from 'lucide-vue-next';
import { ref, toRef } from 'vue';
import { toast } from 'vue-sonner';
import { useI18n } from 'vue-i18n';

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const { t } = useI18n();

const registerForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const { passwordChecks, getPasswordStrengthColor } = usePasswordStrength(
    toRef(registerForm, 'password'),
);

const submit = () => {
    registerForm.post(register().url, {
        onSuccess: () => {
            toast.success(t('register.messages.created'));
        },
        onError: (errors) => {
            console.log(errors);
            if (errors.error) {
                toast.error(errors.error);
            } else if (
                errors.name ||
                errors.email ||
                errors.password ||
                errors.password_confirmation
            ) {
                toast.error(t('register.messages.invalidInput'));
            } else {
                toast.error(t('register.messages.error'));
            }
        },
    });
};
</script>

<template>
    <Head :title="t('register.title')" />

    <CostumedAuthLayout
        :title="t('register.heading')"
        :subtitle="t('register.subtitle')"
        :badge="t('register.badge')"
    >
        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label
                    for="name"
                    class="mb-1.5 block text-sm font-medium text-gray-700"
                >
                    {{ t('register.fields.name') }}
                </label>
                <input
                    id="name"
                    v-model="registerForm.name"
                    type="text"
                    :placeholder="t('register.placeholders.name')"
                    class="w-full rounded-xl border border-gray-200 bg-white/50 px-4 py-2.5 text-sm text-gray-900 backdrop-blur-sm transition-all duration-200 placeholder:text-gray-400 focus:border-gray-400 focus:bg-white focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                    :class="{
                        'border-red-300 focus:border-red-400':
                            registerForm.errors.name,
                    }"
                    required
                    autofocus
                />
                <p
                    v-if="registerForm.errors.name"
                    class="mt-1.5 text-sm text-red-500"
                >
                    {{ registerForm.errors.name }}
                </p>
            </div>

            <div>
                <label
                    for="email"
                    class="mb-1.5 block text-sm font-medium text-gray-700"
                >
                    {{ t('register.fields.email') }}
                </label>
                <input
                    id="email"
                    v-model="registerForm.email"
                    type="email"
                    placeholder="email@example.com"
                    class="w-full rounded-xl border border-gray-200 bg-white/50 px-4 py-2.5 text-sm text-gray-900 backdrop-blur-sm transition-all duration-200 placeholder:text-gray-400 focus:border-gray-400 focus:bg-white focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                    :class="{
                        'border-red-300 focus:border-red-400':
                            registerForm.errors.email,
                    }"
                    required
                />
                <p
                    v-if="registerForm.errors.email"
                    class="mt-1.5 text-sm text-red-500"
                >
                    {{ registerForm.errors.email }}
                </p>
            </div>

            <div>
                <label
                    for="password"
                    class="mb-1.5 block text-sm font-medium text-gray-700"
                >
                    {{ t('register.fields.password') }}
                </label>
                <div class="group relative">
                    <input
                        id="password"
                        v-model="registerForm.password"
                        :type="showPassword ? 'text' : 'password'"
                        :placeholder="t('register.placeholders.password')"
                        class="w-full rounded-xl border border-gray-200 bg-white/50 py-2.5 pr-12 pl-4 text-sm text-gray-900 backdrop-blur-sm transition-all duration-200 placeholder:text-gray-400 focus:border-gray-400 focus:bg-white focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                        :class="{
                            'border-red-300 focus:border-red-400':
                                registerForm.errors.password,
                        }"
                        required
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute top-1/2 right-3 -translate-y-1/2 p-1 text-gray-400 transition-colors hover:text-gray-600"
                        aria-label="Toggle password visibility"
                    >
                        <Eye v-if="showPassword" class="h-5 w-5" />
                        <EyeOff v-else class="h-5 w-5" />
                    </button>
                </div>
                <p
                    v-if="registerForm.errors.password"
                    class="mt-1.5 text-sm text-red-500"
                >
                    {{ registerForm.errors.password }}
                </p>

                <div
                    v-if="registerForm.password.length > 0"
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
                        class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-gray-400"
                    >
                        <span
                            :class="{
                                'text-emerald-500': passwordChecks.length,
                            }"
                            class="transition-colors duration-200"
                        >
                            {{ passwordChecks.length ? '✓' : '○' }}
                            {{ t('register.passwordRequirements.length') }}
                        </span>
                        <span
                            :class="{
                                'text-emerald-500': passwordChecks.uppercase,
                            }"
                            class="transition-colors duration-200"
                        >
                            {{ passwordChecks.uppercase ? '✓' : '○' }}
                            {{ t('register.passwordRequirements.uppercase') }}
                        </span>
                        <span
                            :class="{
                                'text-emerald-500': passwordChecks.lowercase,
                            }"
                            class="transition-colors duration-200"
                        >
                            {{ passwordChecks.lowercase ? '✓' : '○' }}
                            {{ t('register.passwordRequirements.lowercase') }}
                        </span>
                        <span
                            :class="{
                                'text-emerald-500': passwordChecks.number,
                            }"
                            class="transition-colors duration-200"
                        >
                            {{ passwordChecks.number ? '✓' : '○' }}
                            {{ t('register.passwordRequirements.number') }}
                        </span>
                        <span
                            :class="{
                                'text-emerald-500': passwordChecks.symbol,
                            }"
                            class="transition-colors duration-200"
                        >
                            {{ passwordChecks.symbol ? '✓' : '○' }}
                            {{ t('register.passwordRequirements.symbol') }}
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <label
                    for="password_confirmation"
                    class="mb-1.5 block text-sm font-medium text-gray-700"
                >
                    {{ t('register.fields.confirmPassword') }}
                </label>
                <div class="group relative">
                    <input
                        id="password_confirmation"
                        v-model="registerForm.password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        :placeholder="
                            t('register.placeholders.confirmPassword')
                        "
                        class="w-full rounded-xl border border-gray-200 bg-white/50 py-2.5 pr-12 pl-4 text-sm text-gray-900 backdrop-blur-sm transition-all duration-200 placeholder:text-gray-400 focus:border-gray-400 focus:bg-white focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                        :class="{
                            'border-red-300 focus:border-red-400':
                                registerForm.errors.password_confirmation,
                        }"
                        required
                    />
                    <button
                        type="button"
                        @click="
                            showPasswordConfirmation = !showPasswordConfirmation
                        "
                        class="absolute top-1/2 right-3 -translate-y-1/2 p-1 text-gray-400 transition-colors hover:text-gray-600"
                        aria-label="Toggle password confirmation visibility"
                    >
                        <Eye v-if="showPasswordConfirmation" class="h-5 w-5" />
                        <EyeOff v-else class="h-5 w-5" />
                    </button>
                </div>
                <p
                    v-if="registerForm.errors.password_confirmation"
                    class="mt-1.5 text-sm text-red-500"
                >
                    {{ registerForm.errors.password_confirmation }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="registerForm.processing"
                class="group w-full rounded-2xl bg-gray-900 px-4 py-3 text-sm font-medium text-white transition-all duration-200 hover:bg-gray-800 hover:shadow-lg hover:shadow-gray-900/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-gray-900 disabled:hover:shadow-none"
            >
                <span class="flex items-center justify-center gap-2">
                    <span
                        v-if="registerForm.processing"
                        class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                    ></span>
                    <span v-else>{{ t('auth.buttons.createAccount') }}</span>
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
