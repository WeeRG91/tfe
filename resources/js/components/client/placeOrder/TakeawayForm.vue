<script setup lang="ts">
import { CalendarDays, User, Phone } from 'lucide-vue-next';
import { formatDate } from '@/lib/utils';

defineProps<{
    pickupName: string;
    pickupPhone: string;
    pickupTime: string;
    minPickupTime: string;
    maxPickupTime: string;
}>();

const emit = defineEmits<{
    'update:pickupName': [value: string];
    'update:pickupPhone': [value: string];
    'update:pickupTime': [value: string];
}>();
</script>

<template>
    <div class="rounded-lg border bg-white p-6">
        <h2
            class="mb-4 flex items-center gap-2 text-lg font-semibold uppercase"
        >
            <CalendarDays class="h-5 w-5" />
            Pickup Information
        </h2>

        <div class="space-y-4">
            <!-- Pickup Name -->
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Pickup Name *
                </label>
                <div class="relative">
                    <User
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                    />
                    <input
                        :value="pickupName"
                        @input="
                            emit(
                                'update:pickupName',
                                ($event.target as HTMLInputElement).value,
                            )
                        "
                        type="text"
                        placeholder="Full name for pickup"
                        class="w-full rounded-md border border-gray-300 py-2.5 pr-3 pl-10 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                    />
                </div>
            </div>

            <!-- Pickup Phone -->
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Pickup Phone *
                </label>
                <div class="relative">
                    <Phone
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                    />
                    <input
                        :value="pickupPhone"
                        @input="
                            emit(
                                'update:pickupPhone',
                                ($event.target as HTMLInputElement).value,
                            )
                        "
                        type="tel"
                        placeholder="Phone number for contact"
                        class="w-full rounded-md border border-gray-300 py-2.5 pr-3 pl-10 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                    />
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    We'll send order updates to this number
                </p>
            </div>

            <!-- Pickup Time -->
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Preferred Pickup Time *
                </label>
                <input
                    :value="pickupTime"
                    @input="
                        emit(
                            'update:pickupTime',
                            ($event.target as HTMLInputElement).value,
                        )
                    "
                    type="datetime-local"
                    :min="minPickupTime"
                    :max="maxPickupTime"
                    class="w-full rounded-md border border-gray-300 p-2.5 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                />
                <p class="mt-1 text-xs text-gray-500">
                    Minimum 30 minutes from now, up to 7 days in advance
                </p>
                <div
                    v-if="pickupTime"
                    class="mt-2 rounded-md bg-blue-50 p-2 text-sm text-blue-700"
                >
                    📅 Your order will be ready at: {{ formatDate(pickupTime) }}
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
