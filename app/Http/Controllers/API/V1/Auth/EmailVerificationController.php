<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Services\AuthenticationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    public function __construct(
        protected AuthenticationService $userService
    ) {}

    public function verify(VerifyEmailRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = $request->user();
            $result = $this->userService->verifyEmail($data, $user);
            if (! $result) {
                return ApiResponse::error(message: 'Invalid Or Expired OTP', status: 401);
            }

            return ApiResponse::success(message: 'User verified successfully');
        } catch (Exception $e) {
            Log::error('Error verifying user: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(message: 'Could not verify user, please try again later.', status: 500);
        }
    }

    public function resend(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->userService->resendEmailVerificationOtp($user);
            if (! $result) {
                return ApiResponse::error(message: 'User already verified', status: 409);
            }

            return ApiResponse::success(message: 'Resend verification otp successfully');
        } catch (Exception $e) {
            Log::error('Error sending verification code: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(message: 'Could not send verification code, please try again later.',
                status: 500);
        }
    }
}
