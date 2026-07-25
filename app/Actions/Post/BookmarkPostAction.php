<?php

namespace App\Actions\Post;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Collection;
use Maize\Markable\Mark;
use Maize\Markable\Models\Bookmark;

final class BookmarkPostAction
{
    public function execute(Post $post, User $user): Mark|Collection
    {
        return Bookmark::toggle($post, $user);
    }
}
