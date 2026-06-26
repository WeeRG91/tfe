<?php

namespace App\Http\Controllers\Admin;

use App\Events\MessageSentBroadcast;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Chat\ChatResource;
use App\Http\Resources\Admin\Chat\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function chats()
    {
        return Inertia::render('admin/chat/Chat');
    }

    public function getChats()
    {
        $chats = Chat::with(['user', 'latestMessage'])
            ->whereNotNull('last_message_at')
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

    public function send(Request $request, Chat $chat)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $user = auth()->user();

        $message = Message::query()->create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'is_from_restaurant' => true,
            'content' => $validated['content'],
        ]);

        $chat->update([
            'last_message_at' => now(),
        ]);

        event(new MessageSentBroadcast($message, $user));

        return response()->json(new MessageResource($message));
    }

    /**
     * @param int $chatId
     * @return HttpResponse
     */
    public function markAsRead(int $chatId): HttpResponse
    {
        $user = auth()->user();

        $messages = Message::query()
            ->where('chat_id', $chatId)
            ->where('is_from_restaurant', false)
            ->whereNull('read_at')
            ->get();

        foreach ($messages as $message) {
            $message->update([
                'read_at' => now(),
            ]);
        }

        $message = Message::query()
            ->where('chat_id', $chatId)
            ->latest()
            ->first();

        event(new MessageSentBroadcast($message, $user));

        return response()->noContent();
    }
}
