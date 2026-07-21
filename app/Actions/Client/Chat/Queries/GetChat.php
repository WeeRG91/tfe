<?php

namespace App\Actions\Client\Chat\Queries;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;

class GetChat
{
    /**
     * @param User $user
     * @return array
     */
    public function execute(User $user): array
    {
        $chat = Chat::query()
            ->firstOrCreate([
                'user_id' => $user->id,
            ])
            ->load('user', 'latestMessage');

        $messages = Message::query()
            ->where('chat_id', $chat->id)
            ->with('sender')
            ->withTrashed()
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        return [
            'chat' => $chat,
            'messages' => $messages,
        ];
    }
}
