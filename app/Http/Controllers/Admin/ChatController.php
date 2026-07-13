<?php

namespace App\Http\Controllers\Admin;

use App\Events\MessageSentBroadcast;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Chat\ChatResource;
use App\Http\Resources\Admin\Chat\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function create(User $user)
    {
        $chat = Chat::query()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        $chat->load('user', 'latestMessage');

        return response()->json(new ChatResource($chat));
    }

    public function chats()
    {
        return Inertia::render('admin/chat/Chat');
    }

    public function getChats()
    {
        $chats = Chat::with(['user', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->get();

        return response()->json(ChatResource::collection($chats)->collection);
    }

    public function getChatMessages(Chat $chat)
    {
        return response()->json(
            MessageResource::collection($chat->messages()->with('sender')->withTrashed()->latest()->take(50)->get()->reverse())
        );
    }
}
