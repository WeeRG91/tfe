<?php

namespace App\Actions\Admin\Chat\Queries;

use App\Models\Chat;
use Illuminate\Database\Eloquent\Collection;

class GetChatMessages
{
    /**
     * @param Chat $chat
     * @return Collection
     */
    public function execute(Chat $chat): Collection
    {
        return $chat->messages()
            ->with('sender')
            ->withTrashed()
            ->latest()
            ->take(50)
            ->get()
            ->reverse();
    }
}
