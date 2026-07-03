<?php

use App\Http\Controllers\Admin\AllergenController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\ConfirmedOrderController;
use App\Http\Controllers\Admin\DishController;
use App\Http\Controllers\Admin\DrinkController;
use App\Http\Controllers\Admin\GlobalSearchController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\MeatController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'permission:admin.access'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::controller(DishController::class)->prefix('dishes')->name('dish.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-dishes', 'getDishes')->name('get-dishes');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{dish}/edit', 'edit')->name('edit');
        Route::post('/{dish}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::patch('/{id}/available', 'available')->name('available');
        Route::post('/{id}/restore', 'restore')->name('restore');
        Route::delete('/{id}/forceDelete', 'forceDelete')->name('forceDelete');
    });

    Route::controller(DrinkController::class)->prefix('drinks')->name('drink.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-drinks', 'getDrinks')->name('get-drinks');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{drink}/edit', 'edit')->name('edit');
        Route::post('/{drink}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::patch('/{id}/available', 'available')->name('available');
        Route::post('/{id}/restore', 'restore')->name('restore');
        Route::delete('/{id}/forceDelete', 'forceDelete')->name('forceDelete');
    });

    Route::controller(IngredientController::class)->prefix('ingredients')->name('ingredient.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-ingredients', 'getIngredients')->name('get-ingredients');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::post('/quick-create', 'quickCreate')->name('quick-create');
        Route::get('/{ingredient}/edit', 'edit')->name('edit');
        Route::post('/{ingredient}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::post('/{id}/restore', 'restore')->name('restore');
        Route::delete('/{id}/forceDelete', 'forceDelete')->name('forceDelete');
    });

    Route::controller(AllergenController::class)->prefix('allergens')->name('allergen.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-allergens', 'getAllergens')->name('get-allergens');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{allergen}/edit', 'edit')->name('edit');
        Route::post('/{allergen}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::post('/{id}/restore', 'restore')->name('restore');
        Route::delete('/{id}/forceDelete', 'forceDelete')->name('forceDelete');
    });

    Route::controller(MeatController::class)->prefix('meats')->name('meat.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-meats', 'getMeats')->name('get-meats');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::post('/quick-create', 'quickCreate')->name('quick-create');
        Route::get('/{meat}/edit', 'edit')->name('edit');
        Route::post('/{meat}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::post('/{id}/restore', 'restore')->name('restore');
        Route::delete('/{id}/forceDelete', 'forceDelete')->name('forceDelete');
    });

    Route::controller(ImageController::class)->prefix('images')->name('image.')->group(function () {
        Route::delete('/{image}', 'destroy')->name('destroy');
        Route::post('/{image}/set-main-image', 'setMainImage')->name('set-main-image');
    });

    Route::controller(GlobalSearchController::class)->prefix('global-search')->name('global-search.')->group(function () {
        Route::get('/', 'search')->name('search');
    });

    Route::controller(ConfirmedOrderController::class)->prefix('confirmed-orders')->name('confirmed-order.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-confirmed-orders', 'getConfirmedOrders')->name('get-confirmed-orders');
        Route::post('/{orderId}/update-order-status', 'updateOrderStatus')->name('update-order-status');
    });

    Route::controller(ChatController::class)->prefix('chats')->name('chat.')->group(function () {
        Route::get('/', 'chats')->name('chats');
        Route::get('/get-chats', 'getChats')->name('get-chats');
        Route::get('/{chat}/messages', 'getChatMessages')->name('get-chat-messages');
        Route::patch('/{chatId}/read', 'markAsRead')->name('mark-as-read');
        Route::post('/{chat}/send', 'send')->name('send');
    });

    Route::controller(MessageController::class)->prefix('messages')->name('message.')->group(function () {
        Route::patch('/{message}/update', 'update')->name('update');
        Route::patch('/{message}/unsend', 'unsend')->name('unsend');
        Route::delete('/{message}/delete', 'destroy')->name('destroy');
    });

    Route::controller(RoleController::class)->prefix('roles')->name('role.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-roles', 'getRoles')->name('get-roles');
        Route::get('/create', 'create')->name('create');
        Route::get('/{role}/edit', 'edit')->name('edit');
        Route::post('/', 'store')->name('store');
        Route::post('/{role}', 'update')->name('update');
        Route::delete('/{role}', 'destroy')->name('destroy');
    });

    Route::controller(UserController::class)->prefix('users')->name('user.')->group(function () {
       Route::get('/', 'index')->name('index');
       Route::get('/get-users', 'getUsers')->name('get-users');
       Route::get('/{user}/show', 'show')->name('show');
       Route::get('/create', 'create')->name('create');
       Route::get('/{user}/edit', 'edit')->name('edit');
       Route::post('/', 'store')->name('store');
       Route::post('/{user}', 'update')->name('update');
       Route::delete('/{user}', 'destroy')->name('destroy');
    });
});
