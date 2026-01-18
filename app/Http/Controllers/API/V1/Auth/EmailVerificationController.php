<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Services\UserService;

class EmailVerificationController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function verify (VerifyEmailRequest $request) {
        $result = $this->userService->verifyUserEmail($request);
        if(!$result) {
            return ApiResponse::error(message: 'Invalid Or Expired OTP', status: 401);
        }

        return ApiResponse::success(message: 'User verified successfully');
    }

    public function resend() {
        $result = $this->userService->resendEmailVerificationOtp();
        if(!$result) {
            return ApiResponse::error(message: 'User already verified');
        }

        return ApiResponse::success(message: 'Resend verification otp successfully');
    }
}
