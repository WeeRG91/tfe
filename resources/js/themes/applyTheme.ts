import type { ThemeColors, ThemeDefinition } from '@/types/theme';

const cssVariableMap: Record<keyof ThemeColors, string> = {
    background: '--background',
    foreground: '--foreground',

    card: '--card',
    cardForeground: '--card-foreground',

    popover: '--popover',
    popoverForeground: '--popover-foreground',

    primary: '--primary',
    primaryForeground: '--primary-foreground',

    secondary: '--secondary',
    secondaryForeground: '--secondary-foreground',

    muted: '--muted',
    mutedForeground: '--muted-foreground',

    accent: '--accent',
    accentForeground: '--accent-foreground',

    destructive: '--destructive',
    destructiveForeground: '--destructive-foreground',

    success: '--success',
    successForeground: '--success-foreground',

    warning: '--warning',
    warningForeground: '--warning-foreground',

    info: '--info',
    infoForeground: '--info-foreground',

    border: '--border',
    input: '--input',
    ring: '--ring',

    chart1: '--chart-1',
    chart2: '--chart-2',
    chart3: '--chart-3',
    chart4: '--chart-4',
    chart5: '--chart-5',

    sidebarBackground: '--sidebar-background',
    sidebarForeground: '--sidebar-foreground',
    sidebarPrimary: '--sidebar-primary',
    sidebarPrimaryForeground: '--sidebar-primary-foreground',
    sidebarAccent: '--sidebar-accent',
    sidebarAccentForeground: '--sidebar-accent-foreground',
    sidebarBorder: '--sidebar-border',
    sidebarRing: '--sidebar-ring',
};

export function applyTheme(theme: ThemeDefinition): void {
    if (typeof document === 'undefined') {
        return;
    }

    const root = document.documentElement;

    root.dataset.theme = theme.key;
    root.dataset.themeMode = theme.mode;

    root.classList.toggle('dark', theme.mode === 'dark');
    root.style.colorScheme = theme.mode;
    root.style.setProperty('--radius', theme.radius);

    for (const [colorKey, cssVariable] of Object.entries(cssVariableMap) as [
        keyof ThemeColors,
        string,
    ][]) {
        root.style.setProperty(cssVariable, theme.colors[colorKey]);
    }
}
