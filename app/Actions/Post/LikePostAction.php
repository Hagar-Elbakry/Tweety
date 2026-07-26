<?php

namespace App\Actions\Post;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Collection;
use Maize\Markable\Mark;
use Maize\Markable\Models\Like;

final class LikePostAction
{
    public function execute(Post $post, User $user): Mark|Collection
    {
        return Like::toggle($post, $user);
    }
}
