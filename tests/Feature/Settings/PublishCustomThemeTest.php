<?php

use App\Enums\Permissions\AdminPermissionEnum;
use App\Models\User;
use App\Services\CustomThemeService;
use App\Services\ThemeRegistry;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $creator = User::factory()->create();

    $this->draft = app(CustomThemeService::class)->createDraft(
        'Restaurant Theme',
        'builtin/light',
        $creator,
    );
});

test('guests cannot publish a custom theme', function () {
    $this->post(route('appearance.custom-themes.publish', $this->draft))
        ->assertRedirect(route('login'));

    expect($this->draft->fresh()->published)->toBeFalse();
});

test('users without permission cannot publish a custom theme', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('appearance.custom-themes.publish', $this->draft))
        ->assertForbidden();

    expect($this->draft->fresh()->published)->toBeFalse();
});

test('a theme manager can publish a readable draft', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo(Permission::findOrCreate(
        AdminPermissionEnum::THEME_MANAGE->value,
        'web',
    ));

    $this->actingAs($admin)
        ->post(route('appearance.custom-themes.publish', $this->draft))
        ->assertRedirect(route('appearance.custom-themes.edit', $this->draft));

    expect($this->draft->fresh()->published)->toBeTrue();
    expect(app(ThemeRegistry::class)->findPublishedTheme($this->draft->key))
        ->not->toBeNull();
});

test('an unreadable draft cannot be published', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo(Permission::findOrCreate(
        AdminPermissionEnum::THEME_MANAGE->value,
        'web',
    ));

    $colors = $this->draft->colors;
    $colors['primaryForeground'] = $colors['primary'];

    app(CustomThemeService::class)->updateTheme(
        $this->draft,
        ['name' => 'Unreadable Theme', 'colors' => $colors],
        $admin,
    );

    $this->actingAs($admin)
        ->from(route('appearance.custom-themes.edit', $this->draft))
        ->post(route('appearance.custom-themes.publish', $this->draft))
        ->assertRedirect(route('appearance.custom-themes.edit', $this->draft))
        ->assertSessionHasErrors('colors.primaryForeground');

    expect($this->draft->fresh()->published)->toBeFalse();
});
