<?php

namespace App\Http\Controllers\API\V1;

use App\Actions\LoadMoreReplyAction;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Exception;
use Illuminate\Support\Facades\Log;

class ReplyController extends Controller
{
    public function __invoke(Comment $comment, LoadMoreReplyAction $action)
    {
        try {
            $replies = $action->execute($comment);

            return ApiResponse::success(data: [
                'comments' => CommentResource::collection($replies),
                'next_page_url' => $replies->nextPageUrl(),
                'prev_next_url' => $replies->previousPageUrl(),
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching replies: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(message: 'Failed to fetch replies', status: 500);
        }
    }
}
