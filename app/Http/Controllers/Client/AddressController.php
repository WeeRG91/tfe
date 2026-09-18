<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Address\Commands\CreateAddress;
use App\Actions\Client\Address\Commands\DeleteAddress;
use App\Actions\Client\Address\Commands\UpdateAddress;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Address\CreateAddressRequest;
use App\Http\Requests\Client\Address\UpdateAddressRequest;
use App\Http\Resources\Client\Address\AddressResource;
use Illuminate\Http\JsonResponse;

class AddressController extends Controller
{
    public function store(
        CreateAddressRequest $request,
        CreateAddress $createAddress
    ): JsonResponse {
        $result = $createAddress->execute($request->validated());

        return response()->json([
            'message' => $result['message'],
            'address' => new AddressResource($result['address']),
        ]);
    }

    public function update(
        UpdateAddressRequest $request,
        int $addressId,
        UpdateAddress $updateAddress
    ): JsonResponse {
        $result = $updateAddress->execute(
            $addressId,
            $request->validated()
        );

        return response()->json([
            'message' => $result['message'],
            'address' => $result['address'],
        ]);
    }

    public function destroy(
        int $addressId,
        DeleteAddress $deleteAddress
    ): JsonResponse {
        $result = $deleteAddress->execute($addressId);

        return response()->json([
            'message' => $result['message'],
            'newDefaultAddressId' => $result['address']?->id,
        ]);
    }
}
