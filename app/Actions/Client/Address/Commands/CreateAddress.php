<?php

namespace App\Actions\Client\Address\Commands;

use App\Models\Address;

class CreateAddress
{
    /**
     * @param array $data
     * @return Address
     */
    public function execute(array $data): Address
    {
        $user  = auth()->user();

        if (!empty($data['is_default'])) {
            Address::query()
                ->where('user_id', $user->id)
                ->update(['is_default' => false]);
        }

        return $user->addresses()->create($data);
    }
}
