<?php

namespace App\Http\Controllers\Admin;

use App\Events\MessageSentBroadcast;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Chat\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;

class MessageController extends Controller
{
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

    public function unsend(Message $message)
    {
        $user = auth()->user();

        if ($message->sender_id !== auth()->user()->id) {
            abort(403);
        }

        $message->update([
            'unsent_at' => now(),
        ]);

        $message->load('sender');

        event(new MessageSentBroadcast($message, $user));

        return response()->json(new MessageResource($message));
    }

    /**
     * @param Chat $chat
     * @return HttpResponse
     */
    public function markAsRead(Chat $chat): HttpResponse
    {
        $user = auth()->user();

        $messages = Message::query()
            ->where('chat_id', $chat->id)
            ->where('is_from_restaurant', false)
            ->whereNull('read_at')
            ->get();

        foreach ($messages as $message) {
            $message->update([
                'read_at' => now(),
            ]);
        }

        $message = Message::query()
            ->where('chat_id', $chat->id)
            ->latest()
            ->first();

        event(new MessageSentBroadcast($message, $user));

        return response()->noContent();
    }

    public function update(Request $request, Message $message)
    {
        $user = auth()->user();

        if ($message->sender_id !== auth()->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $message->update([
            'content' => $validated['content'],
            'edited_at' => now(),
        ]);

        $message->load('sender');

        event(new MessageSentBroadcast($message, $user));

        return response()->json(new MessageResource($message));
    }

    public function destroy(Message $message)
    {
        $user = auth()->user();

        if ($message->sender_id !== auth()->user()->id) {
            abort(403);
        }

        $message->delete();

        $message->load('sender');

        event(new MessageSentBroadcast($message, $user));

        return response()->json(new MessageResource($message));
    }
}
