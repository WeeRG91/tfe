<?php

namespace App\Actions\Auth\ChangedEmailVerification\Commands;

use App\Models\User;
use App\Notifications\ChangedEmailVerificationNotification;
use Illuminate\Support\Facades\URL;

class ResendChangedEmailVerificationLink
{
    public function execute(User $user): void
    {
        $url = URL::temporarySignedRoute(
            'verify-changed-email.store',
            now()->addDay(),
            [
                'user' => $user->id,
            ]
        );

        $user->notify(
            (new ChangedEmailVerificationNotification($url))
                ->locale($user->preferredLocale())
        );
    }
}
