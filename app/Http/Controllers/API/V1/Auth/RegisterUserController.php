<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterUserController extends Controller
{
    public function register(RegisterUserRequest $request) {
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);
            $user = User::query()->create($data);
            $token = $user->createToken('auth_token.' . $user->username)->plainTextToken;

            UserRegistered::dispatch($user);
            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token,
                ]
            ]);
    }
}
