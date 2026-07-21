<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\User;
use App\Services\ProfileService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService
    ) {
    }

    public function show(User $user): JsonResponse
    {
        try {
            $profile = $this->profileService->getProfile($user);

            return ApiResponse::success(
                message: 'Profile fetched successfully.',
                data: new ProfileResource($profile),
            );
        } catch (Exception $e) {
            Log::error('Error fetching profile: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error(message: 'Failed to fetch profile, please try again later.', status: 500);
        }
    }

    public function me(Request $request): JsonResponse
    {
        try {
            $profile = $this->profileService->getProfile($request->user());

            return ApiResponse::success(
                message: 'Profile fetched successfully.',
                data: new ProfileResource($profile),
            );
        } catch (Exception $e) {
            Log::error('Error fetching profile: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error(message: 'Failed to fetch profile, please try again later.', status: 500);
        }
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = $request->user();
            $user = $this->profileService->update($data, $user);

            return ApiResponse::success(message: 'Profile updated successfully.', data: new ProfileResource($user));
        } catch (Exception $e) {
            Log::error('Error updating profile: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error(message: 'Failed to update profile, please try again later.', status: 500);
        }
    }
}
