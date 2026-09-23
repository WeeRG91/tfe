<?php

use App\Models\User;
use App\Services\CustomThemeService;
use App\Services\ThemeRegistry;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

test('an admin can create an unpublished copy of a built-in theme', function () {
    $admin = User::factory()->create();
    $registry = app(ThemeRegistry::class);

    $draft = app(CustomThemeService::class)->createDraft(
        '  Our Restaurant  ',
        'builtin/cupcake',
        $admin,
    );

    expect($draft->name)->toBe('Our Restaurant');
    expect(str_starts_with($draft->key, 'custom/'))->toBeTrue();
    expect(Str::isUuid(substr($draft->key, 7)))->toBeTrue();
    expect($draft->mode)->toBe('light');
    expect($draft->colors)
        ->toBe($registry->findBuiltInTheme('builtin/cupcake')['colors']);
    expect($draft->published)->toBeFalse();
    expect($draft->created_by)->toBe($admin->id);
    expect($draft->updated_by)->toBe($admin->id);
    expect($registry->findPublishedTheme($draft->key))->toBeNull();
});

test('an invalid base theme cannot create a draft', function () {
    $admin = User::factory()->create();

    expect(fn () => app(CustomThemeService::class)->createDraft(
        'My Theme',
        'builtin/does-not-exist',
        $admin,
    ))->toThrow(ValidationException::class);

    $this->assertDatabaseCount('custom_themes', 0);
});

test('editing a theme keeps its stable key and updates its palette', function () {
    $creator = User::factory()->create();
    $editor = User::factory()->create();
    $service = app(CustomThemeService::class);

    $theme = $service->createDraft(
        'Original',
        'builtin/cupcake',
        $creator,
    );

    $originalKey = $theme->key;
    $colors = $theme->colors;
    $colors['primary'] = '#123456';

    $updated = $service->updateTheme(
        $theme,
        ['name' => '  Restaurant Blue  ', 'colors' => $colors],
        $editor,
    );

    expect($updated->key)->toBe($originalKey);
    expect($updated->name)->toBe('Restaurant Blue');
    expect($updated->colors['primary'])->toBe('#123456');
    expect($updated->colors)->toHaveCount(38);
    expect($updated->created_by)->toBe($creator->id);
    expect($updated->updated_by)->toBe($editor->id);
});

test('an incomplete or invalid palette cannot be saved', function () {
    $admin = User::factory()->create();
    $service = app(CustomThemeService::class);
    $theme = $service->createDraft('Original', 'builtin/light', $admin);

    $incomplete = $theme->colors;
    unset($incomplete['accent']);

    expect(fn () => $service->updateTheme(
        $theme,
        ['name' => 'Invalid', 'colors' => $incomplete],
        $admin,
    ))->toThrow(ValidationException::class);

    $unsafe = $theme->colors;
    $unsafe['primary'] = 'red; background: blue';

    expect(fn () => $service->updateTheme(
        $theme,
        ['name' => 'Invalid', 'colors' => $unsafe],
        $admin,
    ))->toThrow(ValidationException::class);

    expect($theme->fresh()->name)->toBe('Original');
});

test('a readable draft can be published', function () {
    $admin = User::factory()->create();
    $service = app(CustomThemeService::class);
    $registry = app(ThemeRegistry::class);

    $draft = $service->createDraft('Restaurant', 'builtin/light', $admin);
    $published = $service->publishTheme($draft, $admin);

    expect($published->published)->toBeTrue();
    expect($registry->findPublishedTheme($published->key)['key'])
        ->toBe($published->key);
});

test('an unreadable draft cannot be published', function () {
    $admin = User::factory()->create();
    $service = app(CustomThemeService::class);
    $draft = $service->createDraft('Restaurant', 'builtin/light', $admin);

    $colors = $draft->colors;
    $colors['primaryForeground'] = $colors['primary'];

    $draft = $service->updateTheme(
        $draft,
        ['name' => 'Restaurant', 'colors' => $colors],
        $admin,
    );

    expect(fn () => $service->publishTheme($draft, $admin))
        ->toThrow(ValidationException::class);

    expect($draft->fresh()->published)->toBeFalse();
});

test('editing a published theme cannot reduce its contrast', function () {
    $admin = User::factory()->create();
    $service = app(CustomThemeService::class);
    $theme = $service->createDraft('Restaurant', 'builtin/light', $admin);
    $theme = $service->publishTheme($theme, $admin);

    $originalColors = $theme->colors;
    $colors = $originalColors;
    $colors['primaryForeground'] = $colors['primary'];

    expect(fn () => $service->updateTheme(
        $theme,
        ['name' => 'Unreadable', 'colors' => $colors],
        $admin,
    ))->toThrow(ValidationException::class);

    expect($theme->fresh()->colors)->toBe($originalColors);
    expect($theme->fresh()->name)->toBe('Restaurant');
});
