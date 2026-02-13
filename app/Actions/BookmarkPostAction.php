<?php

namespace App\Actions;

use App\Models\Post;
use App\Models\User;
use Maize\Markable\Models\Bookmark;

final class BookmarkPostAction
{
    public function execute(Post $post, User $user): void
    {
        Bookmark::toggle($post, $user);
    }
}
