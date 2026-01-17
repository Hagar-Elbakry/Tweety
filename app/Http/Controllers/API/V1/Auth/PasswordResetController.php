<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\UserService;

class PasswordResetController extends Controller
{
    protected $userService;
    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }
    public function sendOtp(ForgetPasswordRequest $request) {
        $this->userService->sendPasswordResetOtp($request);

        return ApiResponse::success(message: 'We have sent an otp to reset your password');
    }

    public function verifyOtp(VerifyOtpRequest $request) {
        $tempToken = $this->userService->verifyOtp($request);
        if (!$tempToken) {
            return ApiResponse::error(message: 'Invalid Or Expired OTP', status: 401);
        }

        return ApiResponse::success(message: 'We have sent an otp to reset your password', data: ['token' => $tempToken]);
    }

    public function resetPassword(ResetPasswordRequest $request) {
        try{
            $this->userService->resetPassword($request);
        }catch (\Exception $e){
            return ApiResponse::error(message: $e->getMessage(), status: 422);
        }

        return ApiResponse::success(message: 'Your password has been reset');
    }
}
