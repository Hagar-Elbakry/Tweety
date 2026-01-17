<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    protected $postService;
    public function __construct(PostService $postService) {
        $this->postService = $postService;
    }
    public function store(StorePostRequest $request) {
        $post = $this->postService->createPost($request);

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'data' => new PostResource($post)
        ], 201);
    }

    public function update(StorePostRequest $request, Post $post) {
        $post = $this->postService->updatePost($request, $post);
        if($post) {
            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully',
                'data' => new PostResource($post)
            ]);
        }

            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this post',
            ], 403);
    }

    public function destroy(Post $post) {
        $result = $this->postService->deletePost($post);
        if($result) {
            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully',
            ]);
        }
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this post',
            ], 403);
    }
}
