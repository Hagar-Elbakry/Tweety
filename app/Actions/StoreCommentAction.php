<?php

namespace App\Actions;

use App\Models\Comment;
use App\Models\Post;

final class StoreCommentAction
{
    public function execute(Post $post, array $data): Comment
    {
        $data['parent_id'] = $data['parent_id'] ?? null;
        $comment = $post->comments()->create($data);

        return $comment->load(['user', 'replies.user', 'post']);
    }
}
