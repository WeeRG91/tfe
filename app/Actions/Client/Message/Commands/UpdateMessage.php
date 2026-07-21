<?php

namespace App\Actions\Client\Message\Commands;

use App\Events\MessageSentBroadcast;
use App\Models\Message;
use App\Models\User;

class UpdateMessage
{
    /**
     * @param User $user
     * @param Message $message
     * @param string $content
     * @return Message
     */
    public function execute(
        User $user,
        Message $message,
        string $content
    ): Message
    {
        $message->update([
            'content' => $content,
            'edited_at' => now(),
        ]);

        $message->load('sender');

        event(new MessageSentBroadcast($message, $user));

        return $message;
    }
}
