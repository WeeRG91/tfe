<?php

use App\Http\Middleware\HandleAppearance;
use App\Models\RestaurantThemeSetting;
use App\Models\User;
use App\Services\CustomThemeService;
use App\Services\RestaurantThemeService;
use Illuminate\Http\Request;

function resolveThemeContext(
    string $uri,
    array $cookies = [],
): array {
    $request = Request::create(
        $uri,
        'GET',
        [],
        $cookies,
    );

    $response = app(HandleAppearance::class)->handle(
        $request,
        fn (Request $request) => response()->json(
            $request->attributes->get('themeContext'),
        ),
    );

    return json_decode(
        $response->getContent(),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );
}

test('a new client follows the restaurant default', function () {
    RestaurantThemeSetting::query()->create([
        'default_client_theme_key' => 'builtin/cupcake',
    ]);

    $context = resolveThemeContext('/');

    expect($context['surface'])
        ->toBe('client')
        ->and($context['selection'])
        ->toBe('restaurant-default')
        ->and($context['restaurantDefaultKey'])
        ->toBe('builtin/cupcake')
        ->and($context['initialTheme']['key'])
        ->toBe('builtin/cupcake');
});

test('an explicit client selection overrides the restaurant default', function () {
    RestaurantThemeSetting::query()->create([
        'default_client_theme_key' => 'builtin/cupcake',
    ]);

    $context = resolveThemeContext('/', [
        'client_theme' => 'builtin/dark',
    ]);

    expect($context['selection'])
        ->toBe('builtin/dark')
        ->and($context['initialTheme']['key'])
        ->toBe('builtin/dark');
});

test('the admin surface uses its independent preference', function () {
    $context = resolveThemeContext('/admin/dashboard', [
        'admin_theme' => 'builtin/cupcake',
        'client_theme' => 'builtin/dark',
    ]);

    expect($context['surface'])
        ->toBe('admin')
        ->and($context['selection'])
        ->toBe('builtin/cupcake')
        ->and($context['initialTheme']['key'])
        ->toBe('builtin/cupcake');
});

test('an unavailable client theme falls back to the restaurant default', function () {
    RestaurantThemeSetting::query()->create([
        'default_client_theme_key' => 'builtin/cupcake',
    ]);

    $context = resolveThemeContext('/', [
        'client_theme' => 'custom/missing',
    ]);

    expect($context['selection'])
        ->toBe('restaurant-default')
        ->and($context['initialTheme']['key'])
        ->toBe('builtin/cupcake');
});

test('system selection provides both light and dark themes', function () {
    $context = resolveThemeContext('/admin/dashboard', [
        'admin_theme' => 'system',
    ]);

    expect($context['selection'])
        ->toBe('system')
        ->and($context['initialTheme']['key'])
        ->toBe('builtin/light')
        ->and($context['systemLightTheme']['key'])
        ->toBe('builtin/light')
        ->and($context['systemDarkTheme']['key'])
        ->toBe('builtin/dark');
});

test('the legacy appearance cookie does not override the client default', function () {
    RestaurantThemeSetting::query()->create([
        'default_client_theme_key' => 'builtin/cupcake',
    ]);

    $context = resolveThemeContext('/', [
        'appearance' => 'builtin/dark',
    ]);

    expect($context['selection'])
        ->toBe('restaurant-default')
        ->and($context['restaurantDefaultKey'])
        ->toBe('builtin/cupcake')
        ->and($context['initialTheme']['key'])
        ->toBe('builtin/cupcake');
});

test('a published custom theme can be selected by a client', function () {
    $admin = User::factory()->create();
    $service = app(CustomThemeService::class);

    $draft = $service->createDraft('Restaurant Blue', 'builtin/light', $admin);
    $published = $service->publishTheme($draft, $admin);

    $context = resolveThemeContext('/', [
        'client_theme' => $published->key,
    ]);

    expect($context['selection'])->toBe($published->key);
    expect($context['initialTheme']['key'])->toBe($published->key);
});

test('a client follows a custom restaurant default', function () {
    $admin = User::factory()->create();
    $service = app(CustomThemeService::class);

    $draft = $service->createDraft('Restaurant Blue', 'builtin/light', $admin);
    $published = $service->publishTheme($draft, $admin);

    app(RestaurantThemeService::class)
        ->setDefaultClientTheme($published->key, $admin);

    $context = resolveThemeContext('/');

    expect($context['selection'])->toBe('restaurant-default');
    expect($context['restaurantDefaultKey'])->toBe($published->key);
    expect($context['initialTheme']['key'])->toBe($published->key);
});

test('a draft custom theme cookie is rejected', function () {
    $admin = User::factory()->create();
    $draft = app(CustomThemeService::class)
        ->createDraft('Unpublished', 'builtin/light', $admin);

    RestaurantThemeSetting::query()->create([
        'default_client_theme_key' => 'builtin/cupcake',
    ]);

    $context = resolveThemeContext('/', [
        'client_theme' => $draft->key,
    ]);

    expect($context['selection'])->toBe('restaurant-default');
    expect($context['initialTheme']['key'])->toBe('builtin/cupcake');
});
