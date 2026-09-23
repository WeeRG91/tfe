import { computed, Ref } from 'vue';

export function usePasswordStrength(password: Ref<string>) {
    const passwordChecks = computed(() => ({
        length: password.value.length >= 8,
        uppercase: /[A-Z]/.test(password.value),
        lowercase: /[a-z]/.test(password.value),
        number: /[0-9]/.test(password.value),
        symbol: /[!@#$%^&*(),.?":{}|<>]/.test(password.value),
    }));

    const passwordStrength = computed(
        () => Object.values(passwordChecks.value).filter(Boolean).length,
    );

    const getPasswordStrengthColor = (index: number) => {
        if (index < passwordStrength.value) {
            if (passwordStrength.value <= 2) return 'bg-destructive';
            if (passwordStrength.value === 3) return 'bg-warning';

            return 'bg-success';
        }

        return 'bg-muted';
    };

    return {
        passwordChecks,
        passwordStrength,
        getPasswordStrengthColor,
    };
}
