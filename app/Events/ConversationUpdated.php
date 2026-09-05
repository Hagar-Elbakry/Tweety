<?php

namespace App\Events;

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
    ) {
    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $recipientId = $this->message->conversation->users()->where('user_id', '!=',
            $this->message->sender_id)->first()->id;
        return [
            new PrivateChannel('new-message.'.$recipientId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->message->conversation_id,
            'sender' => [
                'name' => $this->message->sender->name,
                'avatar' => $this->message->sender->avatar,
            ],
            'body' => $this->message->body,
            'sent_at' => $this->message->created_at->toIsoString(),
            'is_read' => false,
        ];
    }
}
