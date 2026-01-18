<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;

class AuthenticatedUserController extends Controller
{
    protected  $userService;
    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }
    public function login(LoginUserRequest $request)
    {
        $result = $this->userService->loginUser($request);
        if (!$result) {
            return ApiResponse::error(message: 'The provided credentials do not match our records.', status: 401);
        }

        return ApiResponse::success(
            message: 'User logged in successfully',
            data: [
                'user' => new UserResource($result['user']),
                'token' => $result['token']
            ]
        );
    }

    public function logout(Request $request) {
        $this->userService->logouUser($request);
        return ApiResponse::success(message: 'User logged out successfully');
    }
}
