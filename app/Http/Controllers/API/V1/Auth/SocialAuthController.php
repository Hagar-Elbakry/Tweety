<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\AuthenticationService;

class SocialAuthController extends Controller
{
    public function __construct(
        protected AuthenticationService $userService
    )
    {}
    public function redirectToGoogle() {
        $redirectUrl = $this->userService->redirectToGoogle();
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
