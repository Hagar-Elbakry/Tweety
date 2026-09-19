<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\QuoteRepostRequest;
use App\Models\Post;
use App\Models\User;
use App\Services\RepostService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RepostController extends Controller
{
    public function __construct(
        protected RepostService $repostService
    ) {
    }

    public function store(Request $request, Post $post): JsonResponse
    {
        try {
            $this->repostService->repost($request->user(), $post);
            return ApiResponse::success(message: 'Post reposted successfully');
        } catch (Exception $e) {
            Log::error('Failed to repost post'.$e->getMessage());
            return ApiResponse::error(message: 'Failed to repost post', status: 500);
        }
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        try {
            $this->repostService->unrepost($request->user(), $post);
            return ApiResponse::success(message: 'Repost deleted successfully');
        } catch (Exception $e) {
            Log::error('Failed to delete repost'.$e->getMessage());

            return ApiResponse::error(message: 'Failed to delete repost', status: 500);
        }
    }

    public function quote(QuoteRepostRequest $request, Post $post): JsonResponse
    {
        try{
            $this->repostService->quote($request->user(), $post, $request->validated('comment'));
            return ApiResponse::success(message: 'Quote reposted successfully');
        } catch (Exception $e) {
            Log::error('Failed to repost quote'.$e->getMessage());
            return ApiResponse::error(message: 'Failed to repost quote', status: 500);
        }
    }
}
