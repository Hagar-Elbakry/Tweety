<?php

namespace App\Actions;

use App\Models\Post;
use Maize\Markable\Models\Like;

final class LikePostAction
{
    public function execute(Post $post, $user)
    {
        Like::toggle($post, $user);
    }
}
