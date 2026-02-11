<?php

namespace App\Actions;

use App\Models\Post;
use Maize\Markable\Models\Bookmark;

class BookmarkPostAction
{
    public function execute(Post $post, $user)
    {
        Bookmark::toggle($post, $user);
    }
}
