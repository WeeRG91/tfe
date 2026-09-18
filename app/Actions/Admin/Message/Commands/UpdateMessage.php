<?php

namespace App\Actions\Admin\Message\Commands;

use App\Events\MessageSentBroadcast;
use App\Models\Message;
use App\Models\User;

class UpdateMessage
{
    public function execute(
        User $user,
        Message $message,
        string $content
    ): Message {
        $message->update([
            'content' => $content,
            'edited_at' => now(),
        ]);

        $message->load('sender');

        event(new MessageSentBroadcast($message, $user));

        return $message;
    }
}
