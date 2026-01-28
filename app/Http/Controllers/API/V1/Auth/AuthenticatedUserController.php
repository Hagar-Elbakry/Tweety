<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticatedUserController extends Controller
{
    public function __construct(
        protected AuthenticationService $userService
    ) {}

    public function login(LoginUserRequest $request) : JsonResponse
    {
        $data = $request->validated();
        $result = $this->userService->login($data);
        if (! $result) {
            return ApiResponse::error(message: 'The provided credentials do not match our records.', status: 401);
        }

        return ApiResponse::success(
            message: 'User logged in successfully',
            data: [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ]
        );
    }

    public function logout(Request $request) : JsonResponse
    {
        $this->userService->logout($request);

        return ApiResponse::success(message: 'User logged out successfully');
    }
}
