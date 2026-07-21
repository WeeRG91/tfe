<?php

namespace App\Actions\Client\Notification\Queries;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;

class GetNotifications
{
    /**
     * @param User $user
     * @param string $filter
     * @return CursorPaginator
     */
    public function execute(User $user, string $filter = 'all'): CursorPaginator
    {
        return Notification::query()
            ->where('user_id', $user->id)
            ->when($filter !== 'all', function ($query) use ($filter) {
                match ($filter) {
                    'read' => $query->whereNotNull('read_at'),
                    'unread' => $query->whereNull('read_at'),
                    default => null,
                };
            })
            ->latest()
            ->cursorPaginate(10);
    }
}
