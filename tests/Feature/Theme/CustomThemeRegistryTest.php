<?php

use App\Models\CustomTheme;
use App\Services\ThemeRegistry;
use Inertia\Testing\AssertableInertia as Assert;

test('only published custom themes are available to clients', function () {
    $registry = app(ThemeRegistry::class);
    $colors = $registry->fallbackTheme()['colors'];

    foreach ([
        ['key' => 'custom/published', 'name' => 'Published', 'published' => true],
        ['key' => 'custom/draft', 'name' => 'Draft', 'published' => false],
    ] as $data) {
        CustomTheme::query()->create([
            ...$data,
            'mode' => 'light',
            'colors' => $colors,
            'radius' => '0.5rem',
        ]);
    }

    $publicKeys = array_column($registry->allPublishedThemes(), 'key');
    $adminKeys = array_column($registry->allCustomThemes(), 'key');

    expect($publicKeys)->toContain('custom/published');
    expect($publicKeys)->not->toContain('custom/draft');

    expect($adminKeys)->toContain('custom/published', 'custom/draft');

    expect($registry->findPublishedTheme('custom/published'))
        ->toMatchArray([
            'key' => 'custom/published',
            'source' => 'custom',
            'published' => true,
            'editable' => true,
        ]);

    expect($registry->findPublishedTheme('custom/draft'))->toBeNull();
});

test('Inertia shares published custom themes but not drafts', function () {
    $colors = app(ThemeRegistry::class)->fallbackTheme()['colors'];

    foreach ([
        ['key' => 'custom/published', 'name' => 'Published', 'published' => true],
        ['key' => 'custom/draft', 'name' => 'Draft', 'published' => false],
    ] as $data) {
        CustomTheme::query()->create([
            ...$data,
            'mode' => 'light',
            'colors' => $colors,
            'radius' => '0.5rem',
        ]);
    }

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('client/Home')
            ->has('theme.customThemes', 1)
            ->where('theme.customThemes.0.key', 'custom/published')
            ->etc()
        );
});
