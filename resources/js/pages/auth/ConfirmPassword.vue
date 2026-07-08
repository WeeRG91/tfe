<script setup lang="ts">
import CostumedAuthLayout from '@/layouts/CostumedAuthLayout.vue';
import { store } from '@/routes/password/confirm';
import { Head, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, MoveLeft } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

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
                toast.error('Unable to confirm your password.');
            }
        },
    });
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head title="Confirm password" />

    <CostumedAuthLayout
        title="Confirm your password"
        subtitle="This is a secure area of the application. Please confirm your password before continuing."
        badge="Password"
    >
        <form @submit.prevent="submit" class="space-y-6">
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
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Enter your password"
                        class="w-full rounded-xl border border-gray-200 bg-white/50 py-2.5 pr-12 pl-4 text-sm text-gray-900 backdrop-blur-sm transition-all duration-200 placeholder:text-gray-400 focus:border-gray-400 focus:bg-white focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                        :class="{
                            'border-red-300 focus:border-red-400':
                                form.errors.password,
                        }"
                        required
                        autofocus
                    />

                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute top-1/2 right-3 -translate-y-1/2 p-1 text-gray-400 transition-colors hover:text-gray-600"
                    >
                        <Eye v-if="showPassword" class="h-5 w-5" />
                        <EyeOff v-else class="h-5 w-5" />
                    </button>
                </div>

                <p
                    v-if="form.errors.password"
                    class="mt-1.5 text-sm text-red-500"
                >
                    {{ form.errors.password }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="group w-full rounded-2xl bg-gray-900 px-4 py-3 text-sm font-medium text-white transition-all duration-200 hover:bg-gray-800 hover:shadow-lg hover:shadow-gray-900/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-gray-900 disabled:hover:shadow-none"
            >
                <span class="flex items-center justify-center gap-2">
                    <span
                        v-if="form.processing"
                        class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                    ></span>

                    <span v-else>Confirm Password</span>
                </span>
            </button>
        </form>

        <template #footer>
            <div
                class="rounded-2xl backdrop-blur-sm transition-all "
            >
                <div class="flex items-center justify-center gap-2">
                    <button
                        @click="goBack"
                        class="group inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-all hover:text-gray-900 focus:outline-none active:scale-95 cursor-pointer"
                    >
                        <MoveLeft
                            class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-1"
                        />
                        <span>Go Back</span>
                    </button>
                </div>
            </div>
        </template>
    </CostumedAuthLayout>
</template>
