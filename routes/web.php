<?php

use App\Http\Controllers\Client\AddressController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\ClientGlobalSearchController;
use App\Http\Controllers\Client\LoyaltyPointTransactionController;
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
        Route::get('/{order}', 'getOrder')->name('get-order');
        Route::get('/{order}/details', 'orderDetails')->name('order-details');
        Route::get('/{order}/reorder', 'reorder')->name('reorder');
        Route::post('/place-order', 'placeOrder')->name('place-order');
        Route::post('/reorder', 'confirmReorder')->name('confirm-reorder');
        Route::patch('/{order}/cancel', 'cancel')->name('cancel');
        Route::delete('/{order}', 'destroy')->name('destroy');
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

    Route::controller(LoyaltyPointTransactionController::class)->prefix('loyalty-point-transactions')->name('loyalty-point-transaction.')->group(function () {
        Route::get('/my-points', 'myPoints')->name('my-points');
        Route::get('/get-loyalty-point-transactions', 'getLoyaltyPointTransactions')->name('get-loyalty-point-transactions');
    });
});



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
