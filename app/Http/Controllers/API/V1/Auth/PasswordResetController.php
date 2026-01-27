<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\AuthenticationService;

class PasswordResetController extends Controller
{
    public function __construct(
        protected AuthenticationService $userService
    )
    {}
    public function sendOtp(ForgetPasswordRequest $request) {
        $data = $request->validated();
        $this->userService->sendPasswordResetOtp($data);

        return ApiResponse::success(message: 'We have sent an otp to reset your password');
    }

    public function verifyOtp(VerifyOtpRequest $request) {
        $data = $request->validated();
        $token = $this->userService->verifyOtp($data);
        if (!$token) {
            return ApiResponse::error(message: 'Invalid Or Expired OTP', status: 401);
        }

        return ApiResponse::success(message: 'OTP verified. You can now reset your password.', data: ['token' => $token]);
    }

    public function resetPassword(ResetPasswordRequest $request) {
            $data = $request->validated();
            $this->userService->resetPassword($data);
            return ApiResponse::success(message: 'Your password has been reset');
    }
}
