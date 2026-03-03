<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ToggleFollowRequest;
use App\Services\FollowService;
use Illuminate\Http\JsonResponse;

class FollowController extends Controller
{
    public function __construct(
        protected FollowService $followService
    ) {}

    public function __invoke(ToggleFollowRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->followService->toggleFollow($data);

        return ApiResponse::success(message: $result['message']);
    }
}
