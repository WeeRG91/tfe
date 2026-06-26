<?php

namespace App\Http\Resources\Client\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'last_message_at' => $this->last_message_at,
            'created_at' => $this->created_at,
            'latest_message' => $this->whenLoaded('latestMessage', fn () => new MessageResource($this->latestMessage)),
        ];
    }
}
