<script setup lang="ts">
import ConfirmModal from '@/components/ConfirmModal.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { BreadcrumbItem } from '@/types';
import {
    RestaurantClosureType,
    RestaurantHourType,
} from '@/types/restaurant-schedule';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    CalendarClock,
    CalendarPlus,
    CalendarX2,
    Loader2,
    Pencil,
    Save,
    Trash2,
} from 'lucide-vue-next';
import { computed, nextTick, ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    hours: RestaurantHourType[];
    closures: RestaurantClosureType[];
    timezone: string;
}>();

const editableHours = ref<RestaurantHourType[]>(
    props.hours.map((day) => ({ ...day })),
);
const isSavingHours = ref<boolean>(false);
const hourErrors = ref<Record<string, string[]>>({});

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Restaurant schedule',
        href: '/admin/restaurant-schedule',
    },
]);

const weekdayLabels: Record<number, string> = {
    1: 'Monday',
    2: 'Tuesday',
    3: 'Wednesday',
    4: 'Thursday',
    5: 'Friday',
    6: 'Saturday',
    7: 'Sunday',
};

type ClosureFormType = {
    is_all_day: boolean;
    starts_on: string;
    ends_on: string;
    starts_at: string;
    ends_at: string;
    reason: string;
    public_message: string;
};

const emptyClosureForm = (): ClosureFormType => ({
    is_all_day: true,
    starts_on: '',
    ends_on: '',
    starts_at: '',
    ends_at: '',
    reason: '',
    public_message: '',
});

const closureForm = ref<ClosureFormType>(emptyClosureForm());
const isSavingClosure = ref<boolean>(false);
const closureErrors = ref<Record<string, string[]>>({});

const editingClosureId = ref<number | null>(null);
const closureFormElement = ref<HTMLFormElement | null>(null);
const closureToDelete = ref<RestaurantClosureType | null>(null);
const isDeletingClosure = ref<boolean>(false);

const formatDateTime = (value: string): string => {
    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const closurePeriod = (closure: RestaurantClosureType): string => {
    if (closure.is_all_day) {
        if (closure.starts_on === closure.ends_on) {
            return closure.starts_on ?? '';
        }

        return `${closure.starts_on} – ${closure.ends_on}`;
    }

    return `${formatDateTime(closure.starts_at)} – ${formatDateTime(
        closure.ends_at,
    )}`;
};

const ensureOpenTimes = (day: RestaurantHourType): void => {
    if (!day.is_open) {
        return;
    }

    day.opens_at ??= '11:00';
    day.closes_at ??= '22:00';
    day.last_pickup_at ??= '21:00';
};

const errorFor = (index: number, field: string): string | null => {
    const errors = hourErrors.value[`hours.${index}.${field}`];

    return errors?.[0] ?? null;
};

const saveRegularHours = async (): Promise<void> => {
    isSavingHours.value = true;
    hourErrors.value = {};

    const hours = editableHours.value.map((day) => ({
        weekday: day.weekday,
        is_open: day.is_open,
        opens_at: day.is_open ? day.opens_at : null,
        closes_at: day.is_open ? day.closes_at : null,
        last_pickup_at: day.is_open ? day.last_pickup_at : null,
    }));

    try {
        await axios.put('/admin/restaurant-schedule/hours', { hours });

        editableHours.value = editableHours.value.map((day) => ({
            ...day,
            opens_at: day.is_open ? day.opens_at : null,
            closes_at: day.is_open ? day.closes_at : null,
            last_pickup_at: day.is_open ? day.last_pickup_at : null,
        }));

        toast.success('Regular opening hours saved.');
    } catch (error: unknown) {
        if (axios.isAxiosError(error) && error.response?.status === 422) {
            hourErrors.value = error.response.data.errors ?? {};

            toast.error('Please correct the highlighted schedule fields.');

            return;
        }

        console.error(error);

        toast.error('Unable to save regular opening hours.');
    } finally {
        isSavingHours.value = false;
    }
};

const closureError = (field: string): string | null => {
    return closureErrors.value[field]?.[0] ?? null;
};

const resetClosureForm = (): void => {
    closureForm.value = emptyClosureForm();
    closureErrors.value = {};
    editingClosureId.value = null;
};

const saveClosure = async (): Promise<void> => {
    isSavingClosure.value = true;
    closureErrors.value = {};

    const payload = closureForm.value.is_all_day
        ? {
              is_all_day: true,
              starts_on: closureForm.value.starts_on,
              ends_on: closureForm.value.ends_on,
              starts_at: null,
              ends_at: null,
              reason: closureForm.value.reason || null,
              public_message: closureForm.value.public_message || null,
          }
        : {
              is_all_day: false,
              starts_on: null,
              ends_on: null,
              starts_at: closureForm.value.starts_at,
              ends_at: closureForm.value.ends_at,
              reason: closureForm.value.reason || null,
              public_message: closureForm.value.public_message || null,
          };

    try {
        const wasEditing = editingClosureId.value !== null;

        if (editingClosureId.value === null) {
            await axios.post('/admin/restaurant-schedule/closures', payload);
        } else {
            await axios.put(
                `/admin/restaurant-schedule/closures/${editingClosureId.value}`,
                payload,
            );
        }

        toast.success(
            wasEditing
                ? 'Exceptional closure updated.'
                : 'Exceptional closure added.',
        );

        resetClosureForm();

        router.reload({
            only: ['closures'],
        });
    } catch (error: unknown) {
        if (axios.isAxiosError(error) && error.response?.status === 422) {
            closureErrors.value = error.response.data.errors ?? {};

            toast.error(
                editingClosureId.value === null
                    ? 'Unable to add the exceptional closure.'
                    : 'Unable to update the exceptional closure.',
            );

            return;
        }

        console.error(error);

        toast.error('Unable to add the exceptional closure.');
    } finally {
        isSavingClosure.value = false;
    }
};

const editClosure = async (closure: RestaurantClosureType): Promise<void> => {
    editingClosureId.value = closure.id;
    closureErrors.value = {};

    closureForm.value = {
        is_all_day: closure.is_all_day,
        starts_on: closure.starts_on ?? '',
        ends_on: closure.ends_on ?? '',

        starts_at: closure.is_all_day ? '' : closure.starts_at.slice(0, 16),

        ends_at: closure.is_all_day ? '' : closure.ends_at.slice(0, 16),

        reason: closure.reason ?? '',
        public_message: closure.public_message ?? '',
    };

    await nextTick();

    closureFormElement.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    });
};

const requestClosureDeletion = (closure: RestaurantClosureType): void => {
    closureToDelete.value = closure;
};

const closeDeleteConfirmation = (): void => {
    if (isDeletingClosure.value) {
        return;
    }

    closureToDelete.value = null;
};

const deleteConfirmationMessage = computed(() => {
    const closure = closureToDelete.value;

    if (closure === null) {
        return '';
    }

    const name = closure.reason || 'this exceptional closure';

    return `Are you sure you want to delete ${name}?`;
});

const deleteClosure = async (): Promise<void> => {
    const closure = closureToDelete.value;

    if (closure === null) {
        return;
    }

    isDeletingClosure.value = true;

    try {
        await axios.delete(`/admin/restaurant-schedule/closures/${closure.id}`);

        if (editingClosureId.value === closure.id) {
            resetClosureForm();
        }

        closureToDelete.value = null;

        toast.success('Exceptional closure deleted.');

        router.reload({
            only: ['closures'],
        });
    } catch (error: unknown) {
        console.error(error);

        toast.error('Unable to delete the exceptional closure.');
    } finally {
        isDeletingClosure.value = false;
    }
};
</script>

<template>
    <Head title="Restaurant schedule" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Restaurant schedule
                </h1>

                <p class="mt-1 text-sm text-muted-foreground">
                    Manage regular opening hours and exceptional closures. Times
                    are displayed in {{ props.timezone }}.
                </p>
            </div>

            <section class="rounded-xl border bg-background">
                <div
                    class="flex flex-col gap-4 border-b p-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50 text-red-600"
                        >
                            <CalendarClock class="h-5 w-5" />
                        </div>

                        <div>
                            <h2 class="font-semibold">Regular opening hours</h2>

                            <p class="text-sm text-muted-foreground">
                                The normal weekly restaurant schedule.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        :disabled="isSavingHours"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                        @click="saveRegularHours"
                    >
                        <Loader2
                            v-if="isSavingHours"
                            class="h-4 w-4 animate-spin"
                        />

                        <Save v-else class="h-4 w-4" />

                        {{ isSavingHours ? 'Saving…' : 'Save hours' }}
                    </button>
                </div>

                <div class="divide-y">
                    <div
                        v-for="(day, index) in editableHours"
                        :key="day.id"
                        class="space-y-4 p-4"
                    >
                        <div
                            class="flex flex-col gap-3 lg:flex-row lg:items-start"
                        >
                            <div class="w-36 pt-2">
                                <p class="font-medium">
                                    {{ weekdayLabels[day.weekday] }}
                                </p>
                            </div>

                            <label
                                class="flex w-28 cursor-pointer items-center gap-2 pt-2"
                            >
                                <input
                                    v-model="day.is_open"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500"
                                    @change="ensureOpenTimes(day)"
                                />

                                <span class="text-sm font-medium">
                                    {{ day.is_open ? 'Open' : 'Closed' }}
                                </span>
                            </label>

                            <div
                                v-if="day.is_open"
                                class="grid flex-1 gap-4 sm:grid-cols-3"
                            >
                                <div>
                                    <label
                                        :for="`opens-at-${day.weekday}`"
                                        class="mb-1.5 block text-xs font-medium text-muted-foreground"
                                    >
                                        Opens at
                                    </label>

                                    <input
                                        :id="`opens-at-${day.weekday}`"
                                        v-model="day.opens_at"
                                        type="time"
                                        step="900"
                                        class="w-full rounded-lg border bg-background px-3 py-2 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                        :class="{
                                            'border-red-500': errorFor(
                                                index,
                                                'opens_at',
                                            ),
                                        }"
                                    />

                                    <p
                                        v-if="errorFor(index, 'opens_at')"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ errorFor(index, 'opens_at') }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        :for="`closes-at-${day.weekday}`"
                                        class="mb-1.5 block text-xs font-medium text-muted-foreground"
                                    >
                                        Closes at
                                    </label>

                                    <input
                                        :id="`closes-at-${day.weekday}`"
                                        v-model="day.closes_at"
                                        type="time"
                                        step="900"
                                        class="w-full rounded-lg border bg-background px-3 py-2 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                        :class="{
                                            'border-red-500': errorFor(
                                                index,
                                                'closes_at',
                                            ),
                                        }"
                                    />

                                    <p
                                        v-if="errorFor(index, 'closes_at')"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ errorFor(index, 'closes_at') }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        :for="`last-pickup-${day.weekday}`"
                                        class="mb-1.5 block text-xs font-medium text-muted-foreground"
                                    >
                                        Last pickup
                                    </label>

                                    <input
                                        :id="`last-pickup-${day.weekday}`"
                                        v-model="day.last_pickup_at"
                                        type="time"
                                        step="900"
                                        class="w-full rounded-lg border bg-background px-3 py-2 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                        :class="{
                                            'border-red-500': errorFor(
                                                index,
                                                'last_pickup_at',
                                            ),
                                        }"
                                    />

                                    <p
                                        v-if="errorFor(index, 'last_pickup_at')"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ errorFor(index, 'last_pickup_at') }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-else
                                class="flex flex-1 items-center rounded-lg bg-muted/50 px-4 py-3 text-sm text-muted-foreground"
                            >
                                The restaurant is closed all day.
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border bg-background">
                <div class="flex items-center gap-3 border-b p-5">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600"
                    >
                        <CalendarX2 class="h-5 w-5" />
                    </div>

                    <div>
                        <h2 class="font-semibold">Exceptional closures</h2>

                        <p class="text-sm text-muted-foreground">
                            Full-day and temporary closure periods.
                        </p>
                    </div>
                </div>

                <form
                    ref="closureFormElement"
                    class="scroll-mt-4 space-y-5 border-b bg-muted/20 p-5"
                    @submit.prevent="saveClosure"
                >
                    <div class="flex items-center gap-3">
                        <CalendarPlus class="h-5 w-5 text-amber-600" />

                        <div>
                            <h3 class="font-medium">
                                {{
                                    editingClosureId === null
                                        ? 'Add an exceptional closure'
                                        : 'Edit exceptional closure'
                                }}
                            </h3>

                            <p class="text-sm text-muted-foreground">
                                {{
                                    editingClosureId === null
                                        ? 'Close one or more full days, or select a custom period.'
                                        : 'Update the selected closure period and message.'
                                }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button
                            type="button"
                            class="rounded-lg border px-4 py-2 text-sm font-medium transition"
                            :class="
                                closureForm.is_all_day
                                    ? 'border-red-600 bg-red-50 text-red-700'
                                    : 'bg-background hover:bg-muted'
                            "
                            @click="closureForm.is_all_day = true"
                        >
                            Full day
                        </button>

                        <button
                            type="button"
                            class="rounded-lg border px-4 py-2 text-sm font-medium transition"
                            :class="
                                !closureForm.is_all_day
                                    ? 'border-red-600 bg-red-50 text-red-700'
                                    : 'bg-background hover:bg-muted'
                            "
                            @click="closureForm.is_all_day = false"
                        >
                            Custom hours
                        </button>
                    </div>

                    <div
                        v-if="closureForm.is_all_day"
                        class="grid gap-4 sm:grid-cols-2"
                    >
                        <div>
                            <label
                                for="closure-start-date"
                                class="mb-1.5 block text-sm font-medium"
                            >
                                First closed day
                            </label>

                            <input
                                id="closure-start-date"
                                v-model="closureForm.starts_on"
                                type="date"
                                class="w-full rounded-lg border bg-background px-3 py-2 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                :class="{
                                    'border-red-500': closureError('starts_on'),
                                }"
                            />

                            <p
                                v-if="closureError('starts_on')"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ closureError('starts_on') }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="closure-end-date"
                                class="mb-1.5 block text-sm font-medium"
                            >
                                Last closed day
                            </label>

                            <input
                                id="closure-end-date"
                                v-model="closureForm.ends_on"
                                type="date"
                                :min="closureForm.starts_on || undefined"
                                class="w-full rounded-lg border bg-background px-3 py-2 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                :class="{
                                    'border-red-500': closureError('ends_on'),
                                }"
                            />

                            <p
                                v-if="closureError('ends_on')"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ closureError('ends_on') }}
                            </p>
                        </div>
                    </div>

                    <div v-else class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                for="closure-start-time"
                                class="mb-1.5 block text-sm font-medium"
                            >
                                Closed from
                            </label>

                            <input
                                id="closure-start-time"
                                v-model="closureForm.starts_at"
                                type="datetime-local"
                                step="900"
                                class="w-full rounded-lg border bg-background px-3 py-2 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                :class="{
                                    'border-red-500': closureError('starts_at'),
                                }"
                            />

                            <p
                                v-if="closureError('starts_at')"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ closureError('starts_at') }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="closure-end-time"
                                class="mb-1.5 block text-sm font-medium"
                            >
                                Closed until
                            </label>

                            <input
                                id="closure-end-time"
                                v-model="closureForm.ends_at"
                                type="datetime-local"
                                step="900"
                                :min="closureForm.starts_at || undefined"
                                class="w-full rounded-lg border bg-background px-3 py-2 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                :class="{
                                    'border-red-500': closureError('ends_at'),
                                }"
                            />

                            <p
                                v-if="closureError('ends_at')"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ closureError('ends_at') }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        <div>
                            <label
                                for="closure-reason"
                                class="mb-1.5 block text-sm font-medium"
                            >
                                Internal reason
                            </label>

                            <input
                                id="closure-reason"
                                v-model="closureForm.reason"
                                type="text"
                                maxlength="255"
                                placeholder="For example: Public holiday"
                                class="w-full rounded-lg border bg-background px-3 py-2 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                :class="{
                                    'border-red-500': closureError('reason'),
                                }"
                            />

                            <p class="mt-1 text-xs text-muted-foreground">
                                Only administrators will see this reason.
                            </p>

                            <p
                                v-if="closureError('reason')"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ closureError('reason') }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="closure-public-message"
                                class="mb-1.5 block text-sm font-medium"
                            >
                                Client message
                            </label>

                            <textarea
                                id="closure-public-message"
                                v-model="closureForm.public_message"
                                rows="3"
                                maxlength="500"
                                placeholder="For example: We are closed today for a public holiday."
                                class="w-full resize-none rounded-lg border bg-background px-3 py-2 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                :class="{
                                    'border-red-500':
                                        closureError('public_message'),
                                }"
                            ></textarea>

                            <div class="mt-1 flex justify-between gap-3">
                                <p class="text-xs text-muted-foreground">
                                    Displayed in the client banner and warning
                                    modal.
                                </p>

                                <span class="text-xs text-muted-foreground">
                                    {{ closureForm.public_message.length }}/500
                                </span>
                            </div>

                            <p
                                v-if="closureError('public_message')"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ closureError('public_message') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap justify-end gap-3">
                        <button
                            type="button"
                            :disabled="isSavingClosure"
                            class="rounded-lg border bg-background px-4 py-2 text-sm font-medium transition hover:bg-muted disabled:opacity-60"
                            @click="resetClosureForm"
                        >
                            {{
                                editingClosureId === null
                                    ? 'Reset'
                                    : 'Cancel editing'
                            }}
                        </button>

                        <button
                            type="submit"
                            :disabled="isSavingClosure"
                            class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <Loader2
                                v-if="isSavingClosure"
                                class="h-4 w-4 animate-spin"
                            />

                            <template v-else>
                                <Pencil
                                    v-if="editingClosureId !== null"
                                    class="h-4 w-4"
                                />

                                <CalendarPlus v-else class="h-4 w-4" />
                            </template>

                            {{
                                isSavingClosure
                                    ? editingClosureId === null
                                        ? 'Adding closure…'
                                        : 'Saving changes…'
                                    : editingClosureId === null
                                      ? 'Add closure'
                                      : 'Save changes'
                            }}
                        </button>
                    </div>
                </form>

                <div
                    v-if="props.closures.length === 0"
                    class="p-8 text-center text-sm text-muted-foreground"
                >
                    No exceptional closures have been added.
                </div>

                <div v-else class="divide-y">
                    <article
                        v-for="closure in props.closures"
                        :key="closure.id"
                        class="space-y-2 p-4"
                    >
                        <div
                            class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"
                        >
                            <div>
                                <p class="font-medium">
                                    {{
                                        closure.reason || 'Exceptional closure'
                                    }}
                                </p>

                                <p class="text-sm text-muted-foreground">
                                    {{ closurePeriod(closure) }}
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="w-fit rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700"
                                >
                                    {{
                                        closure.is_all_day
                                            ? 'Full day'
                                            : 'Custom hours'
                                    }}
                                </span>

                                <button
                                    type="button"
                                    class="inline-flex h-8 items-center gap-1.5 rounded-lg border px-2.5 text-xs font-medium transition hover:bg-muted"
                                    @click="editClosure(closure)"
                                >
                                    <Pencil class="h-3.5 w-3.5" />
                                    Edit
                                </button>

                                <button
                                    type="button"
                                    class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-red-200 px-2.5 text-xs font-medium text-red-600 transition hover:bg-red-50"
                                    @click="requestClosureDeletion(closure)"
                                >
                                    <Trash2 class="h-3.5 w-3.5" />
                                    Delete
                                </button>
                            </div>
                        </div>

                        <p v-if="closure.public_message" class="text-sm">
                            Client message:
                            {{ closure.public_message }}
                        </p>

                        <p
                            v-if="closure.created_by"
                            class="text-xs text-muted-foreground"
                        >
                            Created by {{ closure.created_by }}
                        </p>
                    </article>
                </div>
            </section>
        </div>

        <ConfirmModal
            :open="closureToDelete !== null"
            :on-close="closeDeleteConfirmation"
            :message="deleteConfirmationMessage"
            type="destructive"
            :is-loading="isDeletingClosure"
            @confirm="deleteClosure"
        />
    </AdminLayout>
</template>
