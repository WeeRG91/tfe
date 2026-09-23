import { isThemeSelection } from '@/themes/registry';
import type { ThemeSelection, ThemeSurface } from '@/types/theme';

const storageKeys: Record<ThemeSurface, string> = {
    admin: 'admin-theme',
    client: 'client-theme',
};

const cookieKeys: Record<ThemeSurface, string> = {
    admin: 'admin_theme',
    client: 'client_theme',
};

function normalizeSelection(value: unknown): ThemeSelection | null {
    if (value === 'light') {
        return 'builtin/light';
    }

    if (value === 'dark') {
        return 'builtin/dark';
    }

    return isThemeSelection(value) ? value : null;
}

function defaultSelectionForSurface(surface: ThemeSurface): ThemeSelection {
    return surface === 'admin' ? 'system' : 'restaurant-default';
}

export function getStoredThemeSelection(surface: ThemeSurface): ThemeSelection {
    const defaultSelection = defaultSelectionForSurface(surface);

    if (typeof window === 'undefined') {
        return defaultSelection;
    }

    const storedSelection = normalizeSelection(
        localStorage.getItem(storageKeys[surface]),
    );

    if (storedSelection) {
        return storedSelection;
    }

    if (surface === 'admin') {
        const legacySelection = normalizeSelection(
            localStorage.getItem('appearance'),
        );

        return legacySelection ?? defaultSelection;
    }

    return defaultSelection;
}

export function storeThemeSelection(
    surface: ThemeSurface,
    selection: ThemeSelection,
): void {
    if (typeof window === 'undefined') {
        return;
    }

    localStorage.setItem(storageKeys[surface], selection);

    const maxAge = 365 * 24 * 60 * 60;

    document.cookie = `${cookieKeys[surface]}=${encodeURIComponent(selection)};path=/;max-age=${maxAge};SameSite=Lax`;
}
