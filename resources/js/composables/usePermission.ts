import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermission() {
    const page = usePage();

    const permissions = computed(() => page.props.auth.permissions ?? []);

    const can = (permission: string) => {
        return permissions.value.includes(permission);
    }

    return {
        can
    }
}
