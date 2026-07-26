<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ToggleFollowRequest;
use App\Services\FollowService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class FollowController extends Controller
{
    public function __construct(
        protected FollowService $followService
    ) {}

    public function __invoke(ToggleFollowRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $result = $this->followService->toggleFollow($data, $request->user());

            return ApiResponse::success(message: $result['message']);
        } catch (Exception $e) {
            Log::error('Failed to toggle follow: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(message: 'Failed to toggle follow', status: 500);
        }
    }
}
