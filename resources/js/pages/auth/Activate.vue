<script setup lang="ts">
import CostumedAuthLayout from '@/layouts/CostumedAuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';

type User = {
    id: number;
    name: string;
    email: string;
};

defineProps<{
    user: User;
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
        if (passwordStrength.value <= 2) return 'bg-red-500';
        if (passwordStrength.value === 3) return 'bg-yellow-500';
        return 'bg-green-500';
    }
    return 'bg-gray-200';
};
</script>

<template>
    <Head title="Sign Up" />
    <CostumedAuthLayout
        title="Activate Account"
        subtitle="Join us and start your culinary journey"
        badge="Welcome"
    >
        <form @submit.prevent="submit" class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <div class="space-y-2 text-sm">
                    <div>
                        <span class="font-semibold text-gray-700">Name</span>
                        <p class="text-gray-600">{{ user.name }}</p>
                    </div>

                    <div>
                        <span class="font-semibold text-gray-700">Email</span>
                        <p class="text-gray-600">{{ user.email }}</p>
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
                <div class="relative">
                    <svg
                        class="pointer-events-none absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-gray-400 transition-colors group-focus-within:text-red-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                        />
                    </svg>
                    <input
                        id="password"
                        v-model="activateForm.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="••••••••"
                        class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 py-2.5 pr-12 pl-10 text-gray-900 transition-all duration-200 placeholder:text-gray-400 focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                        :class="{
                            'border-red-500 focus:ring-red-500/15':
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
                        <svg
                            v-if="showPassword"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                            />
                        </svg>
                        <svg
                            v-else
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                            />
                        </svg>
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
                    class="mt-3 space-y-2"
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
                        class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-gray-500"
                    >
                        <span
                            :class="{ 'text-green-500': passwordChecks.length }"
                        >
                            {{ passwordChecks.length ? '✓' : '○' }} 8+
                            characters
                        </span>
                        <span
                            :class="{
                                'text-green-500': passwordChecks.uppercase,
                            }"
                        >
                            {{ passwordChecks.uppercase ? '✓' : '○' }} Uppercase
                        </span>
                        <span
                            :class="{
                                'text-green-500': passwordChecks.lowercase,
                            }"
                        >
                            {{ passwordChecks.lowercase ? '✓' : '○' }} Lowercase
                        </span>
                        <span
                            :class="{ 'text-green-500': passwordChecks.number }"
                        >
                            {{ passwordChecks.number ? '✓' : '○' }} Number
                        </span>
                        <span
                            :class="{ 'text-green-500': passwordChecks.symbol }"
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
                <div class="relative">
                    <svg
                        class="pointer-events-none absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-gray-400 transition-colors group-focus-within:text-red-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                        />
                    </svg>
                    <input
                        id="password_confirmation"
                        v-model="activateForm.password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        placeholder="••••••••"
                        class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 py-2.5 pr-12 pl-10 text-gray-900 transition-all duration-200 placeholder:text-gray-400 focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                        :class="{
                            'border-red-500 focus:ring-red-500/15':
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
                        <svg
                            v-if="showPasswordConfirmation"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                            />
                        </svg>
                        <svg
                            v-else
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                            />
                        </svg>
                    </button>
                </div>
                <p
                    v-if="activateForm.errors.password_confirmation"
                    class="mt-1.5 text-sm text-red-500"
                >
                    {{ activateForm.errors.password_confirmation }}
                </p>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="activateForm.processing"
                class="w-full rounded-xl bg-red-500 px-4 py-3 text-sm font-semibold tracking-wide text-white uppercase shadow-lg transition-all duration-200 hover:bg-red-600 hover:shadow-red-500/30 active:bg-red-700 active:shadow-none disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-red-500"
            >
                <span
                    v-if="activateForm.processing"
                    class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                ></span>
                <span v-else>Activate</span>
            </button>
        </form>
        <template #footer>
            <p>
                Already have an account?
                <Link
                    href="/login"
                    class="font-medium text-red-500 transition-colors hover:text-red-600"
                >
                    Sign In
                </Link>
            </p>
        </template>
    </CostumedAuthLayout>
</template>
