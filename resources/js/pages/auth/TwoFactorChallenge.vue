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
import { useI18n } from 'vue-i18n';

interface AuthConfigContent {
    title: string;
    description: string;
    toggleText: string;
}

const { t } = useI18n();

const authConfigContent = computed<AuthConfigContent>(() => {
    if (showRecoveryInput.value) {
        return {
            title: t('twoFactorChallenge.recoveryCode.title'),
            description: t('twoFactorChallenge.recoveryCode.description'),
            toggleText: t('twoFactorChallenge.recoveryCode.toggleText'),
        };
    }

    return {
        title: t('twoFactorChallenge.authenticationCode.title'),
        description: t('twoFactorChallenge.authenticationCode.description'),
        toggleText: t('twoFactorChallenge.authenticationCode.toggleText'),
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
    <Head :title="t('twoFactorChallenge.title')" />

    <CostumedAuthLayout
        :title="authConfigContent.title"
        :subtitle="authConfigContent.description"
        :badge="t('twoFactorChallenge.badge')"
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
                        class="group w-full rounded-2xl bg-primary px-4 py-3 text-sm font-medium text-primary-foreground transition-all duration-200 hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-primary disabled:hover:shadow-none"
                    >
                        <span class="flex items-center justify-center gap-2">
                            <span
                                v-if="processing"
                                class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-primary-foreground border-t-transparent"
                            ></span>
                            <span v-else>
                                {{ t('twoFactorChallenge.continue') }}
                            </span>
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
                        :placeholder="
                            t('twoFactorChallenge.recoveryCode.placeholder')
                        "
                        :autofocus="showRecoveryInput"
                        required
                    />
                    <InputError :message="errors.recovery_code" />
                    <button
                        type="submit"
                        :disabled="processing"
                        class="group w-full rounded-2xl bg-primary px-4 py-3 text-sm font-medium text-primary-foreground transition-all duration-200 hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-primary disabled:hover:shadow-none"
                    >
                        <span class="flex items-center justify-center gap-2">
                            <span
                                v-if="processing"
                                class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-primary-foreground border-t-transparent"
                            ></span>
                            <span v-else>
                                {{ t('twoFactorChallenge.continue') }}
                            </span>
                        </span>
                    </button>
                </Form>
            </template>
        </div>
        <template #footer>
            <div class="text-center text-sm text-muted-foreground">
                <span> {{ t('twoFactorChallenge.orYouCan') }}&nbsp; </span>
                <button
                    type="button"
                    class="text-foreground underline decoration-border underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current!"
                    @click="() => toggleRecoveryMode()"
                >
                    {{ authConfigContent.toggleText }}
                </button>
            </div>
        </template>
    </CostumedAuthLayout>
</template>
