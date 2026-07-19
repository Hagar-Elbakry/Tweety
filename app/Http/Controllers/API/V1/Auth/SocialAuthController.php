<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Services\AuthenticationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SocialAuthController extends Controller
{
    public function __construct(
        protected AuthenticationService $userService
    ) {
    }

    public function redirectToGoogle(): JsonResponse
    {
        $redirectUrl = $this->userService->redirectToGoogle();

        return response()->json([
            'url' => $redirectUrl,
        ]);
    }

    public function handleGoogleCallback(): JsonResponse
    {
        try {
            $result = $this->userService->handleGoogleCallback();

            return ApiResponse::success(
                message: 'User successfully logged in',
                data: [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                ]
            );
        } catch (Exception $e) {
            Log::error('Error handling Google callback: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error(message: 'Failed to authenticate with Google', status: 500);
        }
    }
}
