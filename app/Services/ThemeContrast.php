<?php

namespace App\Services;

class ThemeContrast
{
    public const TEXT_PAIRS = [
        ['background', 'foreground'],
        ['card', 'cardForeground'],
        ['popover', 'popoverForeground'],
        ['primary', 'primaryForeground'],
        ['secondary', 'secondaryForeground'],
        ['muted', 'mutedForeground'],
        ['accent', 'accentForeground'],
        ['destructive', 'destructiveForeground'],
        ['success', 'successForeground'],
        ['warning', 'warningForeground'],
        ['info', 'infoForeground'],
        ['sidebarBackground', 'sidebarForeground'],
        ['sidebarPrimary', 'sidebarPrimaryForeground'],
        ['sidebarAccent', 'sidebarAccentForeground'],
    ];

    public static function ratio(
        string $first,
        string $second,
    ): float
    {
        $a = self::luminance($first);
        $b = self::luminance($second);

        return (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
    }

    private static function luminance(string $hex): float
    {
        $channels = [
            hexdec(substr($hex, 1, 2)) / 255,
            hexdec(substr($hex, 3, 2)) / 255,
            hexdec(substr($hex, 5, 2)) / 255,
        ];

        $linear = array_map(
            static fn (float $channel): float => $channel <= 0.04045
                ? $channel / 12.92
                : (($channel + 0.055) / 1.055) ** 2.4,
            $channels,
        );

        return
            0.2126 * $linear[0]
            + 0.7152 * $linear[1]
            + 0.0722 * $linear[2];
    }
}
