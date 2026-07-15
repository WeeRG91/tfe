<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Notification\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function myNotifications()
    {
        return Inertia::render('client/MyNotifications');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotifications(Request $request): JsonResponse
    {
        $filter = $request->filter;

        $notifications = Notification::query()
            ->where('user_id', auth()->id())
            ->when($filter !== 'all', function ($query) use ($filter) {
                match ($filter) {
                    'read' => $query->whereNotNull('read_at'),
                    'unread' => $query->whereNull('read_at'),
                };
            })
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(10);

        return response()->json([
            'data' => NotificationResource::collection($notifications)->collection,
            'path' => $notifications->path(),
            'per_page' => $notifications->perPage(),
            'next_cursor' => $notifications->nextCursor()?->encode(),
            'next_page_url' => $notifications->nextPageUrl(),
            'prev_cursor' => $notifications->previousCursor()?->encode(),
            'prev_page_url' => $notifications->previousPageUrl(),
        ]);
    }

    /**
     * @param Notification $notification
     * @return JsonResponse
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json(new NotificationResource($notification));
    }

    /**
     * @return HttpResponse
     */
    public function markAllAsRead(): HttpResponse
    {
        Notification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->noContent();
    }

    /**
     * @param Notification $notification
     * @return HttpResponse
     */
    public function delete(Notification $notification): HttpResponse
    {
        $notification->delete();

        return response()->noContent();
    }

    /**
     * @return HttpResponse
     */
    public function deleteAll(): HttpResponse
    {
        Notification::query()
            ->where('user_id', auth()->id())
            ->delete();

        return response()->noContent();
    }
}
