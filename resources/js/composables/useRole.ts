import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useRole() {
    const page = usePage();

    const roles = computed(() => page.props.auth.roles ?? []);

    const hasRole = (role: string) => {
        return roles.value.includes(role);
    };

    return {
        hasRole,
    };
}
