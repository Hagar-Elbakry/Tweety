<?php

namespace App\Actions;

use App\Models\Post;
use App\Models\User;
use Maize\Markable\Models\Like;

final class LikePostAction
{
    public function execute(Post $post, User $user): void
    {
        Like::toggle($post, $user);
    }
}
