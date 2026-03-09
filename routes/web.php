<?php

use App\Http\Controllers\Admin\AllergenController;
use App\Http\Controllers\Admin\DishController;
use App\Http\Controllers\Admin\DrinkController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\MeatController;
use App\Http\Controllers\Admin\TrashedController;
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
        Route::get('/get-dishes', 'getDishes')->name('get-dishes');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{dish}/edit', 'edit')->name('edit');
        Route::post('/{dish}', 'update')->name('update');
        Route::delete('/{dish}', 'destroy')->name('destroy');
        Route::patch('/{dish}/available', 'available')->name('available');
    });

    Route::controller(DrinkController::class)->prefix('drinks')->name('drink.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-drinks', 'getDrinks')->name('get-drinks');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{drink}/edit', 'edit')->name('edit');
        Route::post('/{drink}', 'update')->name('update');
        Route::delete('/{drink}', 'destroy')->name('destroy');
        Route::patch('/{drink}/available', 'available')->name('available');
    });

    Route::controller(IngredientController::class)->prefix('ingredients')->name('ingredient.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-ingredients', 'getIngredients')->name('get-ingredients');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::post('/quick-create', 'quickCreate')->name('quick-create');
        Route::get('/{ingredient}/edit', 'edit')->name('edit');
        Route::post('/{ingredient}', 'update')->name('update');
        Route::delete('/{ingredient}', 'destroy')->name('destroy');
    });

    Route::controller(AllergenController::class)->prefix('allergens')->name('allergen.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-allergens', 'getAllergens')->name('get-allergens');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{allergen}/edit', 'edit')->name('edit');
        Route::post('/{allergen}', 'update')->name('update');
        Route::delete('/{allergen}', 'destroy')->name('destroy');
    });

    Route::controller(MeatController::class)->prefix('meats')->name('meat.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-meats', 'getMeats')->name('get-meats');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::post('/quick-create', 'quickCreate')->name('quick-create');
        Route::get('/{meat}/edit', 'edit')->name('edit');
        Route::post('/{meat}', 'update')->name('update');
        Route::delete('/{meat}', 'destroy')->name('destroy');
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
