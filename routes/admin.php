<?php

use App\Http\Controllers\Admin\AllergenController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\CompanyDeliveryDateController;
use App\Http\Controllers\Admin\ConfirmedOrderController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryCompanyController;
use App\Http\Controllers\Admin\DishController;
use App\Http\Controllers\Admin\DrinkController;
use App\Http\Controllers\Admin\GlobalSearchController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\MeatController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\RestaurantScheduleController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'permission:admin.access'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::controller(DishController::class)->prefix('dishes')->name('dish.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-dishes', 'getDishes')->name('get-dishes');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{dish}/edit', 'edit')->name('edit');
            Route::post('/{dish}', 'update')->name('update');
            Route::delete('/{dish}', 'destroy')->name('destroy');
            Route::patch('/{dish}/available', 'available')->name('available');
            Route::post('/{dish}/restore', 'restore')->name('restore');
            Route::delete('/{dish}/forceDelete', 'forceDelete')->name('forceDelete');
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
            Route::post('/{drink}/restore', 'restore')->name('restore');
            Route::delete('/{drink}/forceDelete', 'forceDelete')->name('forceDelete');
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
            Route::post('/{ingredient}/restore', 'restore')->name('restore');
            Route::delete('/{ingredient}/forceDelete', 'forceDelete')->name('forceDelete');
        });

        Route::controller(AllergenController::class)->prefix('allergens')->name('allergen.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-allergens', 'getAllergens')->name('get-allergens');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{allergen}/edit', 'edit')->name('edit');
            Route::post('/{allergen}', 'update')->name('update');
            Route::delete('/{allergen}', 'destroy')->name('destroy');
            Route::post('/{allergen}/restore', 'restore')->name('restore');
            Route::delete('/{allergen}/forceDelete', 'forceDelete')->name('forceDelete');
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
            Route::post('/{meat}/restore', 'restore')->name('restore');
            Route::delete('/{meat}/forceDelete', 'forceDelete')->name('forceDelete');
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
            Route::post('/{order}/update-order-status', 'updateOrderStatus')->name('update-order-status');
            Route::patch('/{order}/cancel', 'cancel')->name('cancel');
        });

        Route::controller(ChatController::class)->prefix('chats')->name('chat.')->group(function () {
            Route::get('/', 'chats')->name('chats');
            Route::get('/get-chats', 'getChats')->name('get-chats');
            Route::get('/{chat}/messages', 'getChatMessages')->name('get-chat-messages');
            Route::post('/{user}/create', 'create')->name('create');
        });

        Route::controller(MessageController::class)->prefix('messages')->name('message.')->group(function () {
            Route::post('/{chat}/send', 'send')->name('send');
            Route::patch('/{message}/unsend', 'unsend')->name('unsend');
            Route::patch('/{chat}/read', 'markAsRead')->name('mark-as-read');
            Route::patch('/{message}/update', 'update')->name('update');
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
            Route::get('/{user}/show', 'show')->withTrashed()->name('show');
            Route::get('/create', 'create')->name('create');
            Route::get('/{user}/edit', 'edit')->name('edit');
            Route::post('/', 'store')->name('store');
            Route::post('/{user}', 'update')->name('update');
            Route::post('/{user}/inactivate', 'inactivate')->name('inactivate');
            Route::post('/{user}/reactivate', 'reactivate')->withTrashed()->name('reactivate');
            Route::delete('/{user}', 'destroy')->withTrashed()->name('destroy');
        });

        Route::controller(RestaurantScheduleController::class)->prefix('restaurant-schedule')->name('restaurant-schedule.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/hours', 'updateHours')->name('hours.update');
            Route::post('/closures', 'storeClosure')->name('closures.store');
            Route::put('/closures/{restaurantClosure}', 'updateClosure')->name('closures.update');
            Route::delete('/closures/{restaurantClosure}', 'destroyClosure')->name('closures.destroy');
        });

        Route::controller(DeliveryCompanyController::class)
            ->prefix('delivery-companies')
            ->name('delivery-companies.')
            ->middleware('company.delivery.enabled')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::put('/{deliveryCompany}', 'update')->name('update');
                Route::delete('/{deliveryCompany}', 'destroy')->name('destroy');
            });

        Route::controller(CompanyDeliveryDateController::class)
            ->prefix('delivery-companies/{deliveryCompany}/dates')
            ->name('delivery-companies.dates.')
            ->middleware('company.delivery.enabled')
            ->scopeBindings()
            ->group(function () {
                Route::post('/', 'store')->name('store');
                Route::patch('/{deliveryDate}/availability', 'updateAvailability')->name('availability');
                Route::delete('/{deliveryDate}', 'destroy')->name('destroy');
            });
    });
