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

    public function follow(ToggleFollowRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->followService->follow($data);

        return ApiResponse::success(message: 'Successfully followed the user.');
    }

    public function unfollow(ToggleFollowRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->followService->unfollow($data);

        return ApiResponse::success(message: 'Successfully unfollowed the user.');
    }
}
