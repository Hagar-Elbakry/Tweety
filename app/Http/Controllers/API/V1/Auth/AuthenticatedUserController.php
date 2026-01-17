<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticatedUserController extends Controller
{
    protected  $userService;
    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }
    public function login(LoginUserRequest $request)
    {
        $result = $this->userService->loginUser($request);
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'User logged in successfully',
                'data' => [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token']
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'The provided credentials do not match our records.',
        ], 401);
    }

    public function logout(Request $request) {
        $this->userService->logouUser($request);
        return response()->json([
            'success' => true,
            'message' => 'User logged out successfully'
        ]);
    }
}
