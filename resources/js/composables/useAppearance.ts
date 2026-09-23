import { applyTheme } from '@/themes/applyTheme';
import {
    getStoredThemeSelection,
    storeThemeSelection,
} from '@/themes/preferences';
import { resolveThemeSelection } from '@/themes/registry';
import { AppPageProps } from '@/types';
import type {
    ThemeDefinition,
    ThemeKey,
    ThemeSelection,
    ThemeSurface,
} from '@/types/theme';
import { usePage } from '@inertiajs/vue3';
import { onMounted, ref, type Ref, watch } from 'vue';

const restaurantDefaultKeys: Record<ThemeSurface, ThemeKey> = {
    admin: 'builtin/light',
    client: 'builtin/light',
};

const selections: Record<ThemeSurface, Ref<ThemeSelection>> = {
    admin: ref<ThemeSelection>('system'),
    client: ref<ThemeSelection>('restaurant-default'),
};

let activeSurface: ThemeSurface = 'client';
let customThemes: readonly ThemeDefinition[] = [];
let systemListenerRegistered = false;

function mediaQuery(): MediaQueryList | null {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
}

function systemPrefersDark(): boolean {
    return mediaQuery()?.matches ?? false;
}

function applySelection(
    selection: ThemeSelection,
    surface: ThemeSurface,
): void {
    const theme = resolveThemeSelection(selection, {
        systemPrefersDark: systemPrefersDark(),
        restaurantDefault: restaurantDefaultKeys[surface],
        customThemes,
    });

    applyTheme(theme);
}

export function detectThemeSurface(): ThemeSurface {
    if (typeof window === 'undefined') {
        return 'client';
    }

    const path = window.location.pathname;

    return path.startsWith('/admin') || path.startsWith('/settings')
        ? 'admin'
        : 'client';
}

function ensureAvailableSelection(
    selection: ThemeSelection,
    surface: ThemeSurface,
    availableCustomThemes: readonly ThemeDefinition[],
): ThemeSelection {
    if (
        !selection.startsWith('custom/') ||
        availableCustomThemes.some((theme) => theme.key === selection)
    ) {
        return selection;
    }

    const fallback: ThemeSelection =
        surface === 'admin' ? 'system' : 'restaurant-default';

    storeThemeSelection(surface, fallback);
    return fallback;
}

export function activateThemeSurface(
    surface: ThemeSurface,
    restaurantDefaultKey: ThemeKey = 'builtin/light',
    availableCustomThemes: readonly ThemeDefinition[] = [],
): void {
    activeSurface = surface;
    restaurantDefaultKeys[surface] = restaurantDefaultKey;
    customThemes = availableCustomThemes;

    const storedSelection = ensureAvailableSelection(
        getStoredThemeSelection(surface),
        surface,
        availableCustomThemes,
    );

    selections[surface].value = storedSelection;
    applySelection(storedSelection, surface);
}

function handleSystemThemeChange(): void {
    const selection = selections[activeSurface].value;

    if (selection === 'system') {
        applySelection(selection, activeSurface);
    }
}

export function initializeTheme(
    surface: ThemeSurface = detectThemeSurface(),
    restaurantDefaultKey: ThemeKey = 'builtin/light',
    availableCustomThemes: readonly ThemeDefinition[] = [],
): void {
    if (typeof window === 'undefined') {
        return;
    }

    activateThemeSurface(surface, restaurantDefaultKey, availableCustomThemes);

    if (!systemListenerRegistered) {
        mediaQuery()?.addEventListener('change', handleSystemThemeChange);
        systemListenerRegistered = true;
    }
}

export function useAppearance(surface: ThemeSurface = 'admin') {
    const page = usePage<AppPageProps>();
    const appearance = selections[surface];

    watch(
        [
            () => page.props.theme.restaurantDefaultKey,
            () => page.props.theme.customThemes,
        ],
        ([restaurantDefaultKey, availableCustomThemes]) => {
            restaurantDefaultKeys[surface] = restaurantDefaultKey;
            customThemes = availableCustomThemes;

            if (activeSurface === surface) {
                const safeSelection = ensureAvailableSelection(
                    appearance.value,
                    surface,
                    availableCustomThemes,
                );

                appearance.value = safeSelection;
                applySelection(safeSelection, surface);
            }
        },
        { immediate: true },
    );

    onMounted(() => {
        activateThemeSurface(
            surface,
            page.props.theme.restaurantDefaultKey,
            page.props.theme.customThemes,
        );
    });

    function updateAppearance(selection: ThemeSelection): void {
        activeSurface = surface;
        appearance.value = selection;

        storeThemeSelection(surface, selection);
        applySelection(selection, surface);
    }

    return {
        appearance,
        updateAppearance,
    };
}
