<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Message\Commands\DeleteMessage;
use App\Actions\Admin\Message\Commands\MarkAsRead;
use App\Actions\Admin\Message\Commands\SendMessage;
use App\Actions\Admin\Message\Commands\UnsendMessage;
use App\Actions\Admin\Message\Commands\UpdateMessage;
use App\Events\MessageSentBroadcast;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Chat\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;

class MessageController extends Controller
{
    /**
     * @param Request $request
     * @param Chat $chat
     * @param SendMessage $sendMessage
     * @return JsonResponse
     */
    public function send(
        Request $request,
        Chat $chat,
        SendMessage $sendMessage,
    ): JsonResponse
    {
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

    /**
     * @param Message $message
     * @param UnsendMessage $unsendMessage
     * @return JsonResponse
     */
    public function unsend(Message $message, UnsendMessage $unsendMessage): JsonResponse
    {
        if ($message->sender_id !== auth()->user()->id) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        $message = $unsendMessage->execute(
            auth()->user(),
            $message
        );

        return response()->json(new MessageResource($message));
    }

    /**
     * @param Chat $chat
     * @param MarkAsRead $markAsRead
     * @return HttpResponse
     */
    public function markAsRead(Chat $chat, MarkAsRead $markAsRead): HttpResponse
    {
        $markAsRead->execute(
            auth()->user(),
            $chat
        );

        return response()->noContent();
    }

    /**
     * @param Request $request
     * @param Message $message
     * @param UpdateMessage $updateMessage
     * @return JsonResponse
     */
    public function update(
        Request $request,
        Message $message,
        UpdateMessage $updateMessage
    ): JsonResponse
    {
        if ($message->sender_id !== auth()->user()->id) {
            return response()->json([
                'error' => 'Unauthorized.'
            ], 403);
        }

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

    /**
     * @param Message $message
     * @param DeleteMessage $deleteMessage
     * @return JsonResponse
     */
    public function destroy(Message $message, DeleteMessage $deleteMessage): JsonResponse
    {
        if ($message->sender_id !== auth()->user()->id) {
            return response()->json([
                'error' => 'Unauthorized.'
            ], 403);
        }

        $message = $deleteMessage->execute(
            auth()->user(),
            $message
        );

        return response()->json(new MessageResource($message));
    }
}
