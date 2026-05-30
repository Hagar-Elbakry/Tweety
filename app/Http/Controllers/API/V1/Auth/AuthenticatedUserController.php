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
use Log;

class AuthenticatedUserController extends Controller
{
    public function __construct(
        protected AuthenticationService $userService
    ) {
    }

    public function login(LoginUserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $result = $this->userService->login($data);
            if (!$result) {
                return ApiResponse::error(message: 'The provided credentials do not match our records.', status: 401);
            }

            return ApiResponse::success(
                message: 'User logged in successfully',
                data: [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
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
        $user = $request->user();
        $this->userService->logout($user);

        return ApiResponse::success(message: 'User logged out successfully');
    }
}
