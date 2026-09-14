<?php

use App\Models\RestaurantClosure;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    config()->set('restaurant.timezone', 'Europe/Luxembourg');

    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $permission = Permission::findOrCreate(
        'admin.access',
        'web',
    );

    $user->givePermissionTo($permission);

    $this->actingAs($user);
});

it('creates a full-day exceptional closure', function () {
    $response = $this->postJson(
        route('admin.restaurant-schedule.closures.store'),
        [
            'is_all_day' => true,
            'starts_on' => '2026-09-14',
            'ends_on' => '2026-09-14',
            'starts_at' => null,
            'ends_at' => null,
            'reason' => 'Public holiday',
            'public_message' => 'We are closed today.',
        ],
    );

    $response
        ->assertCreated()
        ->assertJsonStructure([
            'data' => ['id'],
        ]);

    /*
     * Luxembourg is UTC+2 on September 14.
     *
     * Local 2026-09-14 00:00 = UTC 2026-09-13 22:00
     * Local 2026-09-15 00:00 = UTC 2026-09-14 22:00
     */
    $this->assertDatabaseHas('restaurant_closures', [
        'starts_at' => '2026-09-13 22:00:00',
        'ends_at' => '2026-09-14 22:00:00',
        'is_all_day' => true,
        'reason' => 'Public holiday',
        'public_message' => 'We are closed today.',
    ]);
});

it('creates a partial exceptional closure', function () {
    $this->postJson(
        route('admin.restaurant-schedule.closures.store'),
        [
            'is_all_day' => false,
            'starts_on' => null,
            'ends_on' => null,
            'starts_at' => '2026-09-14T15:00',
            'ends_at' => '2026-09-14T18:00',
            'reason' => 'Private event',
            'public_message' => 'Closed temporarily.',
        ],
    )->assertCreated();

    $this->assertDatabaseHas('restaurant_closures', [
        'starts_at' => '2026-09-14 13:00:00',
        'ends_at' => '2026-09-14 16:00:00',
        'is_all_day' => false,
        'reason' => 'Private event',
    ]);
});

it('rejects a partial closure ending before it starts', function () {
    $this->postJson(
        route('admin.restaurant-schedule.closures.store'),
        [
            'is_all_day' => false,
            'starts_at' => '2026-09-14T18:00',
            'ends_at' => '2026-09-14T15:00',
        ],
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('ends_at');
});

it('rejects a closure overlapping another closure', function () {
    $this->postJson(
        route('admin.restaurant-schedule.closures.store'),
        [
            'is_all_day' => false,
            'starts_at' => '2026-09-14T15:00',
            'ends_at' => '2026-09-14T18:00',
        ],
    )->assertCreated();

    $this->postJson(
        route('admin.restaurant-schedule.closures.store'),
        [
            'is_all_day' => false,
            'starts_at' => '2026-09-14T17:00',
            'ends_at' => '2026-09-14T19:00',
        ],
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('starts_at');
});

it('allows a closure to be updated without overlapping itself', function () {
    $closure = RestaurantClosure::query()->create([
        'starts_at' => '2026-09-14 13:00:00',
        'ends_at' => '2026-09-14 16:00:00',
        'is_all_day' => false,
        'reason' => 'Old reason',
    ]);

    $this->putJson(
        route(
            'admin.restaurant-schedule.closures.update',
            $closure,
        ),
        [
            'is_all_day' => false,
            'starts_at' => '2026-09-14T15:30',
            'ends_at' => '2026-09-14T18:30',
            'reason' => 'Updated reason',
        ],
    )->assertNoContent();

    $this->assertDatabaseHas('restaurant_closures', [
        'id' => $closure->id,
        'starts_at' => '2026-09-14 13:30:00',
        'ends_at' => '2026-09-14 16:30:00',
        'reason' => 'Updated reason',
    ]);
});

it('soft deletes a closure', function () {
    $closure = RestaurantClosure::query()->create([
        'starts_at' => '2026-09-14 13:00:00',
        'ends_at' => '2026-09-14 16:00:00',
        'is_all_day' => false,
    ]);

    $this->deleteJson(
        route(
            'admin.restaurant-schedule.closures.destroy',
            $closure,
        ),
    )->assertNoContent();

    $this->assertSoftDeleted('restaurant_closures', [
        'id' => $closure->id,
    ]);
});
