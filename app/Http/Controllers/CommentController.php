<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Post;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService
    ) {}

    public function store(StoreCommentRequest $request, Post $post): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $comment = $this->commentService->store($post, $data);

        return ApiResponse::success(message: 'Comment created successfully', data: new CommentResource($comment));
    }
}
