<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import {
    PinInput,
    PinInputGroup,
    PinInputSlot,
} from '@/components/ui/pin-input';
import CostumedAuthLayout from '@/layouts/CostumedAuthLayout.vue';
import { store } from '@/routes/two-factor/login';
import { Form, Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface AuthConfigContent {
    title: string;
    description: string;
    toggleText: string;
}

const authConfigContent = computed<AuthConfigContent>(() => {
    if (showRecoveryInput.value) {
        return {
            title: 'Recovery Code',
            description:
                'Please confirm access to your account by entering one of your emergency recovery codes.',
            toggleText: 'login using an authentication code',
        };
    }

    return {
        title: 'Authentication Code',
        description:
            'Enter the authentication code provided by your authenticator application.',
        toggleText: 'login using a recovery code',
    };
});

const showRecoveryInput = ref<boolean>(false);

const toggleRecoveryMode = (): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    code.value = [];
};

const code = ref<number[]>([]);
const codeValue = computed<string>(() => code.value.join(''));
</script>

<template>
    <Head title="Two-Factor Authentication" />

    <CostumedAuthLayout
        :title="authConfigContent.title"
        :subtitle="authConfigContent.description"
        badge="Code"
    >
        <div class="space-y-6">
            <template v-if="!showRecoveryInput">
                <Form
                    v-bind="store.form()"
                    class="space-y-4"
                    reset-on-error
                    @error="code = []"
                    #default="{ errors, processing }"
                >
                    <input type="hidden" name="code" :value="codeValue" />
                    <div
                        class="flex flex-col items-center justify-center space-y-3 text-center"
                    >
                        <div class="flex w-full items-center justify-center">
                            <PinInput
                                id="otp"
                                placeholder="○"
                                v-model="code"
                                type="number"
                                otp
                            >
                                <PinInputGroup>
                                    <PinInputSlot
                                        v-for="(id, index) in 6"
                                        :key="id"
                                        :index="index"
                                        :disabled="processing"
                                        autofocus
                                    />
                                </PinInputGroup>
                            </PinInput>
                        </div>
                        <InputError :message="errors.code" />
                    </div>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="group w-full rounded-2xl bg-gray-900 px-4 py-3 text-sm font-medium text-white transition-all duration-200 hover:bg-gray-800 hover:shadow-lg hover:shadow-gray-900/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-gray-900 disabled:hover:shadow-none"
                    >
                        <span class="flex items-center justify-center gap-2">
                            <span
                                v-if="processing"
                                class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                            ></span>
                            <span v-else>Continue</span>
                        </span>
                    </button>
                </Form>
            </template>

            <template v-else>
                <Form
                    v-bind="store.form()"
                    class="space-y-4"
                    reset-on-error
                    #default="{ errors, processing }"
                >
                    <Input
                        name="recovery_code"
                        type="text"
                        placeholder="Enter recovery code"
                        :autofocus="showRecoveryInput"
                        required
                    />
                    <InputError :message="errors.recovery_code" />
                    <button
                        type="submit"
                        :disabled="processing"
                        class="group w-full rounded-2xl bg-gray-900 px-4 py-3 text-sm font-medium text-white transition-all duration-200 hover:bg-gray-800 hover:shadow-lg hover:shadow-gray-900/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-gray-900 disabled:hover:shadow-none"
                    >
                        <span class="flex items-center justify-center gap-2">
                            <span
                                v-if="processing"
                                class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                            ></span>
                            <span v-else>Continue</span>
                        </span>
                    </button>
                </Form>
            </template>
        </div>
        <template #footer>
            <div class="text-center text-sm text-muted-foreground">
                <span>or you can </span>
                <button
                    type="button"
                    class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                    @click="() => toggleRecoveryMode()"
                >
                    {{ authConfigContent.toggleText }}
                </button>
            </div>
        </template>
    </CostumedAuthLayout>
</template>
