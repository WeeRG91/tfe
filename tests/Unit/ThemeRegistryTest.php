<?php

use App\Services\ThemeRegistry;

test('it loads the shared built-in themes', function () {
    $path = dirname(__DIR__, 2)
        .'/resources/themes/built-in-themes.json';

    $registry = new ThemeRegistry($path);
    $themes = $registry->allBuiltInThemes();
    $cupcake = $registry->findBuiltInTheme('builtin/cupcake');

    expect($themes)
        ->toHaveCount(15)
        ->and(array_column($themes, 'key'))
        ->toBe([
            'builtin/light',
            'builtin/dark',
            'builtin/cupcake',
            'builtin/emerald',
            'builtin/retro',
            'builtin/cyberpunk',
            'builtin/valentine',
            'builtin/garden',
            'builtin/pastel',
            'builtin/synthwave',
            'builtin/forest',
            'builtin/aqua',
            'builtin/luxury',
            'builtin/coffee',
            'builtin/sunset',
        ])
        ->and($cupcake)
        ->not->toBeNull()
        ->and($cupcake['colors'])
        ->toHaveCount(38)
        ->and($registry->fallbackTheme()['key'])
        ->toBe('builtin/light');
});

test('it rejects duplicate theme keys', function () {
    $path = tempnam(sys_get_temp_dir(), 'themes-');

    expect($path)->not->toBeFalse();

    file_put_contents($path, json_encode([
        ['key' => 'builtin/light'],
        ['key' => 'builtin/light'],
    ], JSON_THROW_ON_ERROR));

    try {
        $registry = new ThemeRegistry($path);

        expect(fn () => $registry->allBuiltInThemes())
            ->toThrow(
                UnexpectedValueException::class,
                'Duplicate built-in theme key [builtin/light].',
            );
    } finally {
        unlink($path);
    }
});

test('it rejects malformed JSON', function () {
    $path = tempnam(sys_get_temp_dir(), 'themes-');

    expect($path)->not->toBeFalse();

    file_put_contents($path, '{"invalid JSON}');

    try {
        $registry = new ThemeRegistry($path);

        expect(fn () => $registry->allBuiltInThemes())
            ->toThrow(JsonException::class);
    } finally {
        unlink($path);
    }
});

test('it requires the light fallback theme', function () {
    $path = tempnam(sys_get_temp_dir(), 'themes-');

    expect($path)->not->toBeFalse();

    file_put_contents($path, json_encode([
        ['key' => 'builtin/dark'],
    ], JSON_THROW_ON_ERROR));

    try {
        $registry = new ThemeRegistry($path);

        expect(fn () => $registry->fallbackTheme())
            ->toThrow(
                RuntimeException::class,
                'The required built-in light theme is missing.',
            );
    } finally {
        unlink($path);
    }
});
