<?php

namespace App\Actions\Client\Notification\Commands;

use App\Models\Notification;
use App\Models\User;

class MarkAllAsRead
{
    public function execute(User $user): void
    {
        Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
