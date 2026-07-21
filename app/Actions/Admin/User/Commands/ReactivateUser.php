<?php

namespace App\Actions\Admin\User\Commands;

use App\Models\User;
use App\Notifications\ReactivateAccountNotification;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class ReactivateUser
{
    /**
     * @param User $user
     * @return void
     */
    public function execute(User $user): void
    {
        if (!$user->trashed()) {
            throw ValidationException::withMessages([
                'user' => 'User is already active.'
            ]);
        }

        $url = URL::temporarySignedRoute(
            'reactivate.reactivate',
            now()->addDay(),
            [
                'user' => $user
            ]
        );

        $user->notify(
            new ReactivateAccountNotification($url)
        );
    }
}
