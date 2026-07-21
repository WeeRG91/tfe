<?php

namespace App\Actions\Admin\Message\Commands;

use App\Events\MessageSentBroadcast;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;

class MarkAsRead
{
    /**
     * @param User $user
     * @param Chat $chat
     * @return void
     */
    public function execute(User $user, Chat $chat): void
    {
        Message::query()
            ->where('chat_id', $chat->id)
            ->where('is_from_restaurant', false)
            ->whereNull('read_at')
            ->update([
                'read_at' => now()
            ]);

        $message = Message::query()
            ->where('chat_id', $chat->id)
            ->latest()
            ->first();

        event(new MessageSentBroadcast($message, $user));
    }
}
