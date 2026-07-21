<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Chat\Commands\CreateChat;
use App\Actions\Admin\Chat\Queries\GetChatMessages;
use App\Actions\Admin\Chat\Queries\GetChats;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Chat\ChatResource;
use App\Http\Resources\Admin\Chat\MessageResource;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    /**
     * @param User $user
     * @param CreateChat $createChat
     * @return JsonResponse
     */
    public function create(User $user, CreateChat $createChat): JsonResponse
    {
        $chat = $createChat->execute($user);

        return response()->json(new ChatResource($chat));
    }

    /**
     * @return Response
     */
    public function chats(): Response
    {
        return Inertia::render('admin/chat/Chat');
    }

    /**
     * @param GetChats $getChats
     * @return JsonResponse
     */
    public function getChats(GetChats $getChats): JsonResponse
    {
        $chats = $getChats->execute();

        return response()->json(ChatResource::collection($chats));
    }

    /**
     * @param Chat $chat
     * @param GetChatMessages $getChatMessages
     * @return JsonResponse
     */
    public function getChatMessages(Chat $chat, GetChatMessages $getChatMessages): JsonResponse
    {
        return response()->json(
            MessageResource::collection($getChatMessages->execute($chat))
        );
    }
}
