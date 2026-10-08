<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    /**
     * The messages that belong to the conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function isReadFor(User $user): bool
    {
        if (!$this->lastMessage) {
            return true;
        }

        $participant = $this->relationLoaded('users')
            ? $this->users->firstWhere('id', $user->id)
            : $this->users()->where('users.id', $user->id)->first();

        $readAt = $participant?->pivot->read_at;

        return $readAt !== null && $this->lastMessage->created_at->lessThanOrEqualTo($readAt);
    }

    /**
     * The users that belong to the conversation.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps()->withPivot('read_at');
    }
}
