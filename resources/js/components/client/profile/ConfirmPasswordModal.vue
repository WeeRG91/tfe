<script setup lang="ts">
import axios from 'axios';
import { Eye, EyeOff, Shield, X } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    open: boolean;
    action: 'enable-2fa' | 'disable-2fa' | 'regenerate-codes';
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirmed'): void;
}>();

const { t } = useI18n();

const password = ref<string>('');
const passwordError = ref<string>('');
const showPassword = ref<boolean>(false);
const isConfirming = ref<boolean>(false);

const content = computed(() => {
    switch (props.action) {
        case 'enable-2fa':
            return {
                title: t('profile.confirmPasswordModal.enableTwoFactor.title'),
                description: t(
                    'profile.confirmPasswordModal.enableTwoFactor.description',
                ),
                confirmText: t(
                    'profile.confirmPasswordModal.enableTwoFactor.confirm',
                ),
                icon: Shield,
            };

        case 'disable-2fa':
            return {
                title: t('profile.confirmPasswordModal.disableTwoFactor.title'),
                description: t(
                    'profile.confirmPasswordModal.disableTwoFactor.description',
                ),
                confirmText: t(
                    'profile.confirmPasswordModal.disableTwoFactor.confirm',
                ),
                icon: Shield,
            };

        case 'regenerate-codes':
            return {
                title: t('profile.confirmPasswordModal.regenerateCodes.title'),
                description: t(
                    'profile.confirmPasswordModal.regenerateCodes.description',
                ),
                confirmText: t(
                    'profile.confirmPasswordModal.regenerateCodes.confirm',
                ),
                icon: Shield,
            };

        default:
            return {
                title: t('profile.confirmPasswordModal.default.title'),
                description: t(
                    'profile.confirmPasswordModal.default.description',
                ),
                confirmText: t('profile.confirmPasswordModal.confirm'),
                icon: Shield,
            };
    }
});
const resetForm = () => {
    password.value = '';
    passwordError.value = '';
    showPassword.value = false;
    isConfirming.value = false;
};

const closeModal = () => {
    if (!isConfirming.value) {
        resetForm();
        emit('close');
    }
};

const confirmPassword = async () => {
    if (!password.value) {
        passwordError.value = t('profile.confirmPasswordModal.errors.required');
        return;
    }

    isConfirming.value = true;
    passwordError.value = '';

    try {
        await axios.post('/user/confirm-password', {
            password: password.value,
        });

        resetForm();
        emit('confirmed');
        emit('close');
    } catch (error: any) {
        if (error.response?.status === 422) {
            passwordError.value = t(
                'profile.confirmPasswordModal.errors.invalid',
            );
        } else {
            toast.error(
                t('profile.confirmPasswordModal.errors.verificationFailed'),
            );
        }
    } finally {
        isConfirming.value = false;
    }
};

const handleKeyup = (e: KeyboardEvent) => {
    if (e.key === 'Enter') {
        confirmPassword();
    }
};

onMounted(() => {
    if (props.open) {
        document.body.style.overflow = 'hidden';
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
});

watch(
    () => props.open,
    async (isOpen) => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    },
);
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeModal"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-red-100 p-2.5 text-red-600">
                            <component :is="content.icon" class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                {{ content.title }}
                            </h3>
                            <p class="mt-0.5 text-sm text-gray-500">
                                {{ content.description }}
                            </p>
                        </div>
                    </div>
                    <button
                        @click="closeModal"
                        class="rounded-lg p-1 hover:bg-gray-100"
                        :disabled="isConfirming"
                        type="button"
                    >
                        <X class="h-5 w-5 text-gray-500" />
                    </button>
                </div>

                <div class="mt-6">
                    <label for="confirm-password-modal" class="sr-only">{{
                        t('profile.confirmPasswordModal.password')
                    }}</label>
                    <div class="relative">
                        <input
                            id="confirm-password-modal"
                            v-model="password"
                            type="password"
                            :placeholder="
                                t(
                                    'profile.confirmPasswordModal.passwordPlaceholder',
                                )
                            "
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 pr-10 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-200 focus:outline-none"
                            :class="{
                                'border-red-500 focus:ring-red-200':
                                    passwordError,
                            }"
                            @keyup="handleKeyup"
                            :disabled="isConfirming"
                            autofocus
                        />
                        <button
                            @click="showPassword = !showPassword"
                            class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            type="button"
                            :disabled="isConfirming"
                        >
                            <Eye v-if="!showPassword" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>
                    <p v-if="passwordError" class="mt-1.5 text-sm text-red-500">
                        {{ passwordError }}
                    </p>
                </div>

                <div class="mt-6 flex gap-3">
                    <button
                        @click="closeModal"
                        class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                        :disabled="isConfirming"
                        type="button"
                    >
                        {{ t('profile.confirmPasswordModal.cancel') }}
                    </button>
                    <button
                        @click="confirmPassword"
                        :disabled="isConfirming || !password"
                        class="flex-1 rounded-lg bg-red-500 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-50"
                        type="button"
                    >
                        <span
                            v-if="isConfirming"
                            class="flex items-center justify-center gap-2"
                        >
                            <svg
                                class="h-4 w-4 animate-spin"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            {{ t('profile.confirmPasswordModal.confirming') }}
                        </span>
                        <span v-else>{{ content.confirmText }}</span>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fixed {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}
</style>
