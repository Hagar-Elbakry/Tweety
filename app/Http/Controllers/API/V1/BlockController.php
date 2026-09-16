<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\BlockRequest;
use App\Models\User;
use App\Services\BlockService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BlockController extends Controller
{
    public function __construct(
        protected BlockService $blockService
    ) {}

    public function store(BlockRequest $request, User $user): JsonResponse
    {
        try {
            $this->blockService->block($request->user(), $user);

            return ApiResponse::success(message: 'User blocked successfully.');
        } catch (Exception $e) {
            Log::error('Error blocking user: '.$e->getMessage());

            return ApiResponse::error(message: 'Failed to block user.', status: 500);
        }
    }

    public function destroy(BlockRequest $request, User $user): JsonResponse
    {
        try {
            $this->blockService->unblock($request->user(), $user);

            return ApiResponse::success(message: 'User unblocked successfully.');
        } catch (Exception $e) {
            Log::error('Error unblocking user: '.$e->getMessage());

            return ApiResponse::error(message: 'Failed to unblock user.', status: 500);
        }
    }
}
