<?php

namespace App\Actions;

use App\Models\Comment;
use Illuminate\Pagination\CursorPaginator;

final class LoadMoreReplyAction
{
    public function execute(Comment $comment): CursorPaginator
    {
        return Comment::where('parent_id', $comment->id)->with('user:id,name,username,avatar')
            ->withCount('replies')
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(3);
    }
}
