<script setup lang="ts">
import { usePasswordStrength } from '@/composables/usePasswordStrength';
import { useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, Lock, Save } from 'lucide-vue-next';
import { ref, toRef } from 'vue';
import { toast } from 'vue-sonner';
import settings from '@/actions/App/Http/Controllers/Settings';

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const { passwordChecks, getPasswordStrengthColor } = usePasswordStrength(
    toRef(passwordForm, 'password'),
);

const updatePassword = () => {
    passwordForm.put(settings.PasswordController.update().url, {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            toast.success('Password updated successfully!');
        },
        onError: (errors) => {
            if (errors.current_password) {
                toast.error(errors.current_password);
            }
            if (errors.password) {
                toast.error(errors.password);
            }
        },
    });
};
</script>

<template>
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold md:text-2xl">Change Password</h2>
            <p class="mt-1 text-sm text-gray-500">
                Ensure your account is using a strong password to stay secure
            </p>
        </div>

        <form @submit.prevent="updatePassword" class="space-y-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Current Password
                </label>
                <div class="relative">
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                    >
                        <Lock class="h-5 w-5 text-gray-400" />
                    </div>
                    <input
                        v-model="passwordForm.current_password"
                        :type="showCurrentPassword ? 'text' : 'password'"
                        class="w-full rounded-lg border border-gray-300 bg-white py-2 pr-12 pl-10 text-sm transition-colors focus:border-red-500 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                        :class="{
                            'border-red-500 ring-2 ring-red-500/20':
                                passwordForm.errors.current_password,
                        }"
                        placeholder="Enter your current password"
                    />
                    <button
                        type="button"
                        @click="showCurrentPassword = !showCurrentPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                    >
                        <Eye v-if="!showCurrentPassword" class="h-5 w-5" />
                        <EyeOff v-else class="h-5 w-5" />
                    </button>
                </div>
                <p
                    v-if="passwordForm.errors.current_password"
                    class="mt-1.5 text-xs text-red-500"
                >
                    {{ passwordForm.errors.current_password }}
                </p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    New Password
                </label>
                <div class="relative">
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                    >
                        <Lock class="h-5 w-5 text-gray-400" />
                    </div>
                    <input
                        v-model="passwordForm.password"
                        :type="showNewPassword ? 'text' : 'password'"
                        class="w-full rounded-lg border border-gray-300 bg-white py-2 pr-12 pl-10 text-sm transition-colors focus:border-red-500 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                        :class="{
                            'border-red-500 ring-2 ring-red-500/20':
                                passwordForm.errors.password,
                        }"
                        placeholder="Enter your new password"
                    />
                    <button
                        type="button"
                        @click="showNewPassword = !showNewPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                    >
                        <Eye v-if="!showNewPassword" class="h-5 w-5" />
                        <EyeOff v-else class="h-5 w-5" />
                    </button>
                </div>
                <p
                    v-if="passwordForm.errors.password"
                    class="mt-1.5 text-xs text-red-500"
                >
                    {{ passwordForm.errors.password }}
                </p>

                <div
                    v-if="passwordForm.password.length > 0"
                    class="mt-3 space-y-2.5"
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
                        class="grid grid-cols-2 gap-x-4 gap-y-0.5 text-xs text-gray-400"
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
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    Confirm New Password
                </label>
                <div class="relative">
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                    >
                        <Lock class="h-5 w-5 text-gray-400" />
                    </div>
                    <input
                        v-model="passwordForm.password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        class="w-full rounded-lg border border-gray-300 bg-white py-2 pr-12 pl-10 text-sm transition-colors focus:border-red-500 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                        :class="{
                            'border-red-500 ring-2 ring-red-500/20':
                                passwordForm.errors.password_confirmation,
                        }"
                        placeholder="Confirm your new password"
                    />
                    <button
                        type="button"
                        @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                    >
                        <Eye v-if="!showConfirmPassword" class="h-5 w-5" />
                        <EyeOff v-else class="h-5 w-5" />
                    </button>
                </div>
                <p
                    v-if="passwordForm.errors.password_confirmation"
                    class="mt-1.5 text-xs text-red-500"
                >
                    {{ passwordForm.errors.password_confirmation }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="passwordForm.processing"
                class="flex items-center gap-2 rounded-lg bg-red-500 px-6 py-2 text-sm font-medium text-white transition-colors hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-50"
            >
                <Save v-if="!passwordForm.processing" class="h-4 w-4" />
                <span
                    v-if="passwordForm.processing"
                    class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/20 border-t-white"
                ></span>
                {{
                    passwordForm.processing ? 'Updating...' : 'Update Password'
                }}
            </button>
        </form>
    </div>
</template>

<style scoped></style>
