<?php

namespace App\Http\Resources\Admin\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $name = $this->sender->id === auth()->user()->id ? 'You' : $this->sender->name;

        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'is_from_restaurant' => $this->is_from_restaurant,
            'content' => $this->unsent_at
                ? $name . ' unsent message'
                : ($this->deleted_at
                    ? $name . ' deleted message'
                    : $this->content),
            'read_at' => $this->read_at,
            'edited_at' => $this->edited_at,
            'unsent_at' => $this->unsent_at,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
