<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeliveryCompany\StoreCompanyDeliveryDateRequest;
use App\Http\Requests\Admin\DeliveryCompany\UpdateCompanyDeliveryDateAvailabilityRequest;
use App\Models\CompanyDeliveryDate;
use App\Models\DeliveryCompany;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CompanyDeliveryDateController extends Controller
{
    public function store(
        StoreCompanyDeliveryDateRequest $request,
        DeliveryCompany $deliveryCompany,
    ): JsonResponse {
        $deliveryDate = $deliveryCompany
            ->deliveryDates()
            ->create($request->validated());

        return response()->json([
            'data' => [
                'id' => $deliveryDate->id,
            ],
        ], Response::HTTP_CREATED);
    }

    public function updateAvailability(
        UpdateCompanyDeliveryDateAvailabilityRequest $request,
        DeliveryCompany $deliveryCompany,
        CompanyDeliveryDate $deliveryDate,
    ): Response {
        $deliveryDate->update($request->validated());

        return response()->noContent();
    }

    public function destroy(
        DeliveryCompany $deliveryCompany,
        CompanyDeliveryDate $deliveryDate,
    ): Response {
        $deliveryDate->delete();

        return response()->noContent();
    }
}
