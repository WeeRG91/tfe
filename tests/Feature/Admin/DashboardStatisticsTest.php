<?php

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    config()->set('restaurant.timezone', 'Europe/Luxembourg');

    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->givePermissionTo(
        Permission::findOrCreate('admin.access', 'web'),
    );

    $this->actingAs($admin);
});

test('today uses the restaurant date for completed sales', function () {
    config()->set('restaurant.timezone', 'Europe/Luxembourg');

    $this->travelTo(Carbon::parse('2026-09-14 22:30:00', 'UTC'));

    $user = User::factory()->withoutTwoFactor()->create();

    Order::query()->create([
        'order_number' => 'PREVIOUS-DAY',
        'user_id' => $user->id,
        'status' => OrderStatusEnum::COMPLETED,
        'payment_status' => PaymentStatusEnum::PAID,
        'total_inc_vat' => 50,
        'created_at' => '2026-09-14 21:55:00',
        'completed_at' => '2026-09-14 21:55:00',
    ]);

    Order::query()->create([
        'order_number' => 'TODAY',
        'user_id' => $user->id,
        'status' => OrderStatusEnum::COMPLETED,
        'payment_status' => PaymentStatusEnum::PAID,
        'total_inc_vat' => 100,
        'created_at' => '2026-09-14 22:15:00',
        'completed_at' => '2026-09-14 22:15:00',
    ]);

    $this->get(route('admin.dashboard', ['period' => 1]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('period', 1)
            ->where('completedOrders', 1)
            ->where('paidCompletedSales', 100)
            ->has('dailySales', 1)
            ->where('dailySales.0.date', '2026-09-15')
            ->where('dailySales.0.sales', 100)
            ->etc()
        );
});

test('placed orders include pending and cancelled orders but sales do not', function () {
    config()->set('restaurant.timezone', 'Europe/Luxembourg');
    $this->travelTo(Carbon::parse('2026-09-14 23:30:00', 'UTC'));

    $user = User::factory()->withoutTwoFactor()->create();

    Order::query()->create([
        'order_number' => 'PAID-COMPLETED',
        'user_id' => $user->id,
        'status' => OrderStatusEnum::COMPLETED,
        'payment_status' => PaymentStatusEnum::PAID,
        'total_inc_vat' => 20,
        'created_at' => '2026-09-14 22:40:00',
        'completed_at' => '2026-09-14 22:50:00',
    ]);

    Order::query()->create([
        'order_number' => 'PENDING',
        'user_id' => $user->id,
        'status' => OrderStatusEnum::PENDING,
        'payment_status' => PaymentStatusEnum::PENDING,
        'total_inc_vat' => 30,
        'created_at' => '2026-09-14 23:00:00',
    ]);

    Order::query()->create([
        'order_number' => 'CANCELLED',
        'user_id' => $user->id,
        'status' => OrderStatusEnum::CANCELLED,
        'payment_status' => PaymentStatusEnum::PENDING,
        'total_inc_vat' => 40,
        'created_at' => '2026-09-14 23:10:00',
        'cancelled_at' => '2026-09-14 23:15:00',
    ]);

    $this->get(route('admin.dashboard', ['period' => 1]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('totalPeriodOrders', 3)
            ->where('cancelledPeriodOrders', 1)
            ->where('cancellationRate', 33.33)
            ->where('completedOrders', 1)
            ->where('paidCompletedSales', 20)
            ->where('averageOrderValue', 20)
            ->where('dailySales.0.sales', 20)
            ->etc()
        );
});
