<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;

class CommentService
{
    public function store(Post $post, array $data): Comment
    {
        $comment = $post->comments()->create($data);

        return $comment->load(['user', 'replies.user', 'post']);
    }
}
