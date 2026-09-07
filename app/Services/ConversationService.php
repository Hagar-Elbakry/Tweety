<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ConversationService
{
    public function getUserConversations(User $user): LengthAwarePaginator
    {
        return Conversation::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['users', 'lastMessage'])->paginate(20);
    }

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

    public function getUnreadCount(User $user): int
    {
        return Message::whereDoesntHave('seenBy', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->whereHas('conversation', function ($query) use ($user) {
            $query->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        })->where('sender_id', '!=', $user->id)->count();
    }
}
