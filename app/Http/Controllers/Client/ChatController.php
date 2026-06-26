<?php

namespace App\Http\Controllers\Client;

use App\Events\MessageSentBroadcast;
use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Chat\ChatResource;
use App\Http\Resources\Client\Chat\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;

class ChatController extends Controller
{

    /**
     * @return JsonResponse
     */
    public function getChat(): JsonResponse
    {
        $user = auth()->user();

        $chat = Chat::query()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        return response()->json([
            'chat' => new ChatResource($chat->load('user', 'latestMessage')),
            'messages' => MessageResource::collection($chat->messages()->with('sender')->withTrashed()->latest()->take(50)->get()->reverse()),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $user = auth()->user();

        $chat = Chat::query()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        $message = Message::query()->create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'is_from_restaurant' => false,
            'content' => $validated['content'],
        ]);

        $chat->update([
            'last_message_at' => now(),
        ]);

        $message->load('sender');

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
            ->where('is_from_restaurant', true)
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

        $message->load('sender');

        event(new MessageSentBroadcast($message, $user));

        return response()->noContent();
    }
}
