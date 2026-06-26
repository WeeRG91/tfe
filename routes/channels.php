<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::query()->find($chatId);

    if (!$chat) return false;

    return (int) $user->id === (int) $chat->user_id || $user->id === 1;
});

Broadcast::channel('admin.chats', function ($user) {
    return $user->id === 1;
});
