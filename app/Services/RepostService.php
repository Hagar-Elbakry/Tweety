<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;

class RepostService
{
    public function repost(User $user, Post $post): void
    {
        $isRepost = $user->reposts()->where('post_id', $post->id)->where('type', 'repost')->exists();

        if (!$isRepost) {
            $user->reposts()->create([
                'post_id' => $post->id,
                'type' => 'repost'
            ]);
        }
    }
}
