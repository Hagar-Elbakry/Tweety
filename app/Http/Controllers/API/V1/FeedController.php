<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Helpers\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Resources\FeedItemResource;
use App\Services\FeedService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FeedController extends Controller
{
    public function __construct(
        protected FeedService $feedService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $items = $this->feedService->getTimeline($request->user());

            return ApiResponse::success(
                message: 'Feed loaded successfully',
                data: FeedItemResource::collection($items),
                meta: Pagination::meta($items)
            );
        } catch (Exception $e) {
            Log::error('Failed to load feed: '.$e->getMessage());

            return ApiResponse::error(message: 'Failed to load feed', status: 500);
        }
    }
}
