<?php

namespace App\Actions\Client\Address\Commands;

use App\Models\Address;

class DeleteAddress
{
    public function execute(int $addressId): array
    {
        $address = Address::query()->findOrFail($addressId);
        $user = auth()->user();

        if ($address->user_id !== $user->id) {
            return [
                'message' => __('messages.addresses.cannot_delete'),
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
                'message' => __('messages.addresses.deleted'),
                'address' => $newDefaultAddress,
            ];
        }

        return [
            'message' => __('messages.addresses.deleted'),
            'address' => null,
        ];
    }
}
