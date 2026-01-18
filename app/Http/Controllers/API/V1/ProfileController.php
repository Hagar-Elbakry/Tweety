<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\User;
use App\Services\ProfileService;

class ProfileController extends Controller
{
    protected $profileService;
    public function __construct(ProfileService $profileService) {
        $this->profileService = $profileService;
    }

    public function update(ProfileRequest $request, User $user) {
        $user = $this->profileService->updateProfileDetails($request, $user);
        return ApiResponse::success(message:'Profile updated successfully.', data: new ProfileResource($user));
    }
}
