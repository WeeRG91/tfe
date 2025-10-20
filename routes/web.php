<?php

use App\Http\Controllers\DishController;
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
        Route::get('/{dish}', 'show')->name('show');
        Route::get('/{dish}/edit', 'edit')->name('edit');
        Route::post('/{dish}', 'update')->name('update');
        Route::patch('/{dish}/available', 'available')->name('available');
        Route::delete('/{dish}', 'destroy')->name('destroy');
    });

    Route::controller(TrashedController::class)->prefix('trashed')->name('trashed.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/restore', 'restore')->name('restore');
        Route::post('/forceDelete', 'forceDelete')->name('forceDelete');
    });
});



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
