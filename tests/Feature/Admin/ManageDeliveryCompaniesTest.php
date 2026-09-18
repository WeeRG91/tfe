<?php

use App\Models\DeliveryCompany;
use App\Models\User;
use Carbon\CarbonImmutable;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    config()->set(
        'restaurant.delivery.company.enabled',
        true,
    );

    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $permission = Permission::findOrCreate(
        'admin.access',
        'web',
    );

    $user->givePermissionTo($permission);

    $this->actingAs($user);

    config()->set(
        'restaurant.timezone',
        'Europe/Luxembourg',
    );

    CarbonImmutable::setTestNow(
        CarbonImmutable::parse(
            '2026-09-16 10:00:00',
            'Europe/Luxembourg',
        ),
    );
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

it('creates a delivery company', function () {
    $response = $this->postJson(
        route('admin.delivery-companies.store'),
        [
            'name' => 'BMS',
            'is_active' => true,
            'minimum_advance_days' => 2,
        ],
    );

    $response
        ->assertCreated()
        ->assertJsonStructure([
            'data' => ['id'],
        ]);

    $this->assertDatabaseHas('delivery_companies', [
        'name' => 'BMS',
        'is_active' => true,
        'minimum_advance_days' => 2,
    ]);
});

it('rejects company management when company delivery is disabled', function () {
    config()->set(
        'restaurant.delivery.company.enabled',
        false,
    );

    $this->postJson(
        route('admin.delivery-companies.store'),
        [
            'name' => 'BMS',
            'is_active' => true,
            'minimum_advance_days' => 2,
        ],
    )->assertNotFound();

    $this->assertDatabaseMissing('delivery_companies', [
        'name' => 'BMS',
    ]);
});

it('updates a delivery company', function () {
    $company = DeliveryCompany::query()->create([
        'name' => 'BMS',
        'is_active' => true,
        'minimum_advance_days' => 2,
    ]);

    $this->putJson(
        route('admin.delivery-companies.update', $company),
        [
            'name' => 'BMS Luxembourg',
            'is_active' => false,
            'minimum_advance_days' => 3,
        ],
    )->assertNoContent();

    $this->assertDatabaseHas('delivery_companies', [
        'id' => $company->id,
        'name' => 'BMS Luxembourg',
        'is_active' => false,
        'minimum_advance_days' => 3,
    ]);
});

it('soft deletes a delivery company', function () {
    $company = DeliveryCompany::query()->create([
        'name' => 'BMS',
        'is_active' => true,
        'minimum_advance_days' => 2,
    ]);

    $this->deleteJson(
        route('admin.delivery-companies.destroy', $company),
    )->assertNoContent();

    $this->assertSoftDeleted('delivery_companies', [
        'id' => $company->id,
    ]);
});

it('creates an available delivery date for a company', function () {
    $company = DeliveryCompany::query()->create([
        'name' => 'BMS',
        'is_active' => true,
        'minimum_advance_days' => 2,
    ]);

    $response = $this->postJson(
        route(
            'admin.delivery-companies.dates.store',
            $company,
        ),
        [
            'delivery_date' => '2026-09-18',
            'is_available' => true,
        ],
    );

    $response
        ->assertCreated()
        ->assertJsonStructure([
            'data' => ['id'],
        ]);

    $deliveryDate = $company
        ->deliveryDates()
        ->sole();

    expect($deliveryDate->delivery_date->format('Y-m-d'))
        ->toBe('2026-09-18')
        ->and($deliveryDate->is_available)
        ->toBeTrue();
});

it('rejects a company delivery date without enough advance notice', function () {
    $company = DeliveryCompany::query()->create([
        'name' => 'BMS',
        'is_active' => true,
        'minimum_advance_days' => 2,
    ]);

    $this->postJson(
        route(
            'admin.delivery-companies.dates.store',
            $company,
        ),
        [
            'delivery_date' => '2026-09-17',
            'is_available' => true,
        ],
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('delivery_date');

    $this->assertDatabaseMissing('company_delivery_dates', [
        'delivery_company_id' => $company->id,
        'delivery_date' => '2026-09-17',
    ]);
});

it('changes the availability of a company delivery date', function () {
    $company = DeliveryCompany::query()->create([
        'name' => 'BMS',
        'is_active' => true,
        'minimum_advance_days' => 2,
    ]);

    $deliveryDate = $company->deliveryDates()->create([
        'delivery_date' => '2026-09-18',
        'is_available' => true,
    ]);

    $this->patchJson(
        route(
            'admin.delivery-companies.dates.availability',
            [$company, $deliveryDate],
        ),
        [
            'is_available' => false,
        ],
    )->assertNoContent();

    expect($deliveryDate->refresh()->is_available)
        ->toBeFalse();
});

it('deletes a company delivery date', function () {
    $company = DeliveryCompany::query()->create([
        'name' => 'BMS',
        'is_active' => true,
        'minimum_advance_days' => 2,
    ]);

    $deliveryDate = $company->deliveryDates()->create([
        'delivery_date' => '2026-09-18',
        'is_available' => true,
    ]);

    $this->deleteJson(
        route(
            'admin.delivery-companies.dates.destroy',
            [$company, $deliveryDate],
        ),
    )->assertNoContent();

    $this->assertDatabaseMissing('company_delivery_dates', [
        'id' => $deliveryDate->id,
    ]);
});

it('does not manage a delivery date through another company', function () {
    $firstCompany = DeliveryCompany::query()->create([
        'name' => 'BMS',
        'is_active' => true,
        'minimum_advance_days' => 2,
    ]);

    $secondCompany = DeliveryCompany::query()->create([
        'name' => 'Another company',
        'is_active' => true,
        'minimum_advance_days' => 2,
    ]);

    $deliveryDate = $secondCompany->deliveryDates()->create([
        'delivery_date' => '2026-09-18',
        'is_available' => true,
    ]);

    $this->patchJson(
        route(
            'admin.delivery-companies.dates.availability',
            [$firstCompany, $deliveryDate],
        ),
        [
            'is_available' => false,
        ],
    )->assertNotFound();

    expect($deliveryDate->refresh()->is_available)
        ->toBeTrue();
});

it('shows delivery companies and their dates', function () {
    $company = DeliveryCompany::query()->create([
        'name' => 'BMS',
        'is_active' => true,
        'minimum_advance_days' => 2,
    ]);

    $company->deliveryDates()->create([
        'delivery_date' => '2026-09-18',
        'is_available' => true,
    ]);

    $this->get(
        route('admin.delivery-companies.index'),
    )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/delivery-companies/Index')
                ->where('features.companyDelivery', true)
                ->where(
                    'defaultMinimumAdvanceDays',
                    2,
                )
                ->has('companies', 1)
                ->where(
                    'companies.0.id',
                    $company->id,
                )
                ->where(
                    'companies.0.name',
                    'BMS',
                )
                ->where(
                    'companies.0.is_active',
                    true,
                )
                ->where(
                    'companies.0.minimum_advance_days',
                    2,
                )
                ->where(
                    'companies.0.dates.0.delivery_date',
                    '2026-09-18',
                )
                ->where(
                    'companies.0.dates.0.is_available',
                    true,
                )
                ->where(
                    'companies.0.earliest_delivery_date',
                    '2026-09-18',
                ),
        );
});
