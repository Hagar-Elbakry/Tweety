<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedUserController extends Controller
{
    public function login(LoginUserRequest $request) {
        $credentials = $request->validated();
            if(Auth::attempt($credentials)) {
                $user = Auth::user();
                $token = $user->createToken('auth_token.' . $user->username)->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'User logged in successfully',
                    'data' => [
                        'user' => new UserResource($user),
                        'token' => $token
                    ]
                ]);
            }
    }

    public function logout(Request $request) {
        $request->user()->tokens()->delete();
        return response()->json([
            'success' => true,
            'message' => 'User logged out successfully'
        ]);
    }
}
