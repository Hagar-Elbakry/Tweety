<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageRead;
use App\Models\User;

class MessagePolicy
{
    public function send(User $user, Conversation $conversation): bool
    {
        $otherUser = $conversation->users()->where('users.id', '!=', $user->id)->first();

        return $conversation->users()->where('users.id', $user->id)->exists()
            && $otherUser->blockedUsers()->where('blocked_id', $user->id)->doesntExist()
            && $user->blockedUsers()->where('blocked_id', $otherUser->id)->doesntExist();
    }

    public function update(User $user, Message $message): bool
    {
        return $message->sender_id === $user->id && MessageRead::where('message_id', $message->id)->doesntExist();
    }

    public function delete(User $user, Message $message): bool
    {
        return $message->conversation->users()->where('users.id', $user->id)->exists();
    }
}
