<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->data ?? [];

        if ($this->notifiable instanceof Order) {
            $data['order_id'] ??= $this->notifiable->id;
            $data['order_number'] ??= $this->notifiable->order_number;
            $data['order_type'] ??= $this->notifiable->type->key();
        }

        return [
            'id' => $this->id,
            'type' => [
                'value' => $this->type->value,
                'key' => strtolower($this->type->name),
                'label' => $this->type->label(),
            ],
            'title' => $this->title,
            'message' => $this->message,
            'data' => $data,
            'is_read' => $this->read_at !== null,
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
