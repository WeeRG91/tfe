<?php

namespace App\Actions\Client\Message\Commands;

use App\Events\MessageSentBroadcast;
use App\Models\Message;
use App\Models\User;

class DeleteMessage
{
    public function execute(User $user, Message $message): Message
    {
        $message->delete();

        $message->refresh();

        $message->load('sender');

        event(new MessageSentBroadcast($message, $user));

        return $message;
    }
}
