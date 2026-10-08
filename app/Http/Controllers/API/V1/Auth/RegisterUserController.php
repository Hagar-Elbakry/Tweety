<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Resources\User\UserResource;
use App\Services\AuthenticationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RegisterUserController extends Controller
{
    public function __construct(
        protected AuthenticationService $authService
    ) {
    }

    public function __invoke(RegisterUserRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return ApiResponse::success(
                message: 'User created successfully',
                data: [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                    'token_type' => $result['token_type'],
                    'expires_at' => $result['expires_at'],
                ],
                status: 201
            );
        } catch (Exception $e) {
            Log::error('Error registering user: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(message: 'Failed to register user, please try again later.', status: 500);
        }
    }
}
