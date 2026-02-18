<?php

namespace App\Policies;

use App\Models\User;

class CommentPolicy
{
    public function delete(User $user, $comment): bool
    {
        return $user->id === $comment->user_id || $user->id === $comment->post->user_id;
    }
}
