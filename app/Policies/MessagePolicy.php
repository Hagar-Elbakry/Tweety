<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function delete(User $user, Message $message): bool
    {
        return $message->conversation->users()->where('users.id', $user->id)->exists();
    }
}
