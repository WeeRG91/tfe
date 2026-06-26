<?php

namespace App\Http\Controllers\Admin;

use App\Events\MessageSentBroadcast;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Chat\MessageResource;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
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
