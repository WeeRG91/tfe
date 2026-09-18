<?php

namespace App\Actions\Client\Message\Commands;

use App\Events\MessageSentBroadcast;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;

class SendMessage
{
    public function execute(User $user, string $content): Message
    {
        $chat = Chat::query()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        $message = Message::query()->create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'is_from_restaurant' => false,
            'content' => $content,
        ]);

        $chat->update([
            'last_message_at' => now(),
        ]);

        $message->load('sender');

        event(new MessageSentBroadcast($message, $user));

        return $message;
    }
}
