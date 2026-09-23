<?php

use App\Enums\Permissions\AdminPermissionEnum;
use App\Models\RestaurantThemeSetting;
use App\Models\User;
use App\Services\CustomThemeService;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

test('guests are redirected from appearance settings', function () {
    $this->get(route('appearance.edit'))
        ->assertRedirect(route('login'));
});

test('appearance settings provide themes and the restaurant default', function () {
    $user = User::factory()->create();

    RestaurantThemeSetting::query()->create([
        'default_client_theme_key' => 'builtin/cupcake',
    ]);

    $this->actingAs($user)
        ->get(route('appearance.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Appearance')
            ->where('defaultClientThemeKey', 'builtin/cupcake')
            ->has('availableThemes', 15)
            ->where('availableThemes.0.key', 'builtin/light')
            ->where('availableThemes.1.key', 'builtin/dark')
            ->where('availableThemes.2.key', 'builtin/cupcake')
            ->where('availableThemes.14.key', 'builtin/sunset')
        );
});

test('only theme managers receive draft themes on the appearance page', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo(Permission::findOrCreate(
        AdminPermissionEnum::THEME_MANAGE->value,
        'web',
    ));

    $customThemes = app(CustomThemeService::class);

    $draft = $customThemes->createDraft(
        'Draft Theme',
        'builtin/light',
        $admin,
    );

    $published = $customThemes->publishTheme(
        $customThemes->createDraft(
            'Published Theme',
            'builtin/light',
            $admin,
        ),
        $admin,
    );

    $this->actingAs($admin)
        ->get(route('appearance.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Appearance')
            ->has('managedCustomThemes', 2)
            ->where('managedCustomThemes.0.key', $draft->key)
            ->where('managedCustomThemes.0.id', $draft->id)
            ->where('managedCustomThemes.1.key', $published->key)
            ->where('managedCustomThemes.1.id', $published->id)
            ->has('availableThemes', 16)
            ->where('availableThemes.15.key', $published->key)
            ->etc()
        );

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('appearance.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Appearance')
            ->has('managedCustomThemes', 0)
            ->has('availableThemes', 16)
            ->where('availableThemes.15.key', $published->key)
            ->etc()
        );
});
