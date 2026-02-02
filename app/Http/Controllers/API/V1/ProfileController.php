<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService
    ) {}

    public function show(User $user): JsonResponse
    {
        $profile = $this->profileService->getProfile($user);

        return ApiResponse::success(
            message: 'Profile fetched successfully.',
            data: new ProfileResource($profile),
        );
    }

    public function me(): JsonResponse
    {
        $profile = $this->profileService->getProfile(auth()->user());

        return ApiResponse::success(
            message: 'Profile fetched successfully.',
            data: new ProfileResource($profile),
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = auth()->user();
        $user = $this->profileService->update($data, $user);

        return ApiResponse::success(message: 'Profile updated successfully.', data: new MyProfileResource($user));
    }
}
