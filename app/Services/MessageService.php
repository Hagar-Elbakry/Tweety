<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageService
{
    public function getMessagesForConversation(Conversation $conversation): LengthAwarePaginator
    {
        return $conversation->messages()->with('sender')->paginate(10);
    }
}
