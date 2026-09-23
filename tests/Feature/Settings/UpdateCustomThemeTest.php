<?php

use App\Enums\Permissions\AdminPermissionEnum;
use App\Models\User;
use App\Services\CustomThemeService;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $creator = User::factory()->create();

    $this->draft = app(CustomThemeService::class)->createDraft(
        'Original Theme',
        'builtin/light',
        $creator,
    );
});

test('guests cannot update a custom theme', function () {
    $this->put(route('appearance.custom-themes.update', $this->draft), [
        'name' => 'Changed Theme',
        'colors' => $this->draft->colors,
    ])->assertRedirect(route('login'));

    expect($this->draft->fresh()->name)->toBe('Original Theme');
});

test('users without permission cannot update a custom theme', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('appearance.custom-themes.update', $this->draft), [
            'name' => 'Changed Theme',
            'colors' => $this->draft->colors,
        ])
        ->assertForbidden();

    expect($this->draft->fresh()->name)->toBe('Original Theme');
});

test('theme managers can update a draft name and palette', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo(Permission::findOrCreate(
        AdminPermissionEnum::THEME_MANAGE->value,
        'web',
    ));

    $originalKey = $this->draft->key;
    $colors = $this->draft->colors;
    $colors['primary'] = '#123456';

    $this->actingAs($admin)
        ->put(route('appearance.custom-themes.update', $this->draft), [
            'name' => '  Blue Theme  ',
            'colors' => $colors,
        ])
        ->assertRedirect(route('appearance.custom-themes.edit', $this->draft));

    $updated = $this->draft->fresh();

    expect($updated->name)->toBe('Blue Theme');
    expect($updated->key)->toBe($originalKey);
    expect($updated->colors['primary'])->toBe('#123456');
    expect($updated->colors)->toHaveCount(38);
    expect($updated->updated_by)->toBe($admin->id);
    expect($updated->published)->toBeFalse();
});

test('an invalid color is rejected without changing the theme', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo(Permission::findOrCreate(
        AdminPermissionEnum::THEME_MANAGE->value,
        'web',
    ));

    $originalColors = $this->draft->colors;
    $invalidColors = $originalColors;
    $invalidColors['primary'] = 'red';

    $this->actingAs($admin)
        ->from(route('appearance.custom-themes.edit', $this->draft))
        ->put(route('appearance.custom-themes.update', $this->draft), [
            'name' => 'Invalid Theme',
            'colors' => $invalidColors,
        ])
        ->assertRedirect(route('appearance.custom-themes.edit', $this->draft))
        ->assertSessionHasErrors('colors.primary');

    $unchanged = $this->draft->fresh();

    expect($unchanged->name)->toBe('Original Theme');
    expect($unchanged->colors)->toBe($originalColors);
});
