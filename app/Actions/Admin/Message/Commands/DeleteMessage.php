<?php

namespace App\Actions\Admin\Message\Commands;

use App\Events\MessageSentBroadcast;
use App\Models\Message;
use App\Models\User;

class DeleteMessage
{
    /**
     * @param User $user
     * @param Message $message
     * @return Message
     */
    public function execute(User $user, Message $message): Message
    {
        $message->delete();

        $message->refresh();

        $message->load('sender');

        event(new MessageSentBroadcast($message, $user));

        return $message;
    }
}
