<?php

namespace App\Actions\Auth\Account\Commands;

use App\Models\User;
use App\Notifications\AccountActivationNotification;
use Illuminate\Support\Facades\URL;

class SendActivationLink
{
    /**
     * @param User $user
     * @return void
     */
    public function execute(User $user): void
    {
        $url = URL::temporarySignedRoute(
            'activate.show',
            now()->addDay(),
            [
                'user' => $user->id,
            ]
        );

        $user->notify(
            new AccountActivationNotification($url)
        );
    }
}
