<script setup lang="ts">
import { Table } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    modelValue: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const { t } = useI18n();
</script>

<template>
    <div
        class="rounded-lg border border-border bg-card p-6 text-card-foreground"
    >
        <h2
            class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
        >
            <Table class="h-5 w-5" />
            {{ t('cart.orderType.dineInForm.title') }}
        </h2>

        <div>
            <label class="mb-1 block text-sm font-medium text-card-foreground">
                {{ t('cart.orderType.dineInForm.tableNumber') }}
                <span class="text-sm text-destructive sm:text-[18px]">*</span>
            </label>
            <input
                :value="props.modelValue"
                @input="
                    emit(
                        'update:modelValue',
                        ($event.target as HTMLInputElement).value,
                    )
                "
                type="text"
                :placeholder="
                    t('cart.orderType.dineInForm.tableNumberPlaceholder')
                "
                class="w-full rounded-md border border-input bg-background p-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:ring-1 focus:ring-ring focus:outline-none"
            />
            <p class="mt-1 text-xs text-muted-foreground">
                {{ t('cart.orderType.dineInForm.helperText') }}
            </p>
        </div>
    </div>
</template>

<style scoped></style>
