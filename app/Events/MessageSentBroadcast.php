<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSentBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Message $message,
        public User $user
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->chat_id),
            new PrivateChannel('admin.chats'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'chat_id' => $this->message->chat_id,
            'sender_name' => $this->message->sender->name,
            'is_from_restaurant' => $this->message->is_from_restaurant,
            'content' => $this->message->content,
            'read_at' => $this->message->read_at,
            'edited_at' => $this->message->edited_at,
            'unsent_at' => $this->message->unsent_at,
            'created_at' => $this->message->created_at,
            'deleted_at' => $this->message->deleted_at,
        ];
    }

    public function broadcastAs(): string
    {
        return 'message-sent';
    }
}
