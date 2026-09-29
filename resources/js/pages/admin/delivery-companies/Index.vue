<script setup lang="ts">
import ConfirmModal from '@/components/ConfirmModal.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import deliveryCompanies from '@/routes/admin/delivery-companies';
import type { BreadcrumbItem } from '@/types';
import type {
    CompanyDeliveryDateType,
    DeliveryCompanyType,
} from '@/types/delivery-company';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    Building2,
    CalendarDays,
    Clock3,
    Loader2,
    Pencil,
    Plus,
    Power,
    Save,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    companies: DeliveryCompanyType[];
    defaultMinimumAdvanceDays: number;
}>();

const { t, locale } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('deliveryCompanies.title'),
        href: deliveryCompanies.index().url,
    },
]);

const formatDate = (value: string): string =>
    new Intl.DateTimeFormat(locale.value, {
        dateStyle: 'long',
        timeZone: 'UTC',
    }).format(new Date(`${value}T12:00:00Z`));

type CompanyFormType = {
    name: string;
    is_active: boolean;
    minimum_advance_days: number;
};

const emptyCompanyForm = (): CompanyFormType => ({
    name: '',
    is_active: true,
    minimum_advance_days: props.defaultMinimumAdvanceDays,
});

const companyForm = ref<CompanyFormType>(emptyCompanyForm());
const companyErrors = ref<Record<string, string[]>>({});
const isCreatingCompany = ref<boolean>(false);

const createCompany = async () => {
    companyErrors.value = {};
    isCreatingCompany.value = true;

    try {
        await axios.post(deliveryCompanies.store().url, companyForm.value);

        companyForm.value = emptyCompanyForm();

        toast.success(t('deliveryCompanies.created'));

        router.reload({
            only: ['companies'],
        });
    } catch (error) {
        if (axios.isAxiosError(error) && error.response?.status === 422) {
            companyErrors.value = error.response.data.errors ?? {};
        }

        toast.error(t('deliveryCompanies.createFailed'));
    } finally {
        isCreatingCompany.value = false;
    }
};

const editingCompanyId = ref<number | null>(null);
const editCompanyForm = ref<CompanyFormType>(emptyCompanyForm());
const companyUpdateErrors = ref<Record<string, string[]>>({});
const isUpdatingCompany = ref<boolean>(false);

const startEditingCompany = (company: DeliveryCompanyType) => {
    editingCompanyId.value = company.id;

    editCompanyForm.value = {
        name: company.name,
        is_active: company.is_active,
        minimum_advance_days: company.minimum_advance_days,
    };

    companyUpdateErrors.value = {};
};

const cancelEditingCompany = () => {
    if (isUpdatingCompany.value) {
        return;
    }

    editingCompanyId.value = null;
    editCompanyForm.value = emptyCompanyForm();
    companyUpdateErrors.value = {};
};

const updateCompany = async (company: DeliveryCompanyType) => {
    companyUpdateErrors.value = {};
    isUpdatingCompany.value = true;

    try {
        await axios.put(
            deliveryCompanies.update(company.id).url,
            editCompanyForm.value,
        );

        toast.success(t('deliveryCompanies.updated'));

        editingCompanyId.value = null;

        router.reload({
            only: ['companies'],
        });
    } catch (error) {
        if (axios.isAxiosError(error) && error.response?.status === 422) {
            companyUpdateErrors.value = error.response.data.errors ?? {};
        }

        toast.error(t('deliveryCompanies.updateFailed'));
    } finally {
        isUpdatingCompany.value = false;
    }
};

const companyToDelete = ref<DeliveryCompanyType | null>(null);
const isDeletingCompany = ref<boolean>(false);

const requestCompanyDeletion = (company: DeliveryCompanyType) => {
    companyToDelete.value = company;
};

const closeCompanyDeletion = () => {
    if (!isDeletingCompany.value) {
        companyToDelete.value = null;
    }
};

const companyDeletionMessage = computed(() => {
    if (!companyToDelete.value) {
        return '';
    }

    return t('deliveryCompanies.confirmDeleteCompany', {
        name: companyToDelete.value.name,
    });
});

const deleteCompany = async () => {
    if (!companyToDelete.value) {
        return;
    }

    isDeletingCompany.value = true;

    try {
        await axios.delete(
            deliveryCompanies.destroy(companyToDelete.value.id).url,
        );

        toast.success(t('deliveryCompanies.deleted'));

        if (editingCompanyId.value === companyToDelete.value.id) {
            editingCompanyId.value = null;
        }

        companyToDelete.value = null;

        router.reload({
            only: ['companies'],
        });
    } catch {
        toast.error(t('deliveryCompanies.deleteFailed'));
    } finally {
        isDeletingCompany.value = false;
    }
};

const deliveryDateValues = ref<Record<number, string>>({});
const deliveryDateErrors = ref<Record<number, string>>({});
const companyAddingDateId = ref<number | null>(null);

const addDeliveryDate = async (company: DeliveryCompanyType) => {
    deliveryDateErrors.value[company.id] = '';
    companyAddingDateId.value = company.id;

    try {
        await axios.post(deliveryCompanies.dates.store(company.id).url, {
            delivery_date: deliveryDateValues.value[company.id] ?? '',
            is_available: true,
        });

        deliveryDateValues.value[company.id] = '';

        toast.success(t('deliveryCompanies.dateAdded'));

        router.reload({
            only: ['companies'],
        });
    } catch (error) {
        if (axios.isAxiosError(error) && error.response?.status === 422) {
            deliveryDateErrors.value[company.id] =
                error.response.data.errors?.delivery_date?.[0] ?? '';
        }

        toast.error(t('deliveryCompanies.dateAddFailed'));
    } finally {
        companyAddingDateId.value = null;
    }
};

const updatingDeliveryDateId = ref<number | null>(null);

type DateDeletionTarget = {
    company: DeliveryCompanyType;
    date: CompanyDeliveryDateType;
};

const dateToDelete = ref<DateDeletionTarget | null>(null);

const isDeletingDate = ref<boolean>(false);

const toggleDeliveryDate = async (
    company: DeliveryCompanyType,
    date: CompanyDeliveryDateType,
) => {
    updatingDeliveryDateId.value = date.id;

    try {
        await axios.patch(
            deliveryCompanies.dates.availability([company.id, date.id]).url,
            {
                is_available: !date.is_available,
            },
        );

        toast.success(t('deliveryCompanies.availabilityUpdated'));

        router.reload({
            only: ['companies'],
        });
    } catch {
        toast.error(t('deliveryCompanies.availabilityUpdateFailed'));
    } finally {
        updatingDeliveryDateId.value = null;
    }
};

const requestDateDeletion = (
    company: DeliveryCompanyType,
    date: CompanyDeliveryDateType,
) => {
    dateToDelete.value = {
        company,
        date,
    };
};

const closeDateDeletion = () => {
    if (!isDeletingDate.value) {
        dateToDelete.value = null;
    }
};

const dateDeletionMessage = computed(() => {
    if (!dateToDelete.value) {
        return '';
    }

    return t('deliveryCompanies.confirmDeleteDate', {
        date: formatDate(dateToDelete.value.date.delivery_date),
    });
});

const deleteDeliveryDate = async () => {
    if (!dateToDelete.value) {
        return;
    }

    isDeletingDate.value = true;

    try {
        await axios.delete(
            deliveryCompanies.dates.destroy([
                dateToDelete.value.company.id,
                dateToDelete.value.date.id,
            ]).url,
        );

        toast.success(t('deliveryCompanies.dateDeleted'));

        dateToDelete.value = null;

        router.reload({
            only: ['companies'],
        });
    } catch {
        toast.error(t('deliveryCompanies.dateDeleteFailed'));
    } finally {
        isDeletingDate.value = false;
    }
};
</script>

<template>
    <Head :title="t('deliveryCompanies.title')" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <section class="space-y-6 overflow-y-auto p-4 md:p-6">
            <header>
                <h1 class="text-2xl font-semibold">
                    {{ t('deliveryCompanies.title') }}
                </h1>

                <p class="mt-1 text-sm text-muted-foreground">
                    {{ t('deliveryCompanies.description') }}
                </p>

                <p class="mt-1 text-xs text-muted-foreground">
                    {{
                        t('deliveryCompanies.defaultAdvance', {
                            count: props.defaultMinimumAdvanceDays,
                        })
                    }}
                </p>
            </header>

            <form
                class="rounded-xl border bg-card p-5 shadow-sm"
                @submit.prevent="createCompany"
            >
                <h2 class="flex items-center gap-2 font-semibold">
                    <Plus class="h-4 w-4" />
                    {{ t('deliveryCompanies.addCompany') }}
                </h2>

                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <div>
                        <label
                            for="company-name"
                            class="mb-1.5 block text-sm font-medium"
                        >
                            {{ t('deliveryCompanies.companyName') }}
                        </label>

                        <input
                            id="company-name"
                            v-model.trim="companyForm.name"
                            type="text"
                            class="w-full rounded-lg border bg-background px-3 py-2 text-sm"
                            :placeholder="
                                t('deliveryCompanies.companyNamePlaceholder')
                            "
                        />

                        <p
                            v-if="companyErrors.name"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ companyErrors.name[0] }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="minimum-advance-days"
                            class="mb-1.5 block text-sm font-medium"
                        >
                            {{ t('deliveryCompanies.minimumAdvanceDays') }}
                        </label>

                        <input
                            id="minimum-advance-days"
                            v-model.number="companyForm.minimum_advance_days"
                            type="number"
                            min="0"
                            max="365"
                            class="w-full rounded-lg border bg-background px-3 py-2 text-sm"
                        />

                        <p
                            v-if="companyErrors.minimum_advance_days"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ companyErrors.minimum_advance_days[0] }}
                        </p>
                    </div>

                    <div class="flex items-end">
                        <label class="flex min-h-10 items-center gap-2 text-sm">
                            <input
                                v-model="companyForm.is_active"
                                type="checkbox"
                                class="h-4 w-4"
                            />

                            {{ t('deliveryCompanies.activeCompany') }}
                        </label>
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="isCreatingCompany"
                    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground disabled:opacity-50"
                >
                    <Loader2
                        v-if="isCreatingCompany"
                        class="h-4 w-4 animate-spin"
                    />

                    <Plus v-else class="h-4 w-4" />

                    {{
                        isCreatingCompany
                            ? t('deliveryCompanies.creating')
                            : t('deliveryCompanies.create')
                    }}
                </button>
            </form>

            <div
                v-if="props.companies.length === 0"
                class="rounded-xl border border-dashed p-8 text-center text-sm text-muted-foreground"
            >
                {{ t('deliveryCompanies.noCompanies') }}
            </div>

            <div v-else class="grid gap-5 lg:grid-cols-2">
                <article
                    v-for="company in props.companies"
                    :key="company.id"
                    class="rounded-xl border bg-card p-5 shadow-sm"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-primary/10 p-2">
                                <Building2 class="h-5 w-5 text-primary" />
                            </div>

                            <div>
                                <h2 class="font-semibold">
                                    {{ company.name }}
                                </h2>

                                <div
                                    class="mt-1 flex items-center gap-1 text-xs text-muted-foreground"
                                >
                                    <Clock3 class="h-3.5 w-3.5" />

                                    {{ t('deliveryCompanies.advanceNotice') }}:
                                    {{
                                        t('deliveryCompanies.days', {
                                            count: company.minimum_advance_days,
                                        })
                                    }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-medium"
                                :class="
                                    company.is_active
                                        ? 'bg-success text-success-foreground'
                                        : 'bg-secondary text-secondary-foreground'
                                "
                            >
                                {{
                                    company.is_active
                                        ? t('deliveryCompanies.active')
                                        : t('deliveryCompanies.inactive')
                                }}
                            </span>

                            <button
                                type="button"
                                class="rounded-md border p-1.5 hover:bg-muted"
                                :title="t('deliveryCompanies.editCompany')"
                                @click="startEditingCompany(company)"
                            >
                                <Pencil class="h-4 w-4" />
                            </button>

                            <button
                                type="button"
                                class="rounded-md border border-destructive/50 p-1.5 text-destructive hover:bg-destructive/10"
                                :title="t('deliveryCompanies.deleteCompany')"
                                @click="requestCompanyDeletion(company)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <form
                        v-if="editingCompanyId === company.id"
                        class="mt-4 space-y-4 rounded-lg border bg-muted/30 p-4"
                        @submit.prevent="updateCompany(company)"
                    >
                        <div>
                            <label
                                :for="`edit-company-name-${company.id}`"
                                class="mb-1.5 block text-sm font-medium"
                            >
                                {{ t('deliveryCompanies.companyName') }}
                            </label>

                            <input
                                :id="`edit-company-name-${company.id}`"
                                v-model.trim="editCompanyForm.name"
                                type="text"
                                class="w-full rounded-lg border bg-background px-3 py-2 text-sm"
                            />

                            <p
                                v-if="companyUpdateErrors.name"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ companyUpdateErrors.name[0] }}
                            </p>
                        </div>

                        <div>
                            <label
                                :for="`edit-advance-days-${company.id}`"
                                class="mb-1.5 block text-sm font-medium"
                            >
                                {{ t('deliveryCompanies.minimumAdvanceDays') }}
                            </label>

                            <input
                                :id="`edit-advance-days-${company.id}`"
                                v-model.number="
                                    editCompanyForm.minimum_advance_days
                                "
                                type="number"
                                min="0"
                                max="365"
                                class="w-full rounded-lg border bg-background px-3 py-2 text-sm"
                            />

                            <p
                                v-if="companyUpdateErrors.minimum_advance_days"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{
                                    companyUpdateErrors.minimum_advance_days[0]
                                }}
                            </p>
                        </div>

                        <label class="flex items-center gap-2 text-sm">
                            <input
                                v-model="editCompanyForm.is_active"
                                type="checkbox"
                                class="h-4 w-4"
                            />

                            {{ t('deliveryCompanies.activeCompany') }}
                        </label>

                        <div class="flex gap-2">
                            <button
                                type="submit"
                                :disabled="isUpdatingCompany"
                                class="inline-flex items-center gap-2 rounded-lg bg-primary px-3 py-2 text-sm font-medium text-primary-foreground disabled:opacity-50"
                            >
                                <Loader2
                                    v-if="isUpdatingCompany"
                                    class="h-4 w-4 animate-spin"
                                />

                                <Save v-else class="h-4 w-4" />

                                {{
                                    isUpdatingCompany
                                        ? t('deliveryCompanies.updating')
                                        : t('deliveryCompanies.save')
                                }}
                            </button>

                            <button
                                type="button"
                                :disabled="isUpdatingCompany"
                                class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium hover:bg-muted disabled:opacity-50"
                                @click="cancelEditingCompany"
                            >
                                <X class="h-4 w-4" />
                                {{ t('deliveryCompanies.cancel') }}
                            </button>
                        </div>
                    </form>

                    <div class="mt-5 border-t pt-4">
                        <h3
                            class="flex items-center gap-2 text-sm font-semibold"
                        >
                            <CalendarDays class="h-4 w-4" />
                            {{ t('deliveryCompanies.availableDates') }}
                        </h3>

                        <form
                            class="mt-3 flex flex-col gap-2 sm:flex-row"
                            @submit.prevent="addDeliveryDate(company)"
                        >
                            <div class="flex-1">
                                <label
                                    :for="`delivery-date-${company.id}`"
                                    class="sr-only"
                                >
                                    {{ t('deliveryCompanies.deliveryDate') }}
                                </label>

                                <input
                                    :id="`delivery-date-${company.id}`"
                                    v-model="deliveryDateValues[company.id]"
                                    type="date"
                                    :min="company.earliest_delivery_date"
                                    required
                                    class="w-full rounded-lg border bg-background px-3 py-2 text-sm"
                                />

                                <p
                                    v-if="deliveryDateErrors[company.id]"
                                    class="mt-1 text-xs text-destructive"
                                >
                                    {{ deliveryDateErrors[company.id] }}
                                </p>
                            </div>

                            <button
                                type="submit"
                                :disabled="companyAddingDateId === company.id"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border px-3 text-sm font-medium hover:bg-muted disabled:opacity-50"
                            >
                                <Loader2
                                    v-if="companyAddingDateId === company.id"
                                    class="h-4 w-4 animate-spin"
                                />

                                <CalendarDays v-else class="h-4 w-4" />

                                {{
                                    companyAddingDateId === company.id
                                        ? t('deliveryCompanies.addingDate')
                                        : t('deliveryCompanies.addDate')
                                }}
                            </button>
                        </form>

                        <p
                            v-if="company.dates.length === 0"
                            class="mt-3 text-sm text-muted-foreground"
                        >
                            {{ t('deliveryCompanies.noDates') }}
                        </p>

                        <ul v-else class="mt-3 space-y-2">
                            <li
                                v-for="date in company.dates"
                                :key="date.id"
                                class="flex items-center justify-between rounded-lg bg-muted/50 px-3 py-2 text-sm"
                            >
                                <span>
                                    {{ formatDate(date.delivery_date) }}
                                </span>

                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-xs"
                                        :class="
                                            date.is_available
                                                ? 'text-success'
                                                : 'text-muted-foreground'
                                        "
                                    >
                                        {{
                                            date.is_available
                                                ? t('deliveryCompanies.active')
                                                : t(
                                                      'deliveryCompanies.inactive',
                                                  )
                                        }}
                                    </span>

                                    <button
                                        type="button"
                                        :disabled="
                                            updatingDeliveryDateId === date.id
                                        "
                                        class="rounded-md border p-1.5 hover:bg-background disabled:opacity-50"
                                        :title="
                                            date.is_available
                                                ? t('deliveryCompanies.disable')
                                                : t('deliveryCompanies.enable')
                                        "
                                        @click="
                                            toggleDeliveryDate(company, date)
                                        "
                                    >
                                        <Loader2
                                            v-if="
                                                updatingDeliveryDateId ===
                                                date.id
                                            "
                                            class="h-4 w-4 animate-spin"
                                        />

                                        <Power v-else class="h-4 w-4" />
                                    </button>

                                    <button
                                        type="button"
                                        class="rounded-md border border-destructive/50 p-1.5 text-destructive hover:bg-destructive/10"
                                        :title="
                                            t('deliveryCompanies.deleteDate')
                                        "
                                        @click="
                                            requestDateDeletion(company, date)
                                        "
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </article>
            </div>
        </section>

        <ConfirmModal
            :open="dateToDelete !== null"
            :on-close="closeDateDeletion"
            :message="dateDeletionMessage"
            type="destructive"
            :is-loading="isDeletingDate"
            @confirm="deleteDeliveryDate"
        />

        <ConfirmModal
            :open="companyToDelete !== null"
            :on-close="closeCompanyDeletion"
            :message="companyDeletionMessage"
            type="destructive"
            :is-loading="isDeletingCompany"
            @confirm="deleteCompany"
        />
    </AdminLayout>
</template>

<style scoped>
.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: var(--border);
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: var(--muted-foreground);
}
</style>
