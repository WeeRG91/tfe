<?php

namespace App\Actions\Admin\User\Queries;

use App\Models\User;

class GetUser
{
    public function execute(User $user): User
    {
        return $user->load([
            'roles',
            'roles.permissions',
            'permissions',
            'loyaltyPointTransactions.order:id,order_number',
            'orders',
        ]);
    }
}
