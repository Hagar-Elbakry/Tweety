<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Mail\ResetPassword;
use App\Models\User;
use App\Services\UserService;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    protected $userService;
    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }
    public function sendOtp(ForgetPasswordRequest $request) {
        $this->userService->sendPasswordResetOtp($request);

            return response()->json([
                'success' => true,
                'message' => 'We have sent an otp to reset your password'
            ]);
    }

    public function verifyOtp(VerifyOtpRequest $request) {
        $tempToken = $this->userService->verifyOtp($request);
        if ($tempToken) {
            return response()->json([
                'success' => true,
                'message' => 'We have sent an otp to reset your password',
                'data' => [
                    'token' => $tempToken
                ]
            ]);
        }

            return response()->json([
                'success' => false,
                'message' => 'Invalid Or Expired OTP'
            ], 401);
    }

    public function resetPassword(ResetPasswordRequest $request) {
        try{
            $this->userService->resetPassword($request);
        }catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ],422);
        }

            return response()->json([
                'success' => true,
                'message' => 'Your password has been reset'
            ]);
    }
}
