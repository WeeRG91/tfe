<?php

namespace App\Actions\Admin\Message\Commands;

use App\Events\MessageSentBroadcast;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;

class SendMessage
{
    /**
     * @param User $user
     * @param Chat $chat
     * @param string $content
     * @return Message
     */
    public function execute(
        User $user,
        Chat $chat,
        string $content
    ): Message
    {
        $message = Message::query()->create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'is_from_restaurant' => true,
            'content' => $content,
        ]);

        $chat->update([
            'last_message_at' => now(),
        ]);

        event(new MessageSentBroadcast($message, $user));

        return $message;
    }
}
