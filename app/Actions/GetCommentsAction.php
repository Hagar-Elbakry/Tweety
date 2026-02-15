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
            'comments.user' => function ($query) {
                $query->select('id', 'name', 'username', 'avatar');
            },
            'comments.replies',
            'comments.replies.user' => function ($query) {
                $query->select('id', 'name', 'username', 'avatar');
            },
        ]);
    }
}
