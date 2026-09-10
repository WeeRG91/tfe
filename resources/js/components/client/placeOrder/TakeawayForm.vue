<script setup lang="ts">
import { CalendarDays, Phone, User, Clock3 } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    pickupName: string;
    pickupPhone: string;
    pickupTime: string;
}>();

const emit = defineEmits<{
    'update:pickupName': [value: string];
    'update:pickupPhone': [value: string];
    'update:pickupTime': [value: string];
}>();

const { t, locale } = useI18n();

const OPENING_HOUR = 11;
const CLOSING_HOUR = 21;
const SLOT_INTERVAL_MINUTES = 15;
const MINIMUM_PREPARATION_MINUTES = 30;
const NUMBER_OF_DAYS = 7;

const selectedDate = ref<string>('');
const currentTime = ref(new Date());

const dateKey = (date: Date): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const localDateTimeValue = (date: Date): string => {
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${dateKey(date)}T${hours}:${minutes}`;
};

const createDateTime = (date: Date, hours: number, minutes: number): Date => {
    const result = new Date(date);

    result.setHours(hours, minutes, 0, 0);

    return result;
};

const minimumPickupDate = (): Date => {
    const minimum = new Date(currentTime.value);

    minimum.setMinutes(minimum.getMinutes() + MINIMUM_PREPARATION_MINUTES);
    minimum.setSeconds(0, 0);

    const remainder = minimum.getMinutes() % SLOT_INTERVAL_MINUTES;

    if (remainder !== 0) {
        minimum.setMinutes(
            minimum.getMinutes() + SLOT_INTERVAL_MINUTES - remainder,
        );
    }

    return minimum;
};

const hasAvailableSlots = (date: Date): boolean => {
    const lastSlot = createDateTime(date, CLOSING_HOUR, 0);

    return lastSlot >= minimumPickupDate();
};

const availableDates = computed(() =>
    Array.from({ length: NUMBER_OF_DAYS }, (_, index) => {
        const date = new Date(currentTime.value);

        date.setDate(date.getDate() + index);
        date.setHours(0, 0, 0, 0);

        return {
            value: dateKey(date),
            date,
            isToday: index === 0,
            available: hasAvailableSlots(date),
            weekday: new Intl.DateTimeFormat(locale.value, {
                weekday: 'short',
            }).format(date),
            day: new Intl.DateTimeFormat(locale.value, {
                day: 'numeric',
            }).format(date),
            month: new Intl.DateTimeFormat(locale.value, {
                month: 'short',
            }).format(date),
        };
    }),
);

const timeSlots = computed(() => {
    const selected = availableDates.value.find(
        (date) => date.value === selectedDate.value,
    );

    if (!selected) {
        return [];
    }

    const slots: Array<{
        value: string;
        label: string;
    }> = [];

    const minimum = minimumPickupDate();

    for (
        let minutes = OPENING_HOUR * 60;
        minutes <= CLOSING_HOUR * 60;
        minutes += SLOT_INTERVAL_MINUTES
    ) {
        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;

        const slotDate = createDateTime(selected.date, hours, remainingMinutes);

        if (slotDate < minimum) {
            continue;
        }

        slots.push({
            value: localDateTimeValue(slotDate),
            label: new Intl.DateTimeFormat(locale.value, {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            }).format(slotDate),
        });
    }

    return slots;
});

const selectedPickupLabel = computed(() => {
    if (!props.pickupTime) {
        return null;
    }

    const date = new Date(props.pickupTime);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    return new Intl.DateTimeFormat(locale.value, {
        dateStyle: 'full',
        timeStyle: 'short',
    }).format(date);
});

const selectDate = (value: string) => {
    if (selectedDate.value === value) {
        return;
    }

    selectedDate.value = value;

    if (!props.pickupTime.startsWith(value)) {
        emit('update:pickupTime', '');
    }
};

const selectTime = (value: string) => {
    emit('update:pickupTime', value);
};

watch(
    () => props.pickupTime,
    (value) => {
        if (value) {
            selectedDate.value = value.slice(0, 10);
        }
    },
);

onMounted(() => {
    if (props.pickupTime) {
        selectedDate.value = props.pickupTime.slice(0, 10);

        return;
    }

    selectedDate.value =
        availableDates.value.find((date) => date.available)?.value ??
        availableDates.value[0]?.value ??
        '';
});
</script>

<template>
    <div class="rounded-lg border bg-white p-6">
        <h2
            class="mb-5 flex items-center gap-2 text-lg font-semibold uppercase"
        >
            <CalendarDays class="h-5 w-5" />
            {{ t('cart.orderType.takeawayForm.title') }}
        </h2>

        <div class="space-y-5">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    {{ t('cart.orderType.takeawayForm.pickupName') }}
                    <span class="text-red-500">*</span>
                </label>

                <div class="relative">
                    <User
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                    />

                    <input
                        :value="pickupName"
                        type="text"
                        :placeholder="
                            t(
                                'cart.orderType.takeawayForm.pickupNamePlaceholder',
                            )
                        "
                        class="w-full rounded-md border border-gray-300 py-2.5 pr-3 pl-10 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                        @input="
                            emit(
                                'update:pickupName',
                                ($event.target as HTMLInputElement).value,
                            )
                        "
                    />
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                    {{ t('cart.orderType.takeawayForm.pickupPhone') }}
                    <span class="text-red-500">*</span>
                </label>

                <div class="relative">
                    <Phone
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                    />

                    <input
                        :value="pickupPhone"
                        type="tel"
                        :placeholder="
                            t(
                                'cart.orderType.takeawayForm.pickupPhonePlaceholder',
                            )
                        "
                        class="w-full rounded-md border border-gray-300 py-2.5 pr-3 pl-10 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                        @input="
                            emit(
                                'update:pickupPhone',
                                ($event.target as HTMLInputElement).value,
                            )
                        "
                    />
                </div>

                <p class="mt-1 text-xs text-gray-500">
                    {{ t('cart.orderType.takeawayForm.pickupPhoneHelper') }}
                </p>
            </div>

            <div class="border-t pt-5">
                <div class="mb-3 flex items-center gap-2">
                    <CalendarDays class="h-4 w-4 text-red-500" />

                    <label class="text-sm font-semibold text-gray-800">
                        {{ t('cart.orderType.takeawayForm.selectDate') }}
                        <span class="text-red-500">*</span>
                    </label>
                </div>

                <div
                    class="grid grid-cols-4 gap-2 sm:grid-cols-7"
                    role="radiogroup"
                >
                    <button
                        v-for="date in availableDates"
                        :key="date.value"
                        type="button"
                        role="radio"
                        :aria-checked="selectedDate === date.value"
                        :disabled="!date.available"
                        class="flex min-h-20 flex-col items-center justify-center rounded-xl border-2 px-2 py-2 transition-all"
                        :class="[
                            selectedDate === date.value
                                ? 'border-red-500 bg-red-50 text-red-700 shadow-sm'
                                : 'border-gray-200 bg-white text-gray-700 hover:border-red-200 hover:bg-red-50/40',
                            !date.available
                                ? 'cursor-not-allowed opacity-40'
                                : 'cursor-pointer',
                        ]"
                        @click="selectDate(date.value)"
                    >
                        <span class="text-xs font-medium uppercase">
                            {{
                                date.isToday
                                    ? t('cart.orderType.takeawayForm.today')
                                    : date.weekday
                            }}
                        </span>

                        <span class="mt-1 text-xl font-bold">
                            {{ date.day }}
                        </span>

                        <span class="text-xs">
                            {{ date.month }}
                        </span>
                    </button>
                </div>
            </div>

            <div>
                <div
                    class="mb-3 flex flex-wrap items-center justify-between gap-2"
                >
                    <div class="flex items-center gap-2">
                        <Clock3 class="h-4 w-4 text-red-500" />

                        <label class="text-sm font-semibold text-gray-800">
                            {{ t('cart.orderType.takeawayForm.selectTime') }}
                            <span class="text-red-500">*</span>
                        </label>
                    </div>

                    <span
                        class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600"
                    >
                        {{ t('cart.orderType.takeawayForm.openingHours') }}
                    </span>
                </div>

                <div
                    v-if="timeSlots.length"
                    class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5"
                    role="radiogroup"
                >
                    <button
                        v-for="slot in timeSlots"
                        :key="slot.value"
                        type="button"
                        role="radio"
                        :aria-checked="pickupTime === slot.value"
                        class="rounded-lg border-2 px-3 py-2.5 text-sm font-semibold transition-all"
                        :class="
                            pickupTime === slot.value
                                ? 'border-red-500 bg-red-500 text-white shadow-sm'
                                : 'border-gray-200 bg-white text-gray-700 hover:border-red-300 hover:bg-red-50'
                        "
                        @click="selectTime(slot.value)"
                    >
                        {{ slot.label }}
                    </button>
                </div>

                <div
                    v-else
                    class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800"
                >
                    {{ t('cart.orderType.takeawayForm.noSlots') }}
                </div>

                <p class="mt-2 text-xs text-gray-500">
                    {{ t('cart.orderType.takeawayForm.pickupTimeHelper') }}
                </p>
            </div>

            <Transition name="selection">
                <div
                    v-if="selectedPickupLabel"
                    class="flex items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 p-3 text-sm text-blue-800"
                >
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100"
                    >
                        <CalendarDays class="h-4 w-4" />
                    </div>

                    <div>
                        <p class="text-xs font-medium text-blue-600">
                            {{ t('cart.orderType.takeawayForm.readyAt') }}
                        </p>

                        <p class="mt-0.5 font-semibold">
                            {{ selectedPickupLabel }}
                        </p>
                    </div>
                </div>
            </Transition>
        </div>
    </div>
</template>

<style scoped>
.selection-enter-active,
.selection-leave-active {
    transition:
        opacity 0.25s ease,
        transform 0.25s ease;
}

.selection-enter-from,
.selection-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
