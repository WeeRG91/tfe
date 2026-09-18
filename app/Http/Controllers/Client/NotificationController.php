<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Notification\Commands\DeleteAllNotifications;
use App\Actions\Client\Notification\Commands\DeleteNotification;
use App\Actions\Client\Notification\Commands\MarkAllAsRead;
use App\Actions\Client\Notification\Commands\MarkAsRead;
use App\Actions\Client\Notification\Queries\GetNotifications;
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

    public function getNotifications(Request $request, GetNotifications $getNotifications): JsonResponse
    {
        $notifications = $getNotifications->execute(
            auth()->user(),
            $request->input('filter', 'all')
        );

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

    public function markAsRead(Notification $notification, MarkAsRead $markAsRead): JsonResponse
    {
        $this->authorize('update', $notification);

        $notification = $markAsRead->execute($notification);

        return response()->json(new NotificationResource($notification));
    }

    public function markAllAsRead(MarkAllAsRead $markAllAsRead): HttpResponse
    {
        $markAllAsRead->execute(auth()->user());

        return response()->noContent();
    }

    public function delete(Notification $notification, DeleteNotification $deleteNotification): HttpResponse
    {
        $this->authorize('delete', $notification);

        $deleteNotification->execute($notification);

        return response()->noContent();
    }

    public function deleteAll(DeleteAllNotifications $deleteAllNotifications): HttpResponse
    {
        $deleteAllNotifications->execute(auth()->user());

        return response()->noContent();
    }
}
