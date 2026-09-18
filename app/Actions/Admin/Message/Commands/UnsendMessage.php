<?php

namespace App\Actions\Admin\Message\Commands;

use App\Events\MessageSentBroadcast;
use App\Models\Message;
use App\Models\User;

class UnsendMessage
{
    public function execute(User $user, Message $message): Message
    {
        $message->update([
            'unsent_at' => now(),
        ]);

        $message->refresh();

        $message->load('sender');

        event(new MessageSentBroadcast($message, $user));

        return $message;
    }
}
