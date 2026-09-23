<?php

use App\Enums\Permissions\AdminPermissionEnum;
use App\Http\Controllers\Settings\CustomThemeController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\RestaurantThemeController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get(
        'settings/appearance',
        [RestaurantThemeController::class, 'edit'],
    )->name('appearance.edit');
    Route::get('settings/appearance/custom-themes/{customTheme}/edit', [CustomThemeController::class, 'edit'])
        ->middleware('permission:'.AdminPermissionEnum::THEME_MANAGE->value)
        ->name('appearance.custom-themes.edit');
    Route::put(
        'settings/appearance/restaurant-default',
        [RestaurantThemeController::class, 'updateDefault'],
    )
        ->middleware(
            'permission:'.AdminPermissionEnum::THEME_MANAGE->value,
        )
        ->name('appearance.restaurant-default.update');
    Route::put(
        'settings/appearance/custom-themes/{customTheme}',
        [CustomThemeController::class, 'update'],
    )
        ->middleware('permission:'.AdminPermissionEnum::THEME_MANAGE->value)
        ->name('appearance.custom-themes.update');
    Route::post('settings/appearance/custom-themes', [CustomThemeController::class, 'store'])
        ->middleware('permission:'.AdminPermissionEnum::THEME_MANAGE->value)
        ->name('appearance.custom-themes.store');
    Route::post(
        'settings/appearance/custom-themes/{customTheme}/publish',
        [CustomThemeController::class, 'publish'],
    )
        ->middleware('permission:'.AdminPermissionEnum::THEME_MANAGE->value)
        ->name('appearance.custom-themes.publish');
    Route::delete('settings/appearance/custom-themes/{customTheme}', [CustomThemeController::class, 'destroy'])
        ->middleware('permission:'.AdminPermissionEnum::THEME_MANAGE->value)
        ->name('appearance.custom-themes.destroy');

    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');
});
