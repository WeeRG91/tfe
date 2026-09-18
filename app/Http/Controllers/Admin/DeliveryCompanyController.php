<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeliveryCompany\StoreDeliveryCompanyRequest;
use App\Http\Requests\Admin\DeliveryCompany\UpdateDeliveryCompanyRequest;
use App\Models\DeliveryCompany;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

class DeliveryCompanyController extends Controller
{
    public function index(): InertiaResponse
    {
        $today = CarbonImmutable::now(
            config('restaurant.timezone'),
        )->startOfDay();

        $companies = DeliveryCompany::query()
            ->with([
                'deliveryDates' => fn ($query) => $query
                    ->orderBy('delivery_date'),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (DeliveryCompany $company) => [
                'id' => $company->id,
                'name' => $company->name,
                'is_active' => $company->is_active,
                'minimum_advance_days' => $company->minimum_advance_days,
                'dates' => $company->deliveryDates
                    ->map(fn ($date) => [
                        'id' => $date->id,
                        'delivery_date' => $date
                            ->delivery_date
                            ->format('Y-m-d'),
                        'is_available' => $date->is_available,
                    ])
                    ->values(),
                'earliest_delivery_date' => $today
                    ->addDays($company->minimum_advance_days)
                    ->format('Y-m-d'),
            ])
            ->values();

        return Inertia::render(
            'admin/delivery-companies/Index',
            [
                'companies' => $companies,
                'defaultMinimumAdvanceDays' => config(
                    'restaurant.delivery.company.default_minimum_advance_days',
                ),
            ],
        );
    }

    public function store(
        StoreDeliveryCompanyRequest $request,
    ): JsonResponse {
        $company = DeliveryCompany::query()->create($request->validated());

        return response()->json([
            'data' => [
                'id' => $company->id,
            ],
        ], Response::HTTP_CREATED);
    }

    public function update(
        UpdateDeliveryCompanyRequest $request,
        DeliveryCompany $deliveryCompany,
    ): Response {
        $deliveryCompany->update($request->validated());

        return response()->noContent();
    }

    public function destroy(
        DeliveryCompany $deliveryCompany,
    ): Response {
        $deliveryCompany->delete();

        return response()->noContent();
    }
}
