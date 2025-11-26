<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Mail\ResetPassword;
use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    private $otp;
    public function __construct() {
        $this->otp = new Otp();
    }
    public function sendOtp(ForgetPasswordRequest $request) {
            $user = User::where('email', $request->email)->first();
            if(!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            Mail::to($user)->queue(new ResetPassword($user));
            return response()->json([
                'success' => true,
                'message' => 'We have sent an otp to reset your password'
            ]);
    }

    public function verifyOtp(VerifyOtpRequest $request) {
        $validatedOtp = $this->otp->validate($request->email, $request->otp);
        if(!$validatedOtp->status) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Or Expired OTP'
            ], 401);
        }

        $tempToken = Str::random(60);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $tempToken,
                'created_at' => now()
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'We have sent an otp to reset your password',
            'data' => [
                'token' => $tempToken
            ]
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request) {
            $reset = DB::table('password_reset_tokens')
                ->where('token', $request->token)
                ->first();
            if(!$reset) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token'
                ], 401);
            }

            $user = User::where('email', $reset->email)->first();
            if(!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Your password has been reset'
            ]);
    }
}
