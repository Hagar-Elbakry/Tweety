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
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService
    ) {}

    public function index(Post $post): JsonResponse
    {
        $comments = $this->commentService->getComments($post);

        return ApiResponse::success(data: CommentResource::collection($comments->comments));
    }

    public function store(StoreCommentRequest $request, Post $post): JsonResponse
    {
        $data = $request->validated();
        $comment = $this->commentService->store($post, $data);

        return ApiResponse::success(message: 'Comment created successfully', data: new CommentResource($comment));
    }

    public function destroy(DeleteCommentRequest $request, Comment $comment): JsonResponse
    {
        $this->commentService->delete($comment);

        return ApiResponse::success(message: 'Comment deleted successfully');
    }
}
