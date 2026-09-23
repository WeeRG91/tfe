<script setup lang="ts">
import CostumedAuthLayout from '@/layouts/CostumedAuthLayout.vue';
import { store } from '@/routes/password/confirm';
import { Head, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, MoveLeft } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const { t } = useI18n();

const showPassword = ref(false);

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(store().url, {
        preserveScroll: true,

        onSuccess: () => {
            form.reset();
        },

        onError: (errors) => {
            if (errors.password) {
                toast.error(errors.password);
            } else {
                toast.error(t('confirmPassword.messages.confirmFailed'));
            }
        },
    });
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head :title="t('confirmPassword.title')" />

    <CostumedAuthLayout
        :title="t('confirmPassword.heading')"
        :subtitle="t('confirmPassword.subtitle')"
        :badge="t('confirmPassword.badge')"
    >
        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label
                    for="password"
                    class="mb-1.5 block text-sm font-medium text-foreground"
                >
                    {{ t('confirmPassword.fields.password') }}
                </label>

                <div class="group relative">
                    <input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        :placeholder="
                            t('confirmPassword.placeholders.password')
                        "
                        class="w-full rounded-xl border border-border bg-background/50 py-2.5 pr-12 pl-4 text-sm text-foreground backdrop-blur-sm transition-all duration-200 placeholder:text-muted-foreground focus:border-ring focus:bg-background focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                        :class="{
                            'border-destructive focus:border-destructive':
                                form.errors.password,
                        }"
                        required
                        autofocus
                    />

                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute top-1/2 right-3 -translate-y-1/2 p-1 text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <Eye v-if="showPassword" class="h-5 w-5" />
                        <EyeOff v-else class="h-5 w-5" />
                    </button>
                </div>

                <p
                    v-if="form.errors.password"
                    class="mt-1.5 text-sm text-destructive"
                >
                    {{ form.errors.password }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="group w-full rounded-2xl bg-primary px-4 py-3 text-sm font-medium text-primary-foreground transition-all duration-200 hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-primary disabled:hover:shadow-none"
            >
                <span class="flex items-center justify-center gap-2">
                    <span
                        v-if="form.processing"
                        class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-primary-foreground border-t-transparent"
                    ></span>

                    <span v-else>{{ t('auth.buttons.confirmPassword') }}</span>
                </span>
            </button>
        </form>

        <template #footer>
            <div class="rounded-2xl backdrop-blur-sm transition-all">
                <div class="flex items-center justify-center gap-2">
                    <button
                        @click="goBack"
                        class="group inline-flex cursor-pointer items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-foreground transition-all hover:text-foreground focus:outline-none active:scale-95"
                    >
                        <MoveLeft
                            class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-1"
                        />
                        <span>{{ t('auth.buttons.goBack') }}</span>
                    </button>
                </div>
            </div>
        </template>
    </CostumedAuthLayout>
</template>
