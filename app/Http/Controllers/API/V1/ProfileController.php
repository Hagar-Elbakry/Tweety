<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
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
        $profile = $this->profileService->show($user);

        return ApiResponse::success(
            message: 'Profile fetched successfully.',
            data: new ProfileResource($profile),
        );
    }

    public function update(ProfileRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        $user = $this->profileService->update($data, $user);

        return ApiResponse::success(message: 'Profile updated successfully.', data: new ProfileResource($user));
    }
}
