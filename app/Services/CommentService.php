<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;

class CommentService
{
    public function getComments(Post $post): Post
    {
        return $post->load([
            'comments' => function ($query) {
                $query->whereNull('parent_id');
            },
        ]);
    }

    public function store(Post $post, array $data): Comment
    {
        $data['user_id'] = auth()->id();
        $data['parent_id'] = $data['parent_id'] ?? null;
        $comment = $post->comments()->create($data);

        return $comment->load(['user', 'replies.user']);
    }

    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}
