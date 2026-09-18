<?php

namespace App\Actions\Client\Message\Commands;

use App\Events\MessageSentBroadcast;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;

class MarkAsRead
{
    public function execute(User $user, Chat $chat): void
    {
        Message::query()
            ->where('chat_id', $chat->id)
            ->where('is_from_restaurant', true)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        $message = Message::query()
            ->where('chat_id', $chat->id)
            ->latest()
            ->first();

        if (! $message) {
            return;
        }

        $message->load('sender');

        event(new MessageSentBroadcast($message, $user));
    }
}
