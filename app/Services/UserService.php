<?php

namespace App\Services;

use App\Events\UserRegistered;
use App\Mail\ResetPassword;
use App\Mail\VerifyEmail;
use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;

class UserService
{
    protected $otp;
    public function __construct()
    {
        $this->otp = new Otp();
    }

    public function createUser($request)
    {
        $data = $request->validated();
        $user = User::query()->create($data);
        $token = $this->getToken($user);

        UserRegistered::dispatch($user);
        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function loginUser($request)
    {
        $user = $this->getUser($request);
        if (!$user || !Hash::check($request->password, $user->password)) {
            return null;
        }
        $token = $this->getToken($user);
        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function logouUser($request)
    {
        $request->user()->tokens()->delete();
    }

    public function redirectUserToGoogle() {
        $redirectUrl = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return $redirectUrl;
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
        $token = $this->getToken($user);

        UserRegistered::dispatch($user);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
    public function verifyUserEmail($request)
    {
        $validatedOtp = $this->otp->validate($request->email, $request->otp);
         if(!$validatedOtp->status) {
                return null;
         }
            $user = $this->getUser($request);
            $user->update([
                'email_verified_at' => now()
            ]);
            return $user;
    }

    public function resendEmailVerificationOtp()
    {
      $user = auth()->user();
      if($user->hasVerifiedEmail()) {
          return null;
      }

      Mail::to($user)->queue(new VerifyEmail($user));
        return $user;
    }

    public function sendPasswordResetOtp($request)
    {
        $user = $this->getUser($request);
        Mail::to($user)->queue(new ResetPassword($user));
    }

    public function verifyOtp($request) {
        $validatedOtp = $this->otp->validate($request->email, $request->otp);
            if(!$validatedOtp->status) {
                    return null;
            }
        $tempToken = Str::random(60);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $tempToken,
                'created_at' => now()
            ]
        );
        return $tempToken;
    }

    public function resetPassword($request) {
        $reset = DB::table('password_reset_tokens')
            ->where('token', $request->token)
            ->first();
        if(!$reset) {
           throw new \Exception('Invalid token');
        }
        $user =$this->getUser($request);
        if(!$user) {
           throw new \Exception('User not found');
        }
        $user->update([
            'password' => Hash::make($request->password)
        ]);
    }
    public function getToken(User $user): string
    {
        return $user->createToken('auth_token.'.$user->username)->plainTextToken;
    }
    public function getUser($request)
    {
        return User::where('email', $request->email)->first();
    }
}
