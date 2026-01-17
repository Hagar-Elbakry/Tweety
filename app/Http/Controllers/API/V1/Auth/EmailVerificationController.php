<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Mail\VerifyEmail;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EmailVerificationController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function verify (VerifyEmailRequest $request) {
        $result = $this->userService->verifyUserEmail($request);
        if($result) {
            return response()->json([
                'success' => true,
                'message' => 'User verified successfully'
            ]);
        }
           return response()->json([
               'success' => false,
               'message' => 'Invalid Or Expired OTP'
           ], 401);
    }

    public function resend() {
        $result = $this->userService->resendEmailVerificationOtp();
        if($result) {
            return response()->json([
                'success' => true,
                'message' => 'Resend Email Verification otp successfully'
            ]);
        }

            return response()->json([
                'success' => false,
                'message' => 'User already verified'
            ], 400);
    }
}
