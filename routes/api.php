<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\NewPasswordController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetLinkController;
use App\Http\Controllers\Api\V1\Auth\ProfileController;
use App\Http\Controllers\Api\V1\Auth\RegisteredUserController;
use App\Http\Controllers\Api\V1\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Api\V1\Auth\TwoFactorSettingsController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\DishController;
use App\Http\Controllers\Api\V1\DrinkController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PushTokenController;
use App\Http\Middleware\SetApiLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware(SetApiLocale::class)
    ->group(function () {
        Route::get('auth/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware(['signed', 'throttle:6,1'])
            ->whereNumber('id')
            ->name('auth.email.verification.verify');
        Route::post('auth/email/verification-notification', [EmailVerificationController::class, 'send'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('auth.email.verification.send');

        Route::get('auth/user', [AuthenticatedSessionController::class, 'show'])
            ->middleware('auth:sanctum')
            ->name('auth.user');
        Route::patch('auth/user', [ProfileController::class, 'update'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('auth.user.update');
        Route::post('auth/user/avatar', [ProfileController::class, 'storePhoto'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('auth.user.avatar.store');
        Route::delete('auth/user/avatar', [ProfileController::class, 'destroyPhoto'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('auth.user.avatar.destroy');

        Route::post('auth/register', [RegisteredUserController::class, 'store'])
            ->name('auth.register');
        Route::post('auth/login', [AuthenticatedSessionController::class, 'store'])
            ->name('auth.login');
        Route::post('auth/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('auth.password.email');
        Route::post('auth/reset-password', [NewPasswordController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('auth.password.update');
        Route::post('auth/logout', [AuthenticatedSessionController::class, 'destroy'])
            ->middleware('auth:sanctum')
            ->name('auth.logout');

        Route::get('auth/two-factor', [TwoFactorSettingsController::class, 'show'])
            ->middleware('auth:sanctum')
            ->name('auth.two-factor.show');
        Route::post('auth/two-factor', [TwoFactorSettingsController::class, 'store'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('auth.two-factor.store');
        Route::post('auth/two-factor/confirm', [TwoFactorSettingsController::class, 'confirm'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('auth.two-factor.confirm');
        Route::post('auth/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('auth.two-factor-challenge');
        Route::post('auth/two-factor/recovery-codes', [TwoFactorSettingsController::class, 'regenerateRecoveryCodes'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('auth.two-factor.recovery-codes.store');
        Route::delete('auth/two-factor', [TwoFactorSettingsController::class, 'destroy'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('auth.two-factor.destroy');

        Route::get('dishes', [DishController::class, 'index'])
            ->name('dishes.index');
        Route::get('dishes/{dish}', [DishController::class, 'show'])
            ->name('dishes.show');

        Route::get('drinks', [DrinkController::class, 'index'])
            ->name('drinks.index');

        Route::get('cart', [CartController::class, 'show'])
            ->middleware('auth:sanctum')
            ->name('cart.show');
        Route::patch('cart/items/{cartItemId}/quantity', [CartController::class, 'updateQuantity'])
            ->whereNumber('cartItemId')
            ->middleware(['auth:sanctum', 'throttle:60,1'])
            ->name('cart.items.quantity.update');
        Route::patch('cart/items/{cartItemId}/notes', [CartController::class, 'updateNotes'])
            ->whereNumber('cartItemId')
            ->middleware(['auth:sanctum', 'throttle:30,1'])
            ->name('cart.items.notes.update');
        Route::post('cart/items/dishes', [CartController::class, 'storeDish'])
            ->middleware(['auth:sanctum', 'throttle:30,1'])
            ->name('cart.items.dishes.store');
        Route::post('cart/items/drinks', [CartController::class, 'storeDrink'])
            ->middleware(['auth:sanctum', 'throttle:30,1'])
            ->name('cart.items.drinks.store');
        Route::delete('cart/items/{cartItemId}', [CartController::class, 'destroyItem'])
            ->whereNumber('cartItemId')
            ->middleware(['auth:sanctum', 'throttle:60,1'])
            ->name('cart.items.destroy');

        Route::get('checkout', [CheckoutController::class, 'show'])
            ->middleware('auth:sanctum')
            ->name('checkout.show');
        Route::post('checkout/orders', [CheckoutController::class, 'storeOrder'])
            ->middleware([
                'auth:sanctum',
                'verified',
                'throttle:10,1',
            ])
            ->name('checkout.orders.store');

        Route::post('checkout/orders/{order}/payment-intent', [PaymentController::class, 'storePaymentIntent'])
            ->whereNumber('order')
            ->middleware([
                'auth:sanctum',
                'verified',
                'throttle:10,1',
            ])
            ->name('checkout.orders.payment-intent.store');

        Route::patch('addresses/{addressId}', [AddressController::class, 'update'])
            ->whereNumber('addressId')
            ->middleware(['auth:sanctum', 'throttle:30,1'])
            ->name('addresses.update');
        Route::post('addresses', [AddressController::class, 'store'])
            ->middleware(['auth:sanctum', 'throttle:30,1'])
            ->name('addresses.store');
        Route::delete('addresses/{addressId}', [AddressController::class, 'destroy'])
            ->whereNumber('addressId')
            ->middleware(['auth:sanctum', 'throttle:30,1'])
            ->name('addresses.destroy');

        Route::get('orders', [OrderController::class, 'index'])
            ->middleware('auth:sanctum')
            ->name('orders.index');
        Route::get('orders/{orderId}', [OrderController::class, 'show'])
            ->whereNumber('orderId')
            ->middleware('auth:sanctum')
            ->name('orders.show');

        Route::get('notifications', [NotificationController::class, 'index'])
            ->middleware('auth:sanctum')
            ->name('notifications.index');
        Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead'])
            ->middleware(['auth:sanctum', 'throttle:30,1'])
            ->name('notifications.read-all');
        Route::patch('notifications/{notificationId}/read', [NotificationController::class, 'markAsRead'])
            ->whereNumber('notificationId')
            ->middleware(['auth:sanctum', 'throttle:60,1'])
            ->name('notifications.read');

        Route::post('push-tokens', [PushTokenController::class, 'store'])
            ->middleware(['auth:sanctum', 'throttle:20,1'])
            ->name('push-tokens.store');
        Route::delete('push-tokens', [PushTokenController::class, 'destroy'])
            ->middleware(['auth:sanctum', 'throttle:20,1'])
            ->name('push-tokens.destroy');
    });

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
