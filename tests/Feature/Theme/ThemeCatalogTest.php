<?php

use App\Models\CustomTheme;
use App\Models\User;
use App\Services\RestaurantThemeService;
use App\Services\ThemeRegistry;

test('guests receive the restaurant default and only published themes', function () {
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

    $admin = User::factory()->create();

    app(RestaurantThemeService::class)
        ->setDefaultClientTheme('builtin/cupcake', $admin);

    $response = $this->getJson(route('api.v1.restaurant.themes'));

    $response
        ->assertOk()
        ->assertJsonPath('data.default_theme_key', 'builtin/cupcake');

    $keys = array_column($response->json('data.themes'), 'key');

    expect($keys)
        ->toContain('builtin/light', 'custom/published')
        ->not->toContain('custom/draft');
});
