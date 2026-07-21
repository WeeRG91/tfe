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
                'user' => "You can't inactivate Super Admin."
            ]);
        }

        if ($user->trashed()) {
            throw ValidationException::withMessages([
                'user' => 'User is already inactive.'
            ]);
        }

        $user->forceFill([
            'email_verified_at' => null,
        ]);

        $user->save();

        $user->delete();
    }
}
