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
        protected AuthenticationService $userService
    ) {
    }

    public function sendOtp(ForgetPasswordRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $this->userService->sendPasswordResetOtp($data);

            return ApiResponse::success(message: 'We have sent an otp to reset your password');
        } catch (Exception $e) {
            Log::error('Error sending OTP: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error(message: 'Failed to send OTP, please try again later.', status: 500);
        }
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $token = $this->userService->verifyOtp($data);
            if (!$token) {
                return ApiResponse::error(message: 'Invalid Or Expired OTP', status: 401);
            }

            return ApiResponse::success(message: 'OTP verified. You can now reset your password.',
                data: ['token' => $token]);
        } catch (Exception $e) {
            Log::error('Error sending OTP: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error(message: 'Failed to send OTP, please try again later.');
        }
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = $request->user();
            $this->userService->resetPassword($data, $user);

            return ApiResponse::success(message: 'Your password has been reset');
        } catch (Exception $e) {
            Log::error('Error sending OTP: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error(message: 'Failed to reset password, please try again later.', status: 500);
        }
    }
}
