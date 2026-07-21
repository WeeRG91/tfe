<?php

namespace App\Actions\Admin\Chat\Queries;

use App\Models\Chat;
use Illuminate\Database\Eloquent\Collection;

class GetChats
{
    /**
     * @return Collection
     */
    public function execute(): Collection
    {
        return Chat::with(['user', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->get();
    }
}
