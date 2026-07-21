<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Chat\Queries\GetChat;
use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Chat\ChatResource;
use App\Http\Resources\Client\Chat\MessageResource;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{

    /**
     * @param GetChat $getChat
     * @return JsonResponse
     */
    public function getChat(GetChat $getChat): JsonResponse
    {
        $result = $getChat->execute(auth()->user());

        return response()->json([
            'chat' => new ChatResource($result['chat']),
            'messages' => MessageResource::collection($result['messages']),
        ]);
    }
}
