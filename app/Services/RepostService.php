<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;

class RepostService
{
    public function repost(User $user, Post $post): void
    {
        $isReposted = $user->reposts()->where('post_id', $post->id)->where('type', 'repost')->exists();

        if (!$isReposted) {
            $user->reposts()->create([
                'post_id' => $post->id,
                'type' => 'repost'
            ]);
        }
    }

    public function unrepost(User $user, Post $post): void
    {
        $user->reposts()->where('post_id', $post->id)->where('type', 'repost')->delete();
    }
}
