<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\MessageRead;
use App\Models\User;

class MessagePolicy
{
    public function update(User $user, Message $message): bool
    {
        return $message->sender_id === $user->id && MessageRead::where('message_id', $message->id)->doesntExist();
    }

    public function delete(User $user, Message $message): bool
    {
        return $message->conversation->users()->where('users.id', $user->id)->exists();
    }
}
