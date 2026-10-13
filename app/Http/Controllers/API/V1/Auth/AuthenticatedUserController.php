<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Resources\User\UserResource;
use App\Services\AuthenticationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthenticatedUserController extends Controller
{
    public function __construct(
        protected AuthenticationService $authService
    ) {
    }

    public function login(LoginUserRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            if (!$result) {
                $message = 'The provided credentials do not match our records.';

                return ApiResponse::error(message: $message, status: 422, errors: ['email' => [$message]]);
            }

            return ApiResponse::success(
                message: 'User logged in successfully',
                data: [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                    'token_type' => $result['token_type'],
                    'expires_at' => $result['expires_at'],
                ]
            );
        } catch (Exception $e) {
            Log::error('Error logging in user: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(message: 'Failed to login user, please try again later.', status: 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return ApiResponse::success(message: 'User logged out successfully');
        } catch (Exception $e) {
            Log::error('Error logging out user: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(message: 'Failed to logout user, please try again later.', status: 500);
        }
    }
}
