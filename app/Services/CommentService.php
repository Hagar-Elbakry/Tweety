<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\NewCommentNotification;

class CommentService
{
    public function getComments(Post $post): Post
    {
        return $post->load([
            'comments' => function ($query) {
                $query->whereNull('parent_id')->with([
                    'user:id,name,username,avatar',
                    'replies' => function ($q) {
                        $q->with('user:id,name,username,avatar')->withCount('replies');
                    }
                ]);
            },
        ]);
    }

    public function store(Post $post, array $data, User $user): Comment
    {
        $data['user_id'] = $user->id;
        $data['parent_id'] = $data['parent_id'] ?? null;
        $comment = $post->comments()->create($data);
        if (is_null($data['parent_id'])) {
            $post->user->notify(new NewCommentNotification($user, $post->user, $comment));
        } else {
            $parentComment = Comment::find($data['parent_id']);
            if ($parentComment && $parentComment->user_id !== $user->id) {
                $parentComment->user->notify(new NewCommentNotification($user, $parentComment->user, $comment));
            }
        }
        return $comment->load('user');
    }

    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}
