<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Services\AuthenticationService;

class EmailVerificationController extends Controller
{
    public function __construct(
        protected AuthenticationService $userService
    ) {}

    public function verify(VerifyEmailRequest $request)
    {
        $data = $request->validated();
        $result = $this->userService->verifyEmail($data);
        if (! $result) {
            return ApiResponse::error(message: 'Invalid Or Expired OTP', status: 401);
        }

        return ApiResponse::success(message: 'User verified successfully');
    }

    public function resend()
    {
        try {
            $result = $this->userService->resendEmailVerificationOtp();
            if (! $result) {
                return ApiResponse::error(message: 'User already verified');
            }

            return ApiResponse::success(message: 'Resend verification otp successfully');
        } catch (\Exception $e) {
            return ApiResponse::error(message: 'Could not send verification code, please try again later.', status: 500);
        }
    }
}
