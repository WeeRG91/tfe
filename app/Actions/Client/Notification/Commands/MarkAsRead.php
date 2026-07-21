<?php

namespace App\Actions\Client\Notification\Commands;

use App\Models\Notification;

class MarkAsRead
{
    /**
     * @param Notification $notification
     * @return Notification
     */
    public function execute(Notification $notification): Notification
    {
        if (!$notification->read_at) {
            $notification->update([
                'read_at' => now(),
            ]);
        }

        return $notification;
    }
}
