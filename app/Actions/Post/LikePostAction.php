<?php

namespace App\Actions\Post;

use App\Models\Post;
use App\Models\User;
use App\Notifications\NewLikeNotification;
use Maize\Markable\Mark;
use Maize\Markable\Models\Like;

final class LikePostAction
{
    public function execute(Post $post, User $user): array
    {
        $result = Like::toggle($post, $user);
        if ($result instanceof Mark) {
            $post->user->notify(new NewLikeNotification($user, $post));
            return ['message' => 'Post liked successfully.'];
        } else {
            return ['message' => 'Post unliked successfully.'];
        }
    }
}
