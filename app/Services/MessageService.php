<?php

namespace App\Services;

use App\Events\ConversationUpdated;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageService
{
    public function getMessagesForConversation(Conversation $conversation, User $user): LengthAwarePaginator
    {
        $messages = $conversation->messages()->with('sender')->paginate(10);
        $conversation->users()->updateExistingPivot($user->id, ['read_at' => now()]);
        return $messages;
    }

    public function sendMessage(Conversation $conversation, string $body, User $user): Message
    {
        $message = $conversation->messages()->create([
            'body' => $body,
            'sender_id' => $user->id
        ]);
        $message->load('sender');
        broadcast(new MessageSent($message))->toOthers();
        broadcast(new ConversationUpdated($message));
        return $message;
    }
}
