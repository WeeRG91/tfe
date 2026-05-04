<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Address\CreateAddressRequest;
use App\Http\Requests\Client\Address\UpdateAddressRequest;
use App\Http\Resources\Client\Address\AddressResource;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function store(CreateAddressRequest $request)
    {
        $validated = $request->validated();

        $user = auth()->user();

        if (!empty($validated['is_default'])) {
            Address::query()
                ->where('user_id', $user->id)
                ->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            ...$validated,
        ]);

        return response()->json([
            'message' => 'Address saved',
            'address' => new AddressResource($address),
        ]);
    }

    public function update(UpdateAddressRequest $request, int $addressId)
    {
        $existingAddress = Address::query()->findOrFail($addressId);
        $user = auth()->user();

        if (empty($existingAddress)) {
            return response()->json([
                'message' => 'Address not found',
                'address' => null,
            ]);
        }

        if ($existingAddress->user_id !== $user->id) {
            return response()->json([
                'message' => 'You cannot edit this address',
                'address' => null,
            ]);
        }

        $validated = $request->validated();

        if (!empty($validated['is_default'])) {
            Address::query()
                ->where('user_id', $user->id)
                ->where('id', '!=', $addressId)
                ->update(['is_default' => false]);
        }

        $existingAddress->update($validated);

        return response()->json([
            'message' => 'Address updated',
            'address' => new AddressResource($existingAddress),
        ]);
    }

    public function destroy(int $addressId)
    {
        $user = auth()->user();

        $address = Address::query()->findOrFail($addressId);

        if ($address->user_id !== $user->id) {
            return response()->json([
                'message' => 'You cannot delete this address',
            ], 403);
        }

        $wasDefault = $address->is_default;
        $address->delete();
        $newDefaultAddress = null;

        if ($wasDefault) {
            $newDefaultAddress = Address::query()
                ->where('user_id', $user->id)
                ->first();

            $newDefaultAddress?->update(['is_default' => true]);
        }

        return response()->json([
            'message' => 'Address deleted',
            'newDefaultAddressId' => $newDefaultAddress?->id,
        ]);
    }
}
