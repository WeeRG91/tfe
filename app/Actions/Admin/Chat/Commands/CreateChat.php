<?php

namespace App\Actions\Admin\Chat\Commands;

use App\Models\Chat;
use App\Models\User;

class CreateChat
{
    public function execute(User $user): Chat
    {
        $chat = Chat::query()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        $chat->load('user', 'latestMessage');

        return $chat;
    }
}
