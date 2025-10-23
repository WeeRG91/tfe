<?php

use App\Http\Controllers\DishController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\TrashedController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::controller(DishController::class)->prefix('dishes')->name('dish.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{dish}/edit', 'edit')->name('edit');
        Route::post('/{dish}', 'update')->name('update');
        Route::delete('/{dish}', 'destroy')->name('destroy');
        Route::patch('/{dish}/available', 'available')->name('available');
    });

    Route::controller(IngredientController::class)->prefix('ingredients')->name('ingredient.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{ingredient}/edit', 'edit')->name('edit');
        Route::post('/{ingredient}', 'update')->name('update');
        Route::delete('/{ingredient}', 'destroy')->name('destroy');
    });

    Route::controller(TrashedController::class)->prefix('trashed')->name('trashed.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/restore', 'restore')->name('restore');
        Route::post('/forceDelete', 'forceDelete')->name('forceDelete');
    });

    Route::controller(ImageController::class)->prefix('images')->name('image.')->group(function () {
        Route::delete('/{image}', 'destroy')->name('destroy');
    });
});



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
