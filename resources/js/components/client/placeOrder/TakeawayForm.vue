<script setup lang="ts">
import { PickupAvailability } from '@/types/restaurant';
import { CalendarDays, Clock3, Phone, User } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    pickupName: string;
    pickupPhone: string;
    pickupTime: string;
    availability: PickupAvailability | null;
    availabilityLoading: boolean;
    availabilityError: boolean;
}>();

const emit = defineEmits<{
    'update:pickupName': [value: string];
    'update:pickupPhone': [value: string];
    'update:pickupTime': [value: string];
    retry: [];
}>();

const { t, locale } = useI18n();

const selectedDate = ref<string>('');

const calendarDate = (value: string): Date => new Date(`${value}T12:00:00Z`);

const availableDates = computed(() =>
    (props.availability?.dates ?? []).map((date, index) => {
        const displayDate = calendarDate(date.date);

        return {
            value: date.date,
            isToday: index === 0,
            available: date.available && date.slots.length > 0,
            weekday: new Intl.DateTimeFormat(locale.value, {
                weekday: 'short',
                timeZone: 'UTC',
            }).format(displayDate),
            day: Number(date.date.slice(8, 10)),
            month: new Intl.DateTimeFormat(locale.value, {
                month: 'short',
                timeZone: 'UTC',
            }).format(displayDate),
        };
    }),
);

const selectedDay = computed(() =>
    props.availability?.dates.find((date) => date.date === selectedDate.value),
);

const timeSlots = computed(() => selectedDay.value?.slots ?? []);

const selectedPickupLabel = computed(() => {
    if (!props.pickupTime || !props.availability) {
        return null;
    }

    const day = props.availability.dates.find((date) =>
        date.slots.some((slot) => slot.value === props.pickupTime),
    );

    const slot = day?.slots.find((item) => item.value === props.pickupTime);

    if (!day || !slot) {
        return null;
    }

    const dateLabel = new Intl.DateTimeFormat(locale.value, {
        dateStyle: 'full',
        timeZone: 'UTC',
    }).format(calendarDate(day.date));

    return `${dateLabel} · ${slot.label}`;
});

const selectDate = (value: string) => {
    selectedDate.value = value;

    const day = props.availability?.dates.find((date) => date.date === value);

    const selectionBelongsToDay = day?.slots.some(
        (slot) => slot.value === props.pickupTime,
    );

    if (!selectionBelongsToDay) {
        emit('update:pickupTime', '');
    }
};

const selectTime = (value: string) => {
    if (!timeSlots.value.some((slot) => slot.value === value)) {
        return;
    }

    emit('update:pickupTime', value);
};

watch(
    [() => props.availability, () => props.pickupTime],
    () => {
        const dates = props.availability?.dates ?? [];

        const selectedPickupDay = dates.find((date) =>
            date.slots.some((slot) => slot.value === props.pickupTime),
        );

        if (selectedPickupDay) {
            selectedDate.value = selectedPickupDay.date;
            return;
        }

        const currentDay = dates.find(
            (date) =>
                date.date === selectedDate.value &&
                date.available &&
                date.slots.length > 0,
        );

        selectedDate.value =
            currentDay?.date ??
            dates.find((date) => date.available && date.slots.length > 0)
                ?.date ??
            dates[0]?.date ??
            '';
    },
    { immediate: true },
);
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

            <div
                v-if="availabilityLoading"
                role="status"
                class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600"
            >
                {{ t('restaurant.pickupLoading') }}
            </div>

            <div
                v-else-if="availabilityError"
                role="alert"
                class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"
            >
                <p>{{ t('restaurant.pickupError') }}</p>

                <button
                    type="button"
                    class="mt-3 rounded-lg border border-red-300 px-3 py-2 font-semibold hover:bg-red-100"
                    @click="emit('retry')"
                >
                    {{ t('restaurant.retry') }}
                </button>
            </div>

            <template
                v-if="
                    !availabilityLoading && !availabilityError && availability
                "
            >
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
                                {{
                                    t('cart.orderType.takeawayForm.selectTime')
                                }}
                                <span class="text-red-500">*</span>
                            </label>
                        </div>

                        <span
                            class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600"
                        >
                            {{
                                t('restaurant.pickupTimezone', {
                                    timezone: availability?.timezone,
                                })
                            }}
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
                        {{ t('restaurant.pickupHelp') }}
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
            </template>
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
