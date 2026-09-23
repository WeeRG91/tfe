<?php

use App\Enums\Permissions\AdminPermissionEnum;
use App\Models\CustomTheme;
use App\Models\User;
use App\Services\ThemeRegistry;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->themePermission = Permission::findOrCreate(
        AdminPermissionEnum::THEME_MANAGE->value,
        'web',
    );
});

test('guests cannot create a custom theme', function () {
    $this->post(route('appearance.custom-themes.store'), [
        'name' => 'Our Theme',
        'base_theme_key' => 'builtin/cupcake',
    ])->assertRedirect(route('login'));

    $this->assertDatabaseCount('custom_themes', 0);
});

test('users without permission cannot create a custom theme', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('appearance.custom-themes.store'), [
            'name' => 'Our Theme',
            'base_theme_key' => 'builtin/cupcake',
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('custom_themes', 0);
});

test('an authorized admin can create an unpublished theme', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo($this->themePermission);

    $this->actingAs($admin)
        ->post(route('appearance.custom-themes.store'), [
            'name' => '  Our Theme  ',
            'base_theme_key' => 'builtin/cupcake',
        ])
        ->assertRedirect(route('appearance.edit'));

    $draft = CustomTheme::query()->sole();

    expect($draft->name)->toBe('Our Theme');
    expect($draft->key)->toStartWith('custom/');
    expect($draft->published)->toBeFalse();
    expect($draft->created_by)->toBe($admin->id);
    expect($draft->colors)->toBe(
        app(ThemeRegistry::class)
            ->findBuiltInTheme('builtin/cupcake')['colors'],
    );
});

test('an unavailable base theme is rejected', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo($this->themePermission);

    $this->actingAs($admin)
        ->from(route('appearance.edit'))
        ->post(route('appearance.custom-themes.store'), [
            'name' => 'Our Theme',
            'base_theme_key' => 'builtin/not-real',
        ])
        ->assertRedirect(route('appearance.edit'))
        ->assertSessionHasErrors('base_theme_key');

    $this->assertDatabaseCount('custom_themes', 0);
});
