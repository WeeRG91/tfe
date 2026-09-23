import type { ThemeDefinition, ThemeKey, ThemeSelection } from '@/types/theme';
import rawBuiltInThemes from '../../themes/built-in-themes.json';

interface ThemeResolutionOptions {
    systemPrefersDark: boolean;
    restaurantDefault?: ThemeKey;
    customThemes?: readonly ThemeDefinition[];
}

export const builtInThemes =
    rawBuiltInThemes as unknown as readonly ThemeDefinition[];

const builtInThemeMap = new Map<ThemeKey, ThemeDefinition>(
    builtInThemes.map((theme) => [theme.key, theme]),
);

function requireTheme(key: ThemeKey): ThemeDefinition {
    const theme = builtInThemeMap.get(key);

    if (!theme) {
        throw new Error(`Required built-in theme "${key}" was not found.`);
    }

    return theme;
}

const fallbackTheme = requireTheme('builtin/light');

export function getTheme(
    key: ThemeKey,
    customThemes: readonly ThemeDefinition[] = [],
): ThemeDefinition | undefined {
    return (
        customThemes.find((theme) => theme.key === key) ??
        builtInThemeMap.get(key)
    );
}

export function getAvailableThemes(
    customThemes: readonly ThemeDefinition[] = [],
): readonly ThemeDefinition[] {
    return [...builtInThemes, ...customThemes];
}

export function resolveThemeSelection(
    selection: ThemeSelection,
    options: ThemeResolutionOptions,
): ThemeDefinition {
    const {
        systemPrefersDark,
        restaurantDefault = 'builtin/light',
        customThemes = [],
    } = options;

    let resolvedKey: ThemeKey;

    if (selection === 'system') {
        resolvedKey = systemPrefersDark ? 'builtin/dark' : 'builtin/light';
    } else if (selection === 'restaurant-default') {
        resolvedKey = restaurantDefault;
    } else {
        resolvedKey = selection;
    }

    return getTheme(resolvedKey, customThemes) ?? fallbackTheme;
}

export function isThemeKey(value: string): value is ThemeKey {
    if (builtInThemeMap.has(value as ThemeKey)) {
        return true;
    }

    return /^custom\/[a-zA-Z0-9-]+$/.test(value);
}

export function isThemeSelection(value: unknown): value is ThemeSelection {
    return (
        value === 'system' ||
        value === 'restaurant-default' ||
        (typeof value === 'string' && isThemeKey(value))
    );
}
