<?php

use App\Enums\Permissions\AdminPermissionEnum;
use App\Models\User;
use App\Services\CustomThemeService;
use App\Services\RestaurantThemeService;
use App\Services\ThemeRegistry;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $creator = User::factory()->create();

    $this->draft = app(CustomThemeService::class)->createDraft(
        'Theme to delete',
        'builtin/light',
        $creator,
    );

    $this->themePermission = Permission::findOrCreate(
        AdminPermissionEnum::THEME_MANAGE->value,
        'web',
    );
});

test('guests cannot delete a custom theme', function () {
    $this->delete(route('appearance.custom-themes.destroy', $this->draft))
        ->assertRedirect(route('login'));

    expect($this->draft->fresh())->not->toBeNull();
});

test('users without permission cannot delete a custom theme', function () {
    $this->actingAs(User::factory()->create())
        ->delete(route('appearance.custom-themes.destroy', $this->draft))
        ->assertForbidden();

    expect($this->draft->fresh())->not->toBeNull();
});

test('a theme manager can soft-delete a draft', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo($this->themePermission);

    $this->actingAs($admin)
        ->delete(route('appearance.custom-themes.destroy', $this->draft))
        ->assertRedirect(route('appearance.edit'));

    $this->assertSoftDeleted('custom_themes', ['id' => $this->draft->id]);
    expect(app(ThemeRegistry::class)->allCustomThemes())->toBeEmpty();

    $this->actingAs($admin)
        ->delete(route('appearance.custom-themes.destroy', $this->draft))
        ->assertNotFound();
});

test('deleting the published restaurant default resets it to light', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo($this->themePermission);

    app(CustomThemeService::class)->publishTheme($this->draft, $admin);
    app(RestaurantThemeService::class)
        ->setDefaultClientTheme($this->draft->key, $admin);

    $this->actingAs($admin)
        ->delete(route('appearance.custom-themes.destroy', $this->draft))
        ->assertRedirect(route('appearance.edit'));

    $this->assertSoftDeleted('custom_themes', ['id' => $this->draft->id]);
    $this->assertDatabaseHas('restaurant_theme_settings', [
        'id' => 1,
        'default_client_theme_key' => 'builtin/light',
        'updated_by' => $admin->id,
    ]);

    expect(app(ThemeRegistry::class)
        ->findPublishedTheme($this->draft->key))->toBeNull();
});
