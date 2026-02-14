<?php

namespace App\Actions;

use App\Models\Post;

final class GetCommentsAction
{
    public function execute(Post $post)
    {
        return $post->load([
            'comments' => function ($query) {
                $query->whereNull('parent_id');
            },
            'comments.replies',
            'comments.user',
        ]);
    }
}
