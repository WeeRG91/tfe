<?php

namespace App\Actions\Client\Address\Commands;

use App\Models\Address;

class DeleteAddress
{
    /**
     * @param int $addressId
     * @return array
     */
    public function execute(int $addressId): array
    {
        $address = Address::query()->findOrFail($addressId);
        $user  = auth()->user();

        if ($address->user_id !== $user->id) {
            return [
                'message' => 'You cannot delete this address',
                'address' => null,
            ];
        }

        $wasDefault = $address->is_default;

        $address->delete();

        if ($wasDefault) {
            $newDefaultAddress = Address::query()
                ->where('user_id', $user->id)
                ->first();

            $newDefaultAddress?->update(['is_default' => true]);

            return [
                'message' => 'Address deleted',
                'address' => $newDefaultAddress,
            ];
        }

        return [
            'message' => 'Address deleted',
            'address' => null,
        ];
    }
}
