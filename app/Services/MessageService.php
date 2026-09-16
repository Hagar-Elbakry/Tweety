<?php

namespace App\Services;

use App\Events\ConversationUpdated;
use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Events\MessageUpdated;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageDelete;
use App\Models\MessageRead;
use App\Models\User;
use App\Traits\Uploadable;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageService
{
    use Uploadable;

    public function __construct(
        protected ConversationService $conversationService,
    ) {}

    public function getMessagesForConversation(Conversation $conversation, User $user): LengthAwarePaginator
    {
        $messages = $conversation->messages()
            ->whereDoesntHave('deletedFor', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['sender', 'attachments', 'seenBy.user'])->paginate(10);
        $seenAt = Carbon::now();
        $anyNewlyRead = false;
        $messages->each(function ($message) use ($user, &$seenAt, &$anyNewlyRead) {
            if (! $message->seenBy->contains('user_id', $user->id) && $message->sender_id != $user->id) {
                MessageRead::create([
                    'message_id' => $message->id,
                    'user_id' => $user->id,
                    'seen_at' => $seenAt,
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

    public function sendMessage(Conversation $conversation, ?string $body, ?array $attachments, User $user): Message
    {
        $message = $conversation->messages()->create([
            'body' => $body,
            'sender_id' => $user->id,
        ]);
        if ($attachments) {
            foreach ($attachments as $attachment) {
                $filePath = $this->uploadFile($attachment, 'messages');
                $message->attachments()->create([
                    'path' => $filePath,
                    'original_name' => $attachment->getClientOriginalName(),
                    'type' => $attachment->getClientMimeType(),
                ]);
            }
        }
        $message->load(['sender', 'attachments']);
        $recipient = $message->conversation->users()->where('users.id', '!=', $user->id)->first();
        $unreadCount = $this->conversationService->getUnreadCount($recipient);
        broadcast(new MessageSent($message))->toOthers();
        broadcast(new ConversationUpdated($message, $unreadCount));

        return $message;
    }

    public function updateMessage(Message $message, string $body): Message
    {
        $message->update(['body' => $body]);
        broadcast(new MessageUpdated($message))->toOthers();

        return $message;
    }

    public function deleteForUser(Message $message, User $user): void
    {
        $isDeleted = MessageDelete::where('message_id', $message->id)->where('user_id', $user->id)->exists();
        if (! $isDeleted) {
            MessageDelete::create([
                'message_id' => $message->id,
                'user_id' => $user->id,
            ]);
        }
    }
}
