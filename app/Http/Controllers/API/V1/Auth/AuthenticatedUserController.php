<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticatedUserController extends Controller
{
    public function login(LoginUserRequest $request) {

        $user = User::where('email', $request->email)->first();
        if(!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.',
            ]);
        }

        $token = $user->createToken('auth-token.' . $user->email)->plainTextToken;
                return response()->json([
                    'success' => true,
                    'message' => 'User logged in successfully',
                    'data' => [
                        'user' => new UserResource($user),
                        'token' => $token
                    ]
                ]);
    }

    public function logout(Request $request) {
        $request->user()->tokens()->delete();
        return response()->json([
            'success' => true,
            'message' => 'User logged out successfully'
        ]);
    }
}
