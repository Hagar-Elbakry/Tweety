<?php

namespace App\Http\Controllers\API\V1;

use App\Actions\Post\BookmarkPostAction;
use App\Actions\Post\LikePostAction;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\DeletePostRequest;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maize\Markable\Models\Bookmark;
use Maize\Markable\Models\Like;

class PostController extends Controller
{
    public function __construct(
        protected PostService $postService
    ) {
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = $request->user();
            $post = $this->postService->create($data, $user);

            return ApiResponse::success(message: 'Post created successfully', data: new PostResource($post),
                status: 201);
        } catch (Exception $e) {
            Log::error('Error creating post: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error(message: 'Failed to create post, please try again later.', status: 500);
        }
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        try {
            $data = $request->validated();
            $post = $this->postService->update($data, $post);
            return ApiResponse::success(message: 'Post updated successfully', data: new PostResource($post));
        } catch (Exception $e) {
            Log::error('Error updating post: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error(message: 'Failed to update post, please try again later.', status: 500);
        }
    }

    public function destroy(DeletePostRequest $request, Post $post): JsonResponse
    {
        try {
            $this->postService->delete($post);

            return ApiResponse::success(message: 'Post deleted successfully');
        } catch (Exception $e) {
            Log::error('Error deleting post: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error(message: 'Failed to delete post, please try again later.', status: 500);
        }
    }

    public function like(Request $request, Post $post, LikePostAction $action): JsonResponse
    {
        try {
            $user = $request->user();
            $action->execute($post, $user);
            if (Like::has($post, $user)) {
                return ApiResponse::success(message: 'Post liked successfully');
            } else {
                return ApiResponse::success(message: 'Post unliked successfully');
            }
        } catch (Exception $e) {
            Log::error('Error liking post: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error(message: 'Failed to like post, please try again later.', status: 500);
        }
    }

    public function bookmark(Request $request, Post $post, BookmarkPostAction $action): JsonResponse
    {
        try {
            $user = $request->user();
            $action->execute($post, $user);
            if (Bookmark::has($post, $user)) {
                return ApiResponse::success(message: 'Post bookmarked successfully');
            } else {
                return ApiResponse::success(message: 'Post unbookmarked successfully');
            }
        } catch (Exception $e) {
            Log::error('Error bookmarking post: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error(message: 'Failed to bookmark post, please try again later.', status: 500);
        }
    }
}
