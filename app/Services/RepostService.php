<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Notifications\NewRepostNotification;

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

            if ($post->user->id != $user->id) {
                $post->user->notify(new NewRepostNotification($user, $post, 'repost'));
            }
        }
    }

    public function unrepost(User $user, Post $post): void
    {
        $user->reposts()->where('post_id', $post->id)->where('type', 'repost')->delete();
    }

    public function quote(User $user, Post $post, string $comment): void
    {
        $user->reposts()->create([
            'post_id' => $post->id,
            'type' => 'quote',
            'comment' => $comment
        ]);

        if ($post->user->id != $user->id) {
            $post->user->notify(new NewRepostNotification($user, $post, 'quote'));
        }
    }
}
