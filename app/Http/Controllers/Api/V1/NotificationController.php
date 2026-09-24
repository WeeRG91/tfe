<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Client\Notification\Commands\MarkAllAsRead;
use App\Actions\Client\Notification\Commands\MarkAsRead;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $unreadCount = Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        $notifications = Notification::query()
            ->with('notifiable')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate(10)
            ->withQueryString();

        return NotificationResource::collection($notifications)
            ->additional([
                'unread_count' => $unreadCount,
            ]);
    }

    public function markAsRead(
        Request $request,
        int $notificationId,
        MarkAsRead $markAsRead,
    ): JsonResponse {
        $notification = Notification::query()
            ->where('user_id', $request->user()->id)
            ->with('notifiable')
            ->findOrFail($notificationId);

        $notification = $markAsRead->execute($notification);

        return response()->json([
            'data' => (new NotificationResource($notification))
                ->resolve($request),
        ]);
    }

    public function markAllAsRead(Request $request, MarkAllAsRead $markAllAsRead): Response
    {
        $markAllAsRead->execute($request->user());

        return response()->noContent();
    }
}
