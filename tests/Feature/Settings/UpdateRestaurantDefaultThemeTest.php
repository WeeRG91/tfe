<?php

use App\Enums\Permissions\AdminPermissionEnum;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->themePermission = Permission::findOrCreate(
        AdminPermissionEnum::THEME_MANAGE->value,
        'web',
    );
});

test('guests cannot update the restaurant default theme', function () {
    $this->put(
        route('appearance.restaurant-default.update'),
        ['default_client_theme_key' => 'builtin/cupcake'],
    )->assertRedirect(route('login'));

    $this->assertDatabaseCount('restaurant_theme_settings', 0);
});

test('users without permission cannot update the restaurant default theme', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(
            route('appearance.restaurant-default.update'),
            ['default_client_theme_key' => 'builtin/cupcake'],
        )
        ->assertForbidden();

    $this->assertDatabaseCount('restaurant_theme_settings', 0);
});

test('authorized administrators can update the restaurant default theme', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo($this->themePermission);

    $this->actingAs($admin)
        ->from(route('appearance.edit'))
        ->put(
            route('appearance.restaurant-default.update'),
            ['default_client_theme_key' => 'builtin/cupcake'],
        )
        ->assertRedirect(route('appearance.edit'));

    $this->assertDatabaseHas('restaurant_theme_settings', [
        'id' => 1,
        'default_client_theme_key' => 'builtin/cupcake',
        'updated_by' => $admin->getKey(),
    ]);
});

test('the restaurant default must have a valid theme-key format', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo($this->themePermission);

    $this->actingAs($admin)
        ->from(route('appearance.edit'))
        ->put(
            route('appearance.restaurant-default.update'),
            ['default_client_theme_key' => 'system'],
        )
        ->assertRedirect(route('appearance.edit'))
        ->assertSessionHasErrors('default_client_theme_key');

    $this->assertDatabaseCount('restaurant_theme_settings', 0);
});

test('a well-formed but unavailable theme is rejected', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo($this->themePermission);

    $this->actingAs($admin)
        ->from(route('appearance.edit'))
        ->put(
            route('appearance.restaurant-default.update'),
            ['default_client_theme_key' => 'builtin/not-real'],
        )
        ->assertRedirect(route('appearance.edit'))
        ->assertSessionHasErrors('default_client_theme_key');

    $this->assertDatabaseCount('restaurant_theme_settings', 0);
});
