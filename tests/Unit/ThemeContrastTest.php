<?php

use App\Services\ThemeContrast;
use App\Services\ThemeRegistry;

test('the contrast calculator handles black and white', function () {
    expect(ThemeContrast::ratio('#000000', '#ffffff'))->toBe(21.0);
    expect(ThemeContrast::ratio('#123456', '#123456'))->toBe(1.0);
});

test('built-in text color pairs reach 4.5 to 1 contrast', function () {
    $path = dirname(__DIR__, 2)
        .'/resources/themes/built-in-themes.json';

    $registry = new ThemeRegistry($path);

    foreach ($registry->allBuiltInThemes() as $theme) {
        foreach (ThemeContrast::TEXT_PAIRS as [$background, $foreground]) {
            $ratio = ThemeContrast::ratio(
                $theme['colors'][$background],
                $theme['colors'][$foreground],
            );

            $this->assertGreaterThanOrEqual(
                4.5,
                $ratio,
                "{$theme['key']}: {$foreground} on {$background}",
            );
        }
    }
});
