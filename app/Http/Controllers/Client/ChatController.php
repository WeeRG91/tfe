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
}
