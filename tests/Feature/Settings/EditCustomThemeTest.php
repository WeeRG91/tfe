<?php

use App\Enums\Permissions\AdminPermissionEnum;
use App\Models\User;
use App\Services\CustomThemeService;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $creator = User::factory()->create();

    $this->draft = app(CustomThemeService::class)->createDraft(
        'My Draft Theme',
        'builtin/light',
        $creator,
    );
});

test('guests cannot open the custom theme editor', function () {
    $this->get(route('appearance.custom-themes.edit', $this->draft))
        ->assertRedirect(route('login'));
});

test('users without permission cannot open the custom theme editor', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('appearance.custom-themes.edit', $this->draft))
        ->assertForbidden();
});

test('theme managers can open a draft in the editor', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo(Permission::findOrCreate(
        AdminPermissionEnum::THEME_MANAGE->value,
        'web',
    ));

    $this->actingAs($admin)
        ->get(route('appearance.custom-themes.edit', $this->draft))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/CustomThemeEdit')
            ->where('customTheme.id', $this->draft->id)
            ->where('customTheme.key', $this->draft->key)
            ->where('customTheme.name', 'My Draft Theme')
            ->where('customTheme.published', false)
            ->where('customTheme.colors.primary', $this->draft->colors['primary'])
            ->where('theme.surface', 'admin')
            ->etc()
        );
});
