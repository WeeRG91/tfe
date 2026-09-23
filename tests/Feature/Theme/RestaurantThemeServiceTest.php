<?php

use App\Models\RestaurantThemeSetting;
use App\Models\User;
use App\Services\CustomThemeService;
use App\Services\RestaurantThemeService;
use Illuminate\Validation\ValidationException;

test('the client theme defaults to light when no setting exists', function () {
    $service = app(RestaurantThemeService::class);

    expect($service->defaultClientThemeKey())
        ->toBe('builtin/light')
        ->and($service->defaultClientTheme()['key'])
        ->toBe('builtin/light');

    $this->assertDatabaseCount('restaurant_theme_settings', 0);
});

test('an administrator can set the default client theme', function () {
    $admin = User::factory()->create();
    $service = app(RestaurantThemeService::class);

    $setting = $service->setDefaultClientTheme(
        'builtin/cupcake',
        $admin,
    );

    expect($setting->default_client_theme_key)
        ->toBe('builtin/cupcake')
        ->and($setting->updated_by)
        ->toBe($admin->getKey())
        ->and($service->defaultClientThemeKey())
        ->toBe('builtin/cupcake');

    $this->assertDatabaseHas('restaurant_theme_settings', [
        'id' => 1,
        'default_client_theme_key' => 'builtin/cupcake',
        'updated_by' => $admin->getKey(),
    ]);
});

test('updating the default reuses the singleton setting', function () {
    $firstAdmin = User::factory()->create();
    $secondAdmin = User::factory()->create();
    $service = app(RestaurantThemeService::class);

    $service->setDefaultClientTheme('builtin/cupcake', $firstAdmin);
    $service->setDefaultClientTheme('builtin/dark', $secondAdmin);

    $this->assertDatabaseCount('restaurant_theme_settings', 1);

    $this->assertDatabaseHas('restaurant_theme_settings', [
        'id' => 1,
        'default_client_theme_key' => 'builtin/dark',
        'updated_by' => $secondAdmin->getKey(),
    ]);
});

test('an unavailable theme cannot become the default', function () {
    $admin = User::factory()->create();
    $service = app(RestaurantThemeService::class);

    expect(fn () => $service->setDefaultClientTheme(
        'builtin/not-real',
        $admin,
    ))->toThrow(ValidationException::class);

    $this->assertDatabaseCount('restaurant_theme_settings', 0);
});

test('an invalid stored theme safely falls back to light', function () {
    RestaurantThemeSetting::query()->create([
        'default_client_theme_key' => 'builtin/missing',
    ]);

    $service = app(RestaurantThemeService::class);

    expect($service->defaultClientThemeKey())
        ->toBe('builtin/light');
});

test('a published custom theme can become the client default', function () {
    $admin = User::factory()->create();
    $customThemes = app(CustomThemeService::class);
    $restaurantThemes = app(RestaurantThemeService::class);

    $draft = $customThemes->createDraft(
        'Restaurant Blue',
        'builtin/light',
        $admin,
    );
    $published = $customThemes->publishTheme($draft, $admin);

    $restaurantThemes->setDefaultClientTheme($published->key, $admin);

    expect($restaurantThemes->defaultClientThemeKey())
        ->toBe($published->key);
    expect($restaurantThemes->defaultClientTheme()['colors'])
        ->toBe($published->colors);
});

test('a draft custom theme cannot become the client default', function () {
    $admin = User::factory()->create();
    $draft = app(CustomThemeService::class)->createDraft(
        'Unpublished',
        'builtin/light',
        $admin,
    );

    expect(fn () => app(RestaurantThemeService::class)
        ->setDefaultClientTheme($draft->key, $admin))
        ->toThrow(ValidationException::class);

    $this->assertDatabaseCount('restaurant_theme_settings', 0);
});

test('an unavailable custom default safely falls back to light', function () {
    $admin = User::factory()->create();
    $customThemes = app(CustomThemeService::class);
    $restaurantThemes = app(RestaurantThemeService::class);

    $draft = $customThemes->createDraft(
        'Restaurant Blue',
        'builtin/light',
        $admin,
    );
    $published = $customThemes->publishTheme($draft, $admin);

    $restaurantThemes->setDefaultClientTheme($published->key, $admin);

    // Simulate a theme becoming unavailable after it was selected.
    $published->update(['published' => false]);

    expect($restaurantThemes->defaultClientThemeKey())
        ->toBe('builtin/light');
});
