<?php

use App\Http\Controllers\Client\AddressController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\ClientGlobalSearchController;
use App\Http\Controllers\Client\MenuController;
use App\Http\Controllers\Client\NotificationController;
use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\Client\PaymentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('client/Home');
})->name('home');

Route::controller(MenuController::class)->prefix('menu')->name('menu.')->group(function () {
    Route::get('/dishes', 'dish')->name('dish');
    Route::get('/dishes/get-dishes', 'getDishes')->name('get-dishes');
    Route::get('/dishes/{dish}/show-dish', 'showDish')->name('show-dish');
    Route::get('/drinks', 'drink')->name('drink');
    Route::get('/drinks/get-drinks', 'getDrinks')->name('get-drinks');
});

Route::controller(CartController::class)->prefix('cart')->name('cart.')->group(function () {
    Route::get('/checkout', 'checkout')->name('checkout');
    Route::get('/place-order', 'placeOrder')->name('place-order');
    Route::get('/get-cart', 'getCart')->name('get-cart');
    Route::post('/items/add-dish', 'addDish')->name('add-dish');
    Route::post('/items/add-drink', 'addDrink')->name('add-drink');
    Route::patch('/items/{cartItemId}/notes', 'updateNotes')->name('update-notes');
    Route::patch('/items/{cartItemId}/quantity', 'updateQuantity')->name('update-quantity');
    Route::delete('/items/{cartItemId}', 'removeItem')->name('remove-item');
});

Route::controller(ClientGlobalSearchController::class)->prefix('client-global-search')->name('client-global-search.')->group(function () {
    Route::get('/', 'search')->name('search');
});

Route::controller(PaymentController::class)->prefix('payments')->name('payment-order.')->group(function () {
    Route::post('/stripe/webhook', 'stripeWebhook')->name('stripe-webhook');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::controller(OrderController::class)->prefix('orders')->name('order.')->group(function () {
        Route::get('/', 'getOrders')->name('get-orders');
        Route::get('/my-orders', 'myOrders')->name('my-orders');
        Route::get('/{orderId}', 'getOrder')->name('get-order');
        Route::get('/{orderId}/details', 'orderDetails')->name('order-details');
        Route::get('/reorder', 'reorder')->name('reorder');
        Route::post('/place-order', 'placeOrder')->name('place-order');
    });

    Route::controller(AddressController::class)->prefix('addresses')->name('address.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::post('/{addressId}/edit', 'update')->name('update');
        Route::delete('/{addressId}/delete', 'destroy')->name('destroy');
    });

    Route::controller(PaymentController::class)->prefix('payments')->name('payment-order.')->group(function () {
        Route::get('/{order}/payment', 'payment')->name('payment');
        Route::get('/{order}/create-payment-intent', 'createPaymentIntent')->name('create-payment-intent');
        Route::get('/{order}/payment-success', 'paymentSuccess')->name('payment-success');
    });

    Route::controller(NotificationController::class)->prefix('notifications')->name('notification.')->group(function () {
        Route::get('/my-notifications', 'myNotifications')->name('my-notifications');
        Route::get('/get-notifications', 'getNotifications')->name('get-notifications');
        Route::patch('/{notification}/read', 'markAsRead')->name('mark-as-read');
        Route::patch('/', 'markAllAsRead')->name('mark-all-as-read');
        Route::delete('/{notification}', 'delete')->name('delete');
        Route::delete('/', 'deleteAll')->name('delete-all');
    });
});



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
