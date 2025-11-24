<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Mail\VerifyEmail;
use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EmailVerificationController extends Controller
{
    private $otp;
    public function __construct()
    {
        $this->otp = new Otp();
    }
    public function verify (VerifyEmailRequest $request) {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validatedOtp = $this->otp->validate($request->email, $request->otp);
       if(!$validatedOtp->status) {
           return response()->json([
               'success' => false,
               'message' => 'Invalid Or Expired OTP'
           ], 401);
       }
       $user->update([
           'email_verified_at' => now()
       ]);

       return response()->json([
           'success' => true,
           'message' => 'User verified successfully'
       ]);
    }

    public function resend() {
        $user = Auth::user();
        if($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'User already verified'
            ]);
        }
        Mail::to($user)->queue(new VerifyEmail($user));
        return response()->json([
            'success' => true,
            'message' => 'Resend Email Verification otp successfully'
        ]);
    }
}
