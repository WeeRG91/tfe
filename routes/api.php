<?php

use App\Http\Controllers\Api\V1\DishController;
use App\Http\Middleware\SetApiLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware(SetApiLocale::class)
    ->group(function () {
        Route::get('dishes', [DishController::class, 'index'])
            ->name('dishes.index');
    });

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
