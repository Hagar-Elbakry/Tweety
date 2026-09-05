<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    /**
     * The users that belong to the conversation.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps()->withPivot('read_at');
    }

    /**
     * The messages that belong to the conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
