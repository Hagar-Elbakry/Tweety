<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle() {
        $redirectUrl = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return response()->json([
            'url' => $redirectUrl,
        ]);
    }

    public function handleGoogleCallback() {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'username' => str_replace(' ', '', $googleUser->getName()),
                'password' => Hash::make(Str::random(8)),
                'provider' => 'google',
                'provider_id' => $googleUser->getId(),
            ]
        );

        $token = $user->createToken('auth-token.'.$user->username)->plainTextToken;

        UserRegistered::dispatch($user);

        return response()->json([
            'success' => true,
            'message' => 'User successfully logged in',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ]
        ]);
    }
}
