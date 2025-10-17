<?php

use App\Http\Controllers\DishController;
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
        Route::patch('/{dish}', 'update')->name('update');
        Route::delete('/{dish}', 'destroy')->name('destroy');
    });
});



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
