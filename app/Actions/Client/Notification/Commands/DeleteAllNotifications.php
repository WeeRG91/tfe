<?php

namespace App\Actions\Client\Notification\Commands;

use App\Models\Notification;
use App\Models\User;

class DeleteAllNotifications
{
    /**
     * @param User $user
     * @return void
     */
    public function execute(User $user): void
    {
        Notification::query()
            ->where('user_id', $user->id)
            ->delete();
    }
}
