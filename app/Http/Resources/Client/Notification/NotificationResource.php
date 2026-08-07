<?php

namespace App\Http\Resources\Client\Notification;

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

        if (
            $this->notifiable instanceof Order
            && !isset($data['order_type'])
        ) {
            $data['order_id'] ??= $this->notifiable->id;
            $data['order_number'] ??= $this->notifiable->order_number;
            $data['order_type'] = $this->notifiable->type->key();
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'notifiable_id' => $this->notifiable_id,
            'notifiable_type' => $this->notifiable_type
                ? strtolower(class_basename($this->notifiable_type))
                : null,
            'type' => $this->type->value,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $data,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
        ];
    }
}
