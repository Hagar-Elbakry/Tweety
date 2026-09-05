<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;

class ConversationService
{
    public function findOrCreateBetween(User $sender, User $recipient): Conversation
    {
        $conversation = Conversation::whereHas('users', function ($query) use ($sender) {
            $query->where('user_id', $sender->id);
        })->whereHas('users', function ($query) use ($recipient) {
            $query->where('user_id', $recipient->id);
        })->withCount('users')->having('users_count', 2)->first();

        if ($conversation) {
            return $conversation;
        }

        $conversation = Conversation::create();
        $conversation->users()->attach([$sender->id, $recipient->id]);

        return $conversation;
    }
}
