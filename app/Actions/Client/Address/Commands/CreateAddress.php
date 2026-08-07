<?php

namespace App\Actions\Client\Address\Commands;

use App\Models\Address;

class CreateAddress
{
    /**
     * @param array $data
     * @return array
     */
    public function execute(array $data): array
    {
        $user  = auth()->user();

        if (!empty($data['is_default'])) {
            Address::query()
                ->where('user_id', $user->id)
                ->update(['is_default' => false]);
        }

        return [
            'message' => __('messages.addresses.added'),
            'address' => $user->addresses()->create($data),
        ];
    }
}
