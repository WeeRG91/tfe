<?php

namespace App\Actions\Client\Notification\Commands;

use App\Models\Notification;

class DeleteNotification
{
    public function execute(Notification $notification): void
    {
        $notification->delete();
    }
}
