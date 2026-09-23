export type ThemeMode = 'light' | 'dark';

export type ThemeSource = 'builtin' | 'custom';

export type ThemeSurface = 'admin' | 'client';

export interface ThemeColors {
    background: string;
    foreground: string;

    card: string;
    cardForeground: string;

    popover: string;
    popoverForeground: string;

    primary: string;
    primaryForeground: string;

    secondary: string;
    secondaryForeground: string;

    muted: string;
    mutedForeground: string;

    accent: string;
    accentForeground: string;

    destructive: string;
    destructiveForeground: string;

    success: string;
    successForeground: string;

    warning: string;
    warningForeground: string;

    info: string;
    infoForeground: string;

    border: string;
    input: string;
    ring: string;

    chart1: string;
    chart2: string;
    chart3: string;
    chart4: string;
    chart5: string;

    sidebarBackground: string;
    sidebarForeground: string;
    sidebarPrimary: string;
    sidebarPrimaryForeground: string;
    sidebarAccent: string;
    sidebarAccentForeground: string;
    sidebarBorder: string;
    sidebarRing: string;
}

export interface ThemeDefinition {
    key: ThemeKey;
    name: string;
    source: ThemeSource;
    mode: ThemeMode;
    colors: ThemeColors;
    radius: string;
    published: boolean;
    editable: boolean;
}

export type BuiltInThemeKey =
    | 'builtin/light'
    | 'builtin/dark'
    | 'builtin/cupcake'
    | 'builtin/emerald'
    | 'builtin/retro'
    | 'builtin/cyberpunk'
    | 'builtin/valentine'
    | 'builtin/garden'
    | 'builtin/pastel'
    | 'builtin/synthwave'
    | 'builtin/forest'
    | 'builtin/aqua'
    | 'builtin/luxury'
    | 'builtin/coffee'
    | 'builtin/sunset';

export type CustomThemeKey = `custom/${string}`;

export type ThemeKey = BuiltInThemeKey | CustomThemeKey;

export type ThemeSelection = 'system' | 'restaurant-default' | ThemeKey;

export interface ManagedCustomTheme extends ThemeDefinition {
    id: number;
}
