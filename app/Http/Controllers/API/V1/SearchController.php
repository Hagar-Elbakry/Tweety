<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchUserRequest;
use App\Http\Resources\UserSearchResource;
use App\Services\SearchService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function __construct(
        protected SearchService $searchService
    ) {
    }

    public function users(SearchUserRequest $request): JsonResponse
    {
        try {
            $users = $this->searchService->searchUsers($request->validated('q'), $request->user());
            if ($users->isEmpty()) {
                return ApiResponse::success(message: 'No users found matching your search');
            }
            return ApiResponse::success(message: 'Found users successfully',
                data: UserSearchResource::collection($users));
        } catch (Exception $e) {
            Log::error('Failed to search'.$e->getMessage());
            return ApiResponse::error(message: 'Failed to search', status: 500);
        }
    }
}
