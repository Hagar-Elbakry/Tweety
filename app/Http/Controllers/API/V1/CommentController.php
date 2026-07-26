<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\DeleteCommentRequest;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService
    ) {}

    public function index(Post $post): JsonResponse
    {
        try {
            $comments = $this->commentService->getComments($post);

            return ApiResponse::success(data: CommentResource::collection($comments->comments));
        } catch (Exception $e) {
            Log::error('Error fetching comments: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(message: 'Failed to fetch comments', status: 500);
        }
    }

    public function store(StoreCommentRequest $request, Post $post): JsonResponse
    {
        try {
            $data = $request->validated();
            $comment = $this->commentService->store($post, $data);

            return ApiResponse::success(message: 'Comment created successfully', data: new CommentResource($comment));
        } catch (Exception $e) {
            Log::error('Error creating comment: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(message: 'Failed to create comment', status: 500);
        }
    }

    public function destroy(DeleteCommentRequest $request, Comment $comment): JsonResponse
    {
        try {
            $this->commentService->delete($comment);

            return ApiResponse::success(message: 'Comment deleted successfully');
        } catch (Exception $e) {
            Log::error('Error deleting comment: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(message: 'Failed to delete comment', status: 500);
        }
    }
}
