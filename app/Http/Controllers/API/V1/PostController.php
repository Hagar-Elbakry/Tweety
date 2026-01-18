<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;

class PostController extends Controller
{
    protected $postService;
    public function __construct(PostService $postService) {
        $this->postService = $postService;
    }
    public function store(StorePostRequest $request) {
        $post = $this->postService->createPost($request);

        return ApiResponse::success(message: 'Post created successfully', data: new PostResource($post), status: 201);
    }

    public function update(StorePostRequest $request, Post $post) {
        $post = $this->postService->updatePost($request, $post);
        if(!$post) {
            return ApiResponse::error(message: 'You are not authorized to update this post', status: 403);
        }

        return ApiResponse::success(message: 'Post updated successfully', data: new PostResource($post));
    }

    public function destroy(Post $post) {
        $result = $this->postService->deletePost($post);
        if(!$result) {
            return ApiResponse::error(message: 'You are not authorized to delete this post', status: 403);
        }

        return ApiResponse::success(message: 'Post deleted successfully');
    }
}
