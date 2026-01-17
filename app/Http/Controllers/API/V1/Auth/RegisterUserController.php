<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Hash;

class RegisterUserController extends Controller
{
    protected $userService;
    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }
    public function register(RegisterUserRequest $request) {
        $result = $this->userService->createUser($request);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                ]
            ], 201);
    }
}
