<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Address\StoreAddressRequest;
use App\Http\Requests\Api\V1\Address\UpdateAddressRequest;
use App\Http\Resources\Api\V1\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class AddressController extends Controller
{
    /**
     * @throws Throwable
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $address = DB::transaction(function () use ($user, $data): Address {
            $existingAddress = $user->addresses()
                ->lockForUpdate()
                ->get();

            $makeDefault = $existingAddress->isEmpty()
                || ($data['is_default'] ?? false);

            if ($makeDefault && $existingAddress->isNotEmpty()) {
                $user->addresses()->update([
                    'is_default' => false,
                ]);
            }

            $data['is_default'] = $makeDefault;

            return $user->addresses()->create($data);
        });

        return response()->json([
            'data' => new AddressResource($address),
        ], 201);
    }

    /**
     * @throws Throwable
     */
    public function update(
        UpdateAddressRequest $request,
        int $addressId,
    ): JsonResponse {
        $user = $request->user();
        $data = $request->validated();

        $address = DB::transaction(function () use ($user, $data, $addressId): Address {
            $address = $user->addresses()
                ->whereKey($addressId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($data['is_default'] ?? false) {
                $user->addresses()
                    ->whereKeyNot($addressId)
                    ->update([
                        'is_default' => false,
                    ]);
            }

            $address->update($data);

            return $address->refresh();
        });

        return response()->json([
            'data' => new AddressResource($address),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function destroy(Request $request, int $addressId): JsonResponse
    {
        $result = DB::transaction(function () use ($request, $addressId): array {
            $user = $request->user();

            $address = $user->addresses()
                ->whereKey($addressId)
                ->lockForUpdate()
                ->firstOrFail();

            $wasDefault = $address->is_default;
            $deletedId = $address->id;

            $address->delete();

            if ($wasDefault) {
                /** @var Address|null $newDefaultAddress */
                $newDefaultAddress = $user->addresses()
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->first();

                $newDefaultAddress?->update([
                    'is_default' => true,
                ]);
            } else {
                $newDefaultAddress = $user->addresses()
                    ->where('is_default', true)
                    ->first();
            }

            return [
                'deleted_id' => $deletedId,
                'new_default_address_id' => $newDefaultAddress?->id,
            ];
        });

        return response()->json([
            'data' => $result,
        ]);
    }
}
