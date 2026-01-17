<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\UserService;

class SocialAuthController extends Controller
{
    protected  $userService;
    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }
    public function redirectToGoogle() {
        $redirectUrl = $this->userService->redirectUserToGoogle();
        return response()->json([
            'url' => $redirectUrl,
        ]);
    }

    public function handleGoogleCallback() {
        $result = $this->userService->handleGoogleCallback();

        return ApiResponse::success(
            message: 'User successfully logged in',
            data: [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ]
        );
    }
}
