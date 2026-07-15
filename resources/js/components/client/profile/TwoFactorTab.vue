<script setup lang="ts">
import ConfirmPasswordModal from '@/components/client/profile/ConfirmPasswordModal.vue';
import type { User as UserType } from '@/types/index';
import axios from 'axios';
import {
    AlertCircle,
    Check,
    Copy,
    Download,
    RefreshCw,
    Shield,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    user: UserType;
    twoFactorAuthEnabled: boolean;
}>();

const isLoading = ref<boolean>(false);
const isVerifying = ref<boolean>(false);
const isTwoFactorEnabled = ref<boolean>(props.twoFactorAuthEnabled || false);
const qrCode = ref<string>('');
const secretKey = ref<string>('');
const recoveryCodes = ref<string[]>([]);
const verificationCode = ref<string>('');
const showSetup = ref<boolean>(false);
const showRecoveryCodes = ref<boolean>(false);
const copiedCode = ref<boolean>(false);

const showPasswordModal = ref<boolean>(false);
const passwordModalAction = ref<
    'enable-2fa' | 'disable-2fa' | 'regenerate-codes'
>('enable-2fa');

const startSetup = () => {
    passwordModalAction.value = 'enable-2fa';
    showPasswordModal.value = true;
};

const handlePasswordConfirmed = async () => {
    switch (passwordModalAction.value) {
        case 'enable-2fa':
            await initializeTwoFactorSetup();
            break;
        case 'disable-2fa':
            await disableTwoFactor();
            break;
        case 'regenerate-codes':
            await regenerateRecoveryCodes();
            break;
    }
};

const initializeTwoFactorSetup = async () => {
    isLoading.value = true;

    try {
        await axios.post('/user/two-factor-authentication');

        const [qr, secret, recovery] = await Promise.all([
            axios.get('/user/two-factor-qr-code'),
            axios.get('/user/two-factor-secret-key'),
            axios.get('/user/two-factor-recovery-codes'),
        ]);

        qrCode.value = qr.data.svg;
        secretKey.value = secret.data.secretKey;
        recoveryCodes.value = recovery.data;

        showSetup.value = true;
        toast.success('Scan the QR code with your authenticator app.');
    } catch (e) {
        console.error(e);
        toast.error('Unable to setup two-factor authentication.');
    } finally {
        isLoading.value = false;
    }
};

const verifyAndEnable = async () => {
    if (verificationCode.value.length !== 6) {
        toast.error('Please enter all 6 digits.');
        return;
    }

    isVerifying.value = true;

    try {
        await axios.post('/user/confirmed-two-factor-authentication', {
            code: verificationCode.value,
        });

        isTwoFactorEnabled.value = true;
        showSetup.value = false;
        showRecoveryCodes.value = true;

        toast.success('Two-factor authentication enabled successfully!');
    } catch (e) {
        console.error(e);
        toast.error('Invalid authentication code. Please try again.');
    } finally {
        isVerifying.value = false;
    }
};

const confirmDisableTwoFactor = () => {
    passwordModalAction.value = 'disable-2fa';
    showPasswordModal.value = true;
};

const disableTwoFactor = async () => {
    isLoading.value = true;

    try {
        await axios.delete('/user/two-factor-authentication');

        isTwoFactorEnabled.value = false;
        qrCode.value = '';
        secretKey.value = '';
        recoveryCodes.value = [];
        showSetup.value = false;
        showRecoveryCodes.value = false;

        toast.success('Two-factor authentication disabled.');
    } catch (error) {
        console.error(error);
        toast.error('Unable to disable two-factor authentication.');
    } finally {
        isLoading.value = false;
    }
};

const confirmRegenerateRecoveryCodes = () => {
    passwordModalAction.value = 'regenerate-codes';
    showPasswordModal.value = true;
};

const regenerateRecoveryCodes = async () => {
    isLoading.value = true;

    try {
        await axios.post('/user/two-factor-recovery-codes');

        const response = await axios.get('/user/two-factor-recovery-codes');

        recoveryCodes.value = response.data;

        toast.success('Recovery codes regenerated successfully!');
    } catch (error) {
        console.error(error);
        toast.error('Unable to regenerate recovery codes.');
    } finally {
        isLoading.value = false;
    }
};

const toggleRecoveryCodes = async () => {
    if (!showRecoveryCodes.value && recoveryCodes.value.length === 0) {
        try {
            const { data } = await axios.get('/user/two-factor-recovery-codes');
            recoveryCodes.value = data;
        } catch (error) {
            console.error(error);
            toast.error('Unable to load recovery codes.');
            return;
        }
    }

    showRecoveryCodes.value = !showRecoveryCodes.value;
};

const copyRecoveryCodes = async () => {
    try {
        await navigator.clipboard.writeText(recoveryCodes.value.join('\n'));
        copiedCode.value = true;
        setTimeout(() => {
            copiedCode.value = false;
        }, 3000);
    } catch (error) {
        console.log(error);
        toast.error('Failed to copy codes');
    }
};

const downloadRecoveryCodes = () => {
    const content = recoveryCodes.value.join('\n');
    const blob = new Blob([content], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `recovery-codes-${props.user.email}.txt`;
    a.click();
    window.URL.revokeObjectURL(url);
};

const copySecretKey = async () => {
    await navigator.clipboard.writeText(secretKey.value);
    toast.success('Secret key copied.');
};
</script>

<template>
    <div class="space-y-4 sm:space-y-6">
        <ConfirmPasswordModal
            :open="showPasswordModal"
            :action="passwordModalAction"
            @close="showPasswordModal = false"
            @confirmed="handlePasswordConfirmed"
        />

        <div>
            <h2 class="text-lg font-semibold sm:text-xl md:text-2xl">
                Two-Factor Authentication
            </h2>
            <p class="mt-1 text-xs text-gray-500 sm:text-sm">
                Add an extra layer of security to your account
            </p>
        </div>

        <div class="rounded-lg border p-3 sm:p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="rounded-full p-1.5 sm:p-2"
                        :class="
                            isTwoFactorEnabled ? 'bg-green-100' : 'bg-gray-100'
                        "
                    >
                        <Shield
                            class="h-4 w-4 sm:h-5 sm:w-5"
                            :class="
                                isTwoFactorEnabled
                                    ? 'text-green-600'
                                    : 'text-gray-400'
                            "
                        />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 sm:text-base">
                            {{ isTwoFactorEnabled ? 'Enabled' : 'Disabled' }}
                        </p>
                        <p class="text-xs text-gray-500 sm:text-sm">
                            {{
                                isTwoFactorEnabled
                                    ? 'Your account is protected with 2FA'
                                    : 'Your account is not protected with 2FA'
                            }}
                        </p>
                    </div>
                </div>
                <button
                    v-if="!isTwoFactorEnabled"
                    @click="startSetup"
                    :disabled="isLoading"
                    class="w-full rounded-lg bg-red-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                >
                    {{ isLoading ? 'Loading...' : 'Enable 2FA' }}
                </button>
                <button
                    v-else
                    @click="confirmDisableTwoFactor"
                    :disabled="isLoading"
                    class="w-full rounded-lg border border-red-500 px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                >
                    {{ isLoading ? 'Loading...' : 'Disable 2FA' }}
                </button>
            </div>
        </div>

        <div v-if="showSetup && !isTwoFactorEnabled" class="space-y-4 sm:space-y-6">
            <div class="rounded-lg border p-4 sm:p-6">
                <h3 class="text-base font-semibold text-gray-900 sm:text-lg">
                    Step 1: Scan QR Code
                </h3>
                <p class="mt-1 text-xs text-gray-500 sm:text-sm">
                    Scan the QR code with your authenticator app (Google
                    Authenticator, Authy, etc.)
                </p>

                <div class="mt-4 flex flex-col items-center gap-4 sm:flex-row sm:items-start sm:gap-6">
                    <div class="flex-shrink-0">
                        <div class="rounded-lg bg-white p-3 shadow-sm sm:p-4">
                            <div
                                v-if="qrCode"
                                v-html="qrCode"
                                class="h-32 w-32 sm:h-48 sm:w-48"
                            />
                            <div
                                v-else
                                class="flex h-32 w-32 items-center justify-center bg-gray-100 sm:h-48 sm:w-48"
                            >
                                <RefreshCw
                                    class="h-6 w-6 animate-spin text-gray-400 sm:h-8 sm:w-8"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="w-full flex-1">
                        <p class="text-xs text-gray-500 sm:text-sm">
                            If you can't scan the QR code, enter this secret key
                            manually into your authenticator app.
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <code
                                class="flex-1 truncate rounded bg-gray-100 px-2 py-1 font-mono text-xs sm:px-3 sm:text-sm"
                            >
                                {{ secretKey || 'Loading...' }}
                            </code>
                            <button
                                v-if="secretKey"
                                @click="copySecretKey"
                                class="rounded p-1 hover:bg-gray-100"
                                type="button"
                            >
                                <Copy class="h-3 w-3 text-gray-500 sm:h-4 sm:w-4" />
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 sm:text-sm">
                            App name:
                            <span class="font-medium">Thai Restaurant</span>
                        </p>
                        <p class="text-xs text-gray-500 sm:text-sm">
                            Account:
                            <span class="font-medium">{{ user.email }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border p-4 sm:p-6">
                <h3 class="text-base font-semibold text-gray-900 sm:text-lg">
                    Step 2: Verify Code
                </h3>
                <p class="mt-1 text-xs text-gray-500 sm:text-sm">
                    Enter the 6-digit code from your authenticator app
                </p>

                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:gap-4">
                    <div class="flex-1">
                        <label for="verification-code" class="sr-only"
                        >Verification Code</label
                        >
                        <input
                            id="verification-code"
                            v-model="verificationCode"
                            type="text"
                            maxlength="6"
                            placeholder="Enter 6-digit code"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-center text-xl tracking-widest focus:border-red-500 focus:ring-2 focus:ring-red-200 focus:outline-none sm:px-4 sm:text-2xl"
                            @keyup.enter="verifyAndEnable"
                        />
                    </div>
                    <button
                        @click="verifyAndEnable"
                        :disabled="isVerifying || verificationCode.length !== 6"
                        class="w-full rounded-lg bg-red-500 px-4 py-2 text-sm text-white transition-colors hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto sm:px-6"
                    >
                        {{ isVerifying ? 'Verifying...' : 'Verify & Enable' }}
                    </button>
                </div>

                <div
                    v-if="
                        verificationCode &&
                        verificationCode.length > 0 &&
                        verificationCode.length < 6
                    "
                    class="mt-2"
                >
                    <p class="text-xs text-red-500 sm:text-sm">
                        Please enter all 6 digits
                    </p>
                </div>
            </div>
        </div>

        <div
            v-if="showRecoveryCodes"
            class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 sm:p-6"
        >
            <div class="flex items-start gap-2 sm:gap-3">
                <AlertCircle
                    class="mt-0.5 h-4 w-4 flex-shrink-0 text-yellow-600 sm:h-5 sm:w-5"
                />
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-yellow-900 sm:text-lg">
                        Save Your Recovery Codes
                    </h3>
                    <p class="text-xs text-yellow-700 sm:text-sm">
                        These codes can be used to access your account if you
                        lose your authenticator device. Store them in a safe
                        place.
                    </p>

                    <div class="mt-3 grid grid-cols-1 gap-2 font-mono text-xs sm:mt-4 sm:grid-cols-2 sm:gap-2 sm:text-sm">
                        <div
                            v-for="(code, index) in recoveryCodes"
                            :key="index"
                            class="rounded bg-white p-2 text-center break-all"
                        >
                            {{ code }}
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2 sm:mt-4 sm:gap-3">
                        <button
                            @click="copyRecoveryCodes"
                            :disabled="recoveryCodes.length === 0"
                            class="inline-flex sm:w-36 flex-1 items-center justify-center gap-1.5 rounded-lg bg-white px-3 py-2 text-xs font-medium text-gray-700 shadow-sm transition-all duration-200 hover:bg-gray-50 hover:shadow-md disabled:cursor-not-allowed disabled:text-gray-400 disabled:opacity-40 disabled:hover:bg-white disabled:hover:shadow-sm sm:flex-none sm:gap-2 sm:px-4 sm:text-sm"
                            type="button"
                        >
                            <Copy
                                v-if="!copiedCode"
                                class="h-3 w-3 sm:h-4 sm:w-4"
                                :class="{
                                    'text-gray-400': recoveryCodes.length === 0,
                                }"
                            />
                            <Check v-else class="h-3 w-3 text-green-500 sm:h-4 sm:w-4" />
                            <span class="hidden xs:inline">{{ copiedCode ? 'Copied!' : 'Copy' }}</span>
                        </button>
                        <button
                            @click="downloadRecoveryCodes"
                            :disabled="recoveryCodes.length === 0"
                            class="inline-flex sm:w-36 flex-1 items-center justify-center gap-1.5 rounded-lg bg-white px-3 py-2 text-xs font-medium text-gray-700 shadow-sm transition-all duration-200 hover:bg-gray-50 hover:shadow-md disabled:cursor-not-allowed disabled:text-gray-400 disabled:opacity-40 disabled:hover:bg-white disabled:hover:shadow-sm sm:flex-none sm:gap-2 sm:px-4 sm:text-sm"
                            type="button"
                        >
                            <Download class="h-3 w-3 sm:h-4 sm:w-4" />
                            <span class="hidden xs:inline">Download</span>
                        </button>
                        <button
                            @click="confirmRegenerateRecoveryCodes"
                            :disabled="isLoading"
                            class="inline-flex sm:w-36 flex-1 items-center justify-center gap-1.5 rounded-lg bg-white px-3 py-2 text-xs font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 sm:flex-none sm:gap-2 sm:px-4 sm:text-sm"
                            type="button"
                        >
                            <RefreshCw
                                class="h-3 w-3 sm:h-4 sm:w-4"
                                :class="{ 'animate-spin': isLoading }"
                            />
                            <span class="hidden xs:inline">Regenerate</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="isTwoFactorEnabled"
            class="rounded-lg border border-green-200 bg-green-50 p-3 sm:p-4"
        >
            <div class="flex items-start gap-2 sm:gap-3">
                <Check class="mt-0.5 h-4 w-4 flex-shrink-0 text-green-600 sm:h-5 sm:w-5" />
                <div class="min-w-0">
                    <p class="text-xs font-medium text-green-900 sm:text-sm">
                        Two-factor authentication is enabled
                    </p>
                    <p class="text-xs text-green-700 sm:text-sm">
                        Your account is protected with an extra layer of
                        security. You'll need your authenticator app to sign in.
                    </p>
                    <button
                        @click="toggleRecoveryCodes()"
                        class="mt-1.5 text-xs text-green-700 underline transition-colors hover:text-green-900 sm:mt-2 sm:text-sm"
                        type="button"
                    >
                        {{ showRecoveryCodes ? 'Hide' : 'Show' }} recovery codes
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
input[type='text'] {
    letter-spacing: 0.5em;
}

@media (min-width: 480px) {
    .xs\:inline {
        display: inline !important;
    }
}

:deep(svg) {
    max-width: 100%;
    height: auto;
}
</style>
