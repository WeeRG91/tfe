<?php

use App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\V1\Auth\RegisteredUserController;
use App\Http\Controllers\Api\V1\DishController;
use App\Http\Controllers\Api\V1\DrinkController;
use App\Http\Middleware\SetApiLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware(SetApiLocale::class)
    ->group(function () {
        Route::get('auth/user', [AuthenticatedSessionController::class, 'show'])
            ->middleware('auth:sanctum')
            ->name('auth.user');
        Route::post('auth/register', [RegisteredUserController::class, 'store'])
            ->name('auth.register');
        Route::post('auth/login', [AuthenticatedSessionController::class, 'store'])
            ->name('auth.login');
        Route::post('auth/logout', [AuthenticatedSessionController::class, 'destroy'])
            ->middleware('auth:sanctum')
            ->name('auth.logout');

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
