<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Message\Commands\DeleteMessage;
use App\Actions\Admin\Message\Commands\MarkAsRead;
use App\Actions\Admin\Message\Commands\SendMessage;
use App\Actions\Admin\Message\Commands\UnsendMessage;
use App\Actions\Admin\Message\Commands\UpdateMessage;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Chat\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;

class MessageController extends Controller
{
    public function send(
        Request $request,
        Chat $chat,
        SendMessage $sendMessage,
    ): JsonResponse {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $message = $sendMessage->execute(
            auth()->user(),
            $chat,
            $validated['content']
        );

        return response()->json(new MessageResource($message));
    }

    public function unsend(Message $message, UnsendMessage $unsendMessage): JsonResponse
    {
        abort_unless($message->sender_id === auth()->id(), 403);

        $message = $unsendMessage->execute(
            auth()->user(),
            $message
        );

        return response()->json(new MessageResource($message));
    }

    public function markAsRead(Chat $chat, MarkAsRead $markAsRead): HttpResponse
    {
        $markAsRead->execute(
            auth()->user(),
            $chat
        );

        return response()->noContent();
    }

    public function update(
        Request $request,
        Message $message,
        UpdateMessage $updateMessage
    ): JsonResponse {
        abort_unless($message->sender_id === auth()->id(), 403);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $message = $updateMessage->execute(
            auth()->user(),
            $message,
            $validated['content']
        );

        return response()->json(new MessageResource($message));
    }

    public function destroy(Message $message, DeleteMessage $deleteMessage): JsonResponse
    {
        abort_unless($message->sender_id === auth()->id(), 403);

        $message = $deleteMessage->execute(
            auth()->user(),
            $message
        );

        return response()->json(new MessageResource($message));
    }
}
