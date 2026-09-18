<?php

namespace App\Actions\Admin\User\Commands;

use App\Models\User;
use App\Notifications\ChangedEmailVerificationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Throwable;

class UpdateUser
{
    /**
     * @throws Throwable
     */
    public function execute(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) {

            $emailChanged = $user->email !== $data['email'];

            $user->name = $data['name'];
            $user->email = $data['email'];

            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            if ($emailChanged) {
                $user->email_verified_at = null;
            }

            $user->save();

            $user->syncRoles(
                empty($data['role']) ? [] : [$data['role']]
            );

            $user->syncPermissions(
                $data['permissions'] ?? []
            );

            if ($emailChanged) {

                $url = URL::temporarySignedRoute(
                    'verify-changed-email.store',
                    now()->addDay(),
                    ['user' => $user]
                );

                $user->notify(
                    new ChangedEmailVerificationNotification($url)
                );
            }
        });
    }
}
