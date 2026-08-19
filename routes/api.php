<?php

use App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\ProfileController;
use App\Http\Controllers\Api\V1\Auth\RegisteredUserController;
use App\Http\Controllers\Api\V1\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Api\V1\Auth\TwoFactorSettingsController;
use App\Http\Controllers\Api\V1\DishController;
use App\Http\Controllers\Api\V1\DrinkController;
use App\Http\Middleware\SetApiLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware(SetApiLocale::class)
    ->group(function () {
        Route::get('auth/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware(['signed', 'throttle:6,1'])
            ->whereNumber('id')
            ->name('auth.email.verification.verify');
        Route::post('auth/email/verification-notification', [EmailVerificationController::class, 'send'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('auth.email.verification.send');

        Route::get('auth/user', [AuthenticatedSessionController::class, 'show'])
            ->middleware('auth:sanctum')
            ->name('auth.user');
        Route::patch('auth/user', [ProfileController::class, 'update'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('auth.user.update');

        Route::post('auth/register', [RegisteredUserController::class, 'store'])
            ->name('auth.register');
        Route::post('auth/login', [AuthenticatedSessionController::class, 'store'])
            ->name('auth.login');
        Route::post('auth/logout', [AuthenticatedSessionController::class, 'destroy'])
            ->middleware('auth:sanctum')
            ->name('auth.logout');

        Route::get('auth/two-factor', [TwoFactorSettingsController::class, 'show'])
            ->middleware('auth:sanctum')
            ->name('auth.two-factor.show');
        Route::post('auth/two-factor', [TwoFactorSettingsController::class, 'store'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('auth.two-factor.store');
        Route::post('auth/two-factor/confirm', [TwoFactorSettingsController::class, 'confirm'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('auth.two-factor.confirm');
        Route::post('auth/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('auth.two-factor-challenge');
        Route::post('auth/two-factor/recovery-codes', [TwoFactorSettingsController::class, 'regenerateRecoveryCodes'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('auth.two-factor.recovery-codes.store');
        Route::delete('auth/two-factor', [TwoFactorSettingsController::class, 'destroy'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('auth.two-factor.destroy');

        Route::get('dishes', [DishController::class, 'index'])
            ->name('dishes.index');
        Route::get('dishes/{dish}', [DishController::class, 'show'])
            ->name('dishes.show');

        Route::get('drinks', [DrinkController::class, 'index'])
            ->name('drinks.index');
    });

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
