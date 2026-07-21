<?php

namespace App\Actions\Client\Message\Commands;

use App\Events\MessageSentBroadcast;
use App\Models\Message;
use App\Models\User;

class UnsendMessage
{
    /**
     * @param User $user
     * @param Message $message
     * @return Message
     */
    public function execute(User $user, Message $message): Message
    {
        $message->update([
            'unsent_at' => now(),
        ]);

        $message->load('sender');

        event(new MessageSentBroadcast($message, $user));

        return $message;
    }
}
