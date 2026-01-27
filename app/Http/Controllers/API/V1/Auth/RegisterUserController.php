<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthenticationService;

class RegisterUserController extends Controller
{
    public function __construct(
        protected AuthenticationService $userService
    ) {}

    public function register(RegisterUserRequest $request)
    {
        try {
            $data = $request->validated();
            $result = $this->userService->register($data);

            return ApiResponse::success(
                message: 'User created successfully',
                data: [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                ],
                status: 201
            );
        } catch (\Exception $e) {
            return ApiResponse::error(message: 'Failed to register user, please try again later.', status: 500);
        }
    }
}
