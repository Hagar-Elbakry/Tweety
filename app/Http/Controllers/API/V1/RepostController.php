<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Post;
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
}
