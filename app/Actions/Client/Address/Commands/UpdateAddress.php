<?php

namespace App\Actions\Client\Address\Commands;

use App\Http\Resources\Client\Address\AddressResource;
use App\Models\Address;

class UpdateAddress
{
    public function execute(int $addressId, array $data): array
    {
        $existingAddress = Address::query()->findOrFail($addressId);
        $user = auth()->user();

        if (empty($existingAddress)) {
            return [
                'message' => __('messages.addresses.not_found'),
                'address' => null,
            ];
        }

        if ($existingAddress->user_id !== $user->id) {
            return [
                'message' => __('messages.addresses.cannot_edit'),
                'address' => null,
            ];
        }

        if (! empty($data['is_default'])) {
            Address::query()
                ->where('user_id', $user->id)
                ->where('id', '!=', $addressId)
                ->update(['is_default' => false]);
        }

        $existingAddress->update($data);

        return [
            'message' => __('messages.addresses.updated'),
            'address' => new AddressResource($existingAddress),
        ];
    }
}
