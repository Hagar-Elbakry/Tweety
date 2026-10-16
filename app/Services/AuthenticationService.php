<?php

namespace App\Services;

use App\Events\UserRegistered;
use App\Exceptions\SocialAuthException;
use App\Helpers\GeneratesOtp;
use App\Mail\ResetPassword;
use App\Mail\VerifyEmail;
use App\Mail\WelcomeUserMail;
use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;

class AuthenticationService
{
    private const TOKEN_LIFETIME_DAYS = 30;

    public function __construct(
        protected Otp $otp
    ) {
    }

    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create(Arr::only($data, ['name', 'username', 'email', 'password']));
            $token = $this->issueToken($user);
            $otpCode = GeneratesOtp::generateOtp($user->email);

            UserRegistered::dispatch($user, $otpCode);

            return ['user' => $user, ...$token];
        });
    }

    private function issueToken(User $user): array
    {
        $expiresAt = now()->addDays(self::TOKEN_LIFETIME_DAYS);
        return [
            'token' => $user->createToken(name: 'auth_token.'.$user->username, expiresAt: $expiresAt)->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIsoString(),
        ];
    }

    public function login(array $data): ?array
    {
        $user = $this->authenticate($data['email'], $data['password']);
        if (!$user) {
            return null;
        }
        $token = $this->issueToken($user);

        return ['user' => $user, ...$token];
    }

    private function authenticate(string $email, string $password): ?User
    {
        $user = $this->getUser($email);
        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    private function getUser(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function redirectToGoogle(): array
    {
        $state = Str::random(40);
        $url = Socialite::driver('google')->stateless()->with(['state' => $state])->redirect()->getTargetUrl();

        return ['url' => $url, 'state' => $state];
    }

    public function handleGoogleCallback(): array
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $email = $googleUser->getEmail();
        $emailVerified = filter_var($googleUser->user['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($email === '' || !$emailVerified) {
            throw new SocialAuthException('Your Google email address is not verified.');
        }

        return DB::transaction(function () use ($googleUser, $email) {
            $googleId = (string) $googleUser->getId();
            $isNewUser = false;

            $user = User::where('provider', 'google')->where('provider_id', $googleId)->first();
            if (!$user) {
                $user = User::where('email', $email)->first();

                if ($user) {
                    $this->linkGoogleAccount($user, $googleId);
                } else {
                    $user = User::create([
                        'name' => $googleUser->getName() ?: Str::before($email, '@'),
                        'username' => $this->generateUniqueUsername($googleUser->getName() ?: Str::before($email, '@')),
                        'email' => $email,
                        'password' => Str::random(32),
                        'provider' => 'google',
                        'provider_id' => $googleId,
                        'email_verified_at' => now(),
                    ]);
                    $isNewUser = true;

                    Mail::to($user)->queue(new WelcomeUserMail($user));
                }
            }
            $token = $this->issueToken($user);
            return ['user' => $user, 'is_new_user' => $isNewUser, ...$token];
        });
    }

    private function linkGoogleAccount(User $user, string $googleId): void
    {
        if ($user->provider_id !== null && $user->provider_id !== $googleId) {
            throw new SocialAuthException('This email is already linked to a different Google account.', 409);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->tokens()->delete();
            $user->password = Str::random(32);
        }

        $user->forceFill([
            'provider' => 'google',
            'provider_id' => $googleId,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();
    }

    private function generateUniqueUsername(string $name): string
    {
        $base = Str::limit(Str::slug($name, ''), 20, '') ?: 'user';
        $username = $base;

        while (User::where('username', $username)->exists()) {
            $username = $base.random_int(100, 99999);
        }
        
        return $username;
    }

    public function verifyEmail(string $otp, User $user): ?User
    {
        return DB::transaction(function () use ($otp, $user) {
            if (!$this->otp->validate($user->email, $otp)->status) {
                return null;
            }
            $user->update([
                'email_verified_at' => now(),
            ]);

            return $user;
        });
    }

    public function resendEmailVerificationOtp(User $user): ?User
    {
        if ($user->hasVerifiedEmail()) {
            return null;
        }
        $otpCode = GeneratesOtp::generateOtp($user->email);
        Mail::to($user)->queue(new VerifyEmail($user, $otpCode));

        return $user;
    }

    public function sendPasswordResetOtp(string $email): void
    {
        $user = $this->getUser($email);
        if (!$user) {
            return;
        }
        $otpCode = GeneratesOtp::generateOtp($user->email);
        Mail::to($user)->queue(new ResetPassword($user, $otpCode));
    }

    public function verifyOtp(string $email, string $otp): ?string
    {
        if (!$this->otp->validate($email, $otp)->status) {
            return null;
        }
        $user = $this->getUser($email);
        return $user ? Password::createToken($user) : null;
    }

    public function resetPassword(array $data): bool
    {
        $status = Password::reset(
            Arr::only($data, ['email', 'token', 'password']),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
                $user->tokens()->delete();
                event(new PasswordReset($user));
            }
        );
        return $status === Password::PASSWORD_RESET;
    }
}
