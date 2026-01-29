<?php

namespace App\Services;

use App\Events\UserRegistered;
use App\Mail\ResetPassword;
use App\Mail\VerifyEmail;
use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;

class AuthenticationService
{
    public function __construct(
        protected Otp $otp
    ) {}

    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create($data);
            $token = $this->getToken($user);
            $otpCode = $this->generateOtp($user->email);

            UserRegistered::dispatch($user, $otpCode);

            return compact('user', 'token');
        });
    }

    public function login(array $data): ?array
    {
        $user = $this->authenticate($data['email'], $data['password']);
        if (! $user) {
            return null;
        }
        $token = $this->getToken($user);

        return compact('user', 'token');
    }

    public function logout(Request $request): void
    {
        $request->user()->currentAccessToken()->delete();
    }

    public function redirectToGoogle(): string
    {
        $redirectUrl = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();

        return $redirectUrl;
    }

    public function handleGoogleCallback(): array
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        return DB::transaction(function () use ($googleUser) {
            $user = User::where('email', $googleUser->getEmail())->first();
            if (! $user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'username' => $this->generateUniqueUsername($googleUser->getName()),
                    'email' => $googleUser->getEmail(),
                    'password' => Str::random(8),
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'email_verified_at' => now(),
                ]);
                UserRegistered::dispatch($user, null);
            }
            $token = $this->getToken($user);

            return compact('user', 'token');
        });
    }

    public function verifyEmail(array $data): ?User
    {
        $user = auth()->user();

        return DB::transaction(function () use ($data, $user) {
            $validatedOtp = $this->otp->validate($user->email, $data['otp']);
            if (! $validatedOtp->status) {
                return null;
            }
            $user->update([
                'email_verified_at' => now(),
            ]);

            return $user;
        });
    }

    public function resendEmailVerificationOtp(): ?User
    {
        $user = auth()->user();
        if ($user->hasVerifiedEmail()) {
            return null;
        }
        $otpCode = $this->generateOtp($user->email);
        Mail::to($user)->queue(new VerifyEmail($user, $otpCode));

        return $user;
    }

    public function sendPasswordResetOtp(array $data): void
    {
        $user = $this->getUser($data['email']);
        $otpCode = $this->generateOtp($user->email);
        Mail::to($user)->queue(new ResetPassword($user, $otpCode));
    }

    public function verifyOtp(array $data): ?string
    {
        return DB::transaction(function () use ($data) {
            $validatedOtp = $this->otp->validate($data['email'], $data['otp']);
            if (! $validatedOtp->status) {
                return null;
            }
            $user = $this->getUser($data['email']);
            $token = $user->createToken('password_reset.'.$user->username, ['reset-password'], now()->addMinutes(15))->plainTextToken;

            return $token;
        });
    }

    public function resetPassword(array $data): void
    {
        $user = auth()->user();
        $user->update([
            'password' => $data['password'],
        ]);
        $user->tokens()->delete();
    }

    private function getToken(User $user): string
    {
        return $user->createToken('auth_token.'.$user->username)->plainTextToken;
    }

    private function getUser(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    private function authenticate(string $email, string $password): ?User
    {
        $user = $this->getUser($email);
        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    private function generateOtp(string $email): string
    {
        return $this->otp->generate($email, 'numeric', 6, 15)->token;
    }

    private function generateUniqueUsername(string $name): string
    {
        $username = Str::slug($name, '');
        $latestUsername = User::whereRaw('username REGEXP ?', ['^'.$username.'[0-9]*$'])
            ->orderByRaw('LENGTH(username) DESC')
            ->orderByDesc('username')
            ->first();
        if ($latestUsername) {
            $number = str_replace($username, '', $latestUsername->username);

            return $username.(is_numeric($number) ? (int) $number + 1 : 1);
        }

        return $username;
    }
}
