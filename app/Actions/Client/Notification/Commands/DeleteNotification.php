<?php

namespace App\Actions\Client\Notification\Commands;

use App\Models\Notification;

class DeleteNotification
{
    /**
     * @param Notification $notification
     * @return void
     */
    public function execute(Notification $notification): void
    {
        $notification->delete();
    }
}
