<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeliveryCompany\StoreDeliveryCompanyRequest;
use App\Http\Requests\Admin\DeliveryCompany\UpdateDeliveryCompanyRequest;
use App\Models\DeliveryCompany;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DeliveryCompanyController extends Controller
{
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
