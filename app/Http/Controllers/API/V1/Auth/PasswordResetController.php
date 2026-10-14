<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\AuthenticationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PasswordResetController extends Controller
{
    public function __construct(
        protected AuthenticationService $authService
    ) {
    }

    public function sendOtp(ForgetPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->sendPasswordResetOtp($request->validated('email'));

            return ApiResponse::success(message: 'If an account exists for this email, we have sent a code to reset the password.');
        } catch (Exception $e) {
            Log::error('Error sending password reset OTP: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(message: 'Failed to send OTP, please try again later.', status: 500);
        }
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $token = $this->authService->verifyOtp(
                $request->validated('email'),
                (string) $request->validated('otp')
            );
            if (!$token) {
                return ApiResponse::error(
                    message: 'Invalid or expired OTP',
                    status: 422,
                    errors: ['otp' => ['Invalid or expired OTP']]
                );
            }

            return ApiResponse::success(
                message: 'OTP verified. You can now reset your password.',
                data: [
                    'reset_token' => $token,
                    'expires_in' => config('auth.passwords.users.expire') * 60,
                ]
            );
        } catch (Exception $e) {
            Log::error('Error verifying password reset OTP: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(message: 'Failed to verify OTP, please try again later.', status: 500);
        }
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            if (!$this->authService->resetPassword($request->validated())) {
                return ApiResponse::error(
                    message: 'Invalid or expired reset token',
                    status: 422,
                    errors: ['token' => ['Invalid or expired reset token']]
                );
            }

            return ApiResponse::success(message: 'Your password has been reset');
        } catch (Exception $e) {
            Log::error('Error resetting password: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(message: 'Failed to reset password, please try again later.', status: 500);
        }
    }
}
