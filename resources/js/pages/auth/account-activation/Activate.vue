<script setup lang="ts">
import CostumedAuthLayout from '@/layouts/CostumedAuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import { login, register } from '@/routes';
import { MoveLeft, MoveRight, Eye, EyeOff } from 'lucide-vue-next';
import { ActivateUserType } from '@/types/user';

defineProps<{
    user: ActivateUserType;
}>();

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
            toast.success('Account activated successfully!');
        },
        onError: (errors) => {
            console.log(errors);
            if (errors.error) {
                toast.error(errors.error);
            } else if (errors.name || errors.email || errors.password) {
                toast.error('Invalid input.');
            } else {
                toast.error('Something went wrong. Please check the form.');
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
        if (passwordStrength.value <= 2) return 'bg-red-400';
        if (passwordStrength.value === 3) return 'bg-amber-400';
        return 'bg-emerald-400';
    }
    return 'bg-gray-100';
};
</script>

<template>
    <Head title="Activate Account" />
    <CostumedAuthLayout
        title="Set your password"
        subtitle="Create a secure password to complete your account setup"
        badge="Welcome"
    >
        <form @submit.prevent="submit" class="space-y-6">
            <div
                class="rounded-2xl border border-gray-100 bg-white/50 p-4 backdrop-blur-sm"
            >
                <div class="space-y-1.5 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-400">Name</span>
                        <span class="font-medium text-gray-700">{{
                            user.name
                        }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-400">Email</span>
                        <span class="font-medium text-gray-700">{{
                            user.email
                        }}</span>
                    </div>
                </div>
            </div>

            <div>
                <label
                    for="password"
                    class="mb-1.5 block text-sm font-medium text-gray-700"
                >
                    Password
                </label>
                <div class="group relative">
                    <input
                        id="password"
                        v-model="activateForm.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Create a strong password"
                        class="w-full rounded-xl border border-gray-200 bg-white/50 py-2.5 pr-12 pl-4 text-sm text-gray-900 backdrop-blur-sm transition-all duration-200 placeholder:text-gray-400 focus:border-gray-400 focus:bg-white focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                        :class="{
                            'border-red-300 focus:border-red-400':
                                activateForm.errors.password,
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
                    v-if="activateForm.errors.password"
                    class="mt-1.5 text-sm text-red-500"
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
                        class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-gray-400"
                    >
                        <span
                            :class="{
                                'text-emerald-500': passwordChecks.length,
                            }"
                            class="transition-colors duration-200"
                        >
                            {{ passwordChecks.length ? '✓' : '○' }} 8+
                            characters
                        </span>
                        <span
                            :class="{
                                'text-emerald-500': passwordChecks.uppercase,
                            }"
                            class="transition-colors duration-200"
                        >
                            {{ passwordChecks.uppercase ? '✓' : '○' }} Uppercase
                        </span>
                        <span
                            :class="{
                                'text-emerald-500': passwordChecks.lowercase,
                            }"
                            class="transition-colors duration-200"
                        >
                            {{ passwordChecks.lowercase ? '✓' : '○' }} Lowercase
                        </span>
                        <span
                            :class="{
                                'text-emerald-500': passwordChecks.number,
                            }"
                            class="transition-colors duration-200"
                        >
                            {{ passwordChecks.number ? '✓' : '○' }} Number
                        </span>
                        <span
                            :class="{
                                'text-emerald-500': passwordChecks.symbol,
                            }"
                            class="transition-colors duration-200"
                        >
                            {{ passwordChecks.symbol ? '✓' : '○' }} Symbol
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <label
                    for="password_confirmation"
                    class="mb-1.5 block text-sm font-medium text-gray-700"
                >
                    Confirm Password
                </label>
                <div class="group relative">
                    <input
                        id="password_confirmation"
                        v-model="activateForm.password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        placeholder="Confirm your password"
                        class="w-full rounded-xl text-sm border border-gray-200 bg-white/50 py-2.5 pr-12 pl-4 text-gray-900 backdrop-blur-sm transition-all duration-200 placeholder:text-gray-400 focus:border-gray-400 focus:bg-white focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                        :class="{
                            'border-red-300 focus:border-red-400':
                                activateForm.errors.password_confirmation,
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
                        <Eye v-if="showPassword" class="h-5 w-5" />
                        <EyeOff v-else class="h-5 w-5" />
                    </button>
                </div>
                <p
                    v-if="activateForm.errors.password_confirmation"
                    class="mt-1.5 text-sm text-red-500"
                >
                    {{ activateForm.errors.password_confirmation }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="activateForm.processing"
                class="group w-full rounded-2xl bg-gray-900 px-4 py-3.5 text-sm font-medium text-white transition-all duration-200 hover:bg-gray-800 hover:shadow-lg hover:shadow-gray-900/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-gray-900 disabled:hover:shadow-none"
            >
                <span class="flex items-center justify-center gap-2">
                    <span
                        v-if="activateForm.processing"
                        class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                    ></span>
                    <span v-else>Activate Account</span>
                </span>
            </button>
        </form>

        <template #footer>
            <div class="space-y-6">
                <div
                    class="rounded-2xl border border-gray-100 bg-white/50 p-4 backdrop-blur-sm"
                >
                    <p class="text-center text-sm text-gray-500">
                        Need help?
                        <Link
                            href="/contact"
                            class="font-medium text-gray-900 transition-colors hover:text-gray-600"
                        >
                            Contact support
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
                        Back to Sign In
                    </Link>

                    <div class="h-4 w-px bg-gray-200"></div>

                    <Link
                        :href="register()"
                        class="group flex items-center gap-1.5 text-sm text-gray-500 transition-all hover:text-gray-900"
                    >
                        Create new account
                        <MoveRight
                            class="h-4 w-4 transition-transform group-hover:translate-x-0.5"
                        />
                    </Link>
                </div>
            </div>
        </template>
    </CostumedAuthLayout>
</template>
