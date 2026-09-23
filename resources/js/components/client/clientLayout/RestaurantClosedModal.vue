<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useRestaurantStore } from '@/stores/restaurant';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { AppPageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';

const restaurantStore = useRestaurantStore();
const { t } = useI18n();
const page = usePage<AppPageProps>();

const companyDeliveryEnabled = computed(
    () => page.props.features?.companyDelivery ?? false,
);

const message = computed(() => {
    return (
        restaurantStore.closedModalMessage?.trim() ||
        restaurantStore.current?.message?.trim() ||
        t('restaurant.closed')
    );
});
</script>

<template>
    <Dialog
        :open="restaurantStore.isClosedModalOpen"
        @update:open="
            (open) => {
                if (!open) restaurantStore.closeClosedModal();
            }
        "
    >
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>
                    {{ t('restaurant.closedTitle') }}
                </DialogTitle>

                <DialogDescription class="space-y-3">
                    <p>
                        {{ message }}
                    </p>

                    <p class="font-medium text-foreground">
                        {{
                            companyDeliveryEnabled
                                ? t(
                                      'restaurant.advanceOrderDescriptionWithCompany',
                                  )
                                : t('restaurant.advanceOrderDescription')
                        }}
                    </p>
                </DialogDescription>
            </DialogHeader>

            <DialogFooter>
                <Button @click="restaurantStore.closeClosedModal()">
                    {{ t('restaurant.understood') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
