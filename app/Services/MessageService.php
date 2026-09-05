<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageService
{
    public function getMessagesForConversation(Conversation $conversation): LengthAwarePaginator
    {
        return $conversation->messages()->with('sender')->paginate(10);
    }

    public function sendMessage(Conversation $conversation, string $body, User $user): Message
    {
        $message = $conversation->messages()->create([
            'body' => $body,
            'sender_id' => $user->id
        ]);
        broadcast(new MessageSent($message))->toOthers();
        return $message->load('sender');
    }
}
