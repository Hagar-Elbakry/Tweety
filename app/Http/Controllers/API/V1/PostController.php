<?php

namespace App\Http\Controllers\API\V1;

use App\Actions\BookmarkPostAction;
use App\Actions\LikePostAction;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\DeletePostRequest;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Maize\Markable\Models\Bookmark;
use Maize\Markable\Models\Like;

class PostController extends Controller
{
    public function __construct(
        protected PostService $postService
    ) {}

    public function store(StorePostRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $post = $this->postService->create($data);

        return ApiResponse::success(message: 'Post created successfully', data: new PostResource($post), status: 201);
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $data = $request->validated();
        $post = $this->postService->update($data, $post);

        return ApiResponse::success(message: 'Post updated successfully', data: new PostResource($post));
    }

    public function destroy(DeletePostRequest $request, Post $post): JsonResponse
    {
        $this->postService->delete($post);

        return ApiResponse::success(message: 'Post deleted successfully');
    }

    public function like(Post $post, LikePostAction $action): JsonResponse
    {
        $user = auth()->user();
        $action->execute($post, $user);
        if (Like::has($post, $user)) {
            return ApiResponse::success(message: 'Post liked successfully');
        } else {
            return ApiResponse::success(message: 'Post unliked successfully');
        }
    }

    public function bookmark(Post $post, BookmarkPostAction $action): JsonResponse
    {
        $user = auth()->user();
        $action->execute($post, $user);
        if (Bookmark::has($post, $user)) {
            return ApiResponse::success(message: 'Post bookmarked successfully');
        } else {
            return ApiResponse::success(message: 'Post unbookmarked successfully');
        }
    }
}
