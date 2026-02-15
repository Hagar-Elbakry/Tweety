<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Post;
use App\Services\CommentService;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService
    ) {}

    public function index(Post $post)
    {
        $comments = $this->commentService->getComments($post);

        return ApiResponse::success(data: CommentResource::collection($comments->comments));
    }

    public function store(StoreCommentRequest $request, Post $post)
    {
        $data = $request->validated();
        $comment = $this->commentService->store($post, $data);

        return ApiResponse::success(message: 'Comment created successfully', data: new CommentResource($comment));
    }
}
