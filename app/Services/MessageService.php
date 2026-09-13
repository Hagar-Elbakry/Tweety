<?php

namespace App\Services;

use App\Events\ConversationUpdated;
use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageRead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageService
{
    public function __construct(
        protected ConversationService $conversationService,
    ) {
    }

    public function getMessagesForConversation(Conversation $conversation, User $user): LengthAwarePaginator
    {
        $messages = $conversation->messages()->with(['sender', 'seenBy.user'])->paginate(10);
        $seenAt = Carbon::now();
        $anyNewlyRead = false;
        $messages->each(function ($message) use ($user, $conversation, &$seenAt, &$anyNewlyRead) {
            if (!$message->seenBy->contains('user_id', $user->id) && $message->sender_id != $user->id) {
                MessageRead::create([
                    'message_id' => $message->id,
                    'user_id' => $user->id,
                    'seen_at' => $seenAt
                ]);
                $anyNewlyRead = true;
            }
        });
        if ($anyNewlyRead) {
            broadcast(new MessagesRead($conversation, $user, $seenAt))->toOthers();
        }
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
        $recipient = $message->conversation->users()->where('users.id', '!=', $user->id)->first();
        $unreadCount = $this->conversationService->getUnreadCount($recipient);
        broadcast(new MessageSent($message))->toOthers();
        broadcast(new ConversationUpdated($message, $unreadCount));
        return $message;
    }
}
