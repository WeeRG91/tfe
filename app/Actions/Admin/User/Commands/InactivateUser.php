<?php

namespace App\Actions\Admin\User\Commands;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class InactivateUser
{
    /**
     * @param User $user
     * @return void
     */
    public function execute(User $user): void
    {
        if ($user->hasRole('Super Admin')) {
            throw ValidationException::withMessages([
                'user' => __('messages.errors.cannot_inactivate_super_admin'),
            ]);
        }

        if ($user->trashed()) {
            throw ValidationException::withMessages([
                'user' => __('messages.errors.user_already_inactive'),
            ]);
        }

        $user->forceFill([
            'email_verified_at' => null,
        ]);

        $user->save();

        $user->delete();
    }
}
