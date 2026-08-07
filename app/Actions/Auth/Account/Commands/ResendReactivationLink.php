<?php

namespace App\Actions\Auth\Account\Commands;

use App\Models\User;
use App\Notifications\ReactivateAccountNotification;
use Illuminate\Support\Facades\URL;

class ResendReactivationLink
{
    /**
     * @param User $user
     * @return void
     */
    public function execute(User $user): void
    {
        $url = URL::temporarySignedRoute(
            'reactivate.reactivate',
            now()->addDay(),
            [
                'user' => $user->id,
            ]
        );

        $user->notify(
            (new ReactivateAccountNotification($url))
                ->locale($user->preferredLocale())
        );
    }
}
