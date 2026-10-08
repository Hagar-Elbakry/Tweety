<?php

namespace App\Events;

use App\Http\Resources\User\UserSimpleResource;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        protected Message $message,
        protected int $unreadCount,
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $recipientId = $this->message->conversation->users()
            ->where('user_id', '!=', $this->message->sender_id)
            ->value('users.id');

        return [
            new PrivateChannel('new-message.'.$recipientId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation.updated';
    }

    public function broadcastWith(): array
    {
        $this->message->loadMissing('sender');

        return [
            'id' => $this->message->conversation_id,
            'other_user' => $this->message->sender
                ? (new UserSimpleResource($this->message->sender))->resolve()
                : null,
            'last_message' => [
                'id' => $this->message->id,
                'body' => $this->message->body,
                'sender_id' => $this->message->sender_id,
                'created_at' => $this->message->created_at->toIsoString(),
            ],
            'is_read' => false,
            'unread_count' => $this->unreadCount,
        ];
    }
}
