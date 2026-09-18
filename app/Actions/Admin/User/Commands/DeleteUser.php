<?php

namespace App\Actions\Admin\User\Commands;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class DeleteUser
{
    /**
     * @throws Throwable
     */
    public function execute(User $user): void
    {
        if ($user->hasRole('Super Admin')) {
            throw ValidationException::withMessages([
                'user' => "You can't delete Super Admin.",
            ]);
        }

        DB::transaction(function () use ($user) {

            $user->syncRoles([]);

            $user->syncPermissions([]);

            $user->forceDelete();
        });
    }
}
