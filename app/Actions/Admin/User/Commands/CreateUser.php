<?php

namespace App\Actions\Admin\User\Commands;

use App\Models\User;
use App\Notifications\AccountActivationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Throwable;

class CreateUser
{
    /**
     * @param array $data
     * @return User
     * @throws Throwable
     */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            $user->syncRoles(
                empty($data['role']) ? [] : [$data['role']]
            );

            $user->syncPermissions(
                $data['permissions'] ?? []
            );

            $url = URL::temporarySignedRoute(
                'activate.show',
                now()->addDay(),
                ['user' => $user]
            );

            $user->notify(
                new AccountActivationNotification($url)
            );

            return $user;
        });
    }
}
