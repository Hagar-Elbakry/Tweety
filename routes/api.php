<?php

use App\Http\Controllers\API\V1\Auth\AuthenticatedUserController;
use App\Http\Controllers\API\V1\Auth\EmailVerificationController;
use App\Http\Controllers\API\V1\Auth\PasswordResetController;
use App\Http\Controllers\API\V1\Auth\RegisterUserController;

Route::prefix('v1')->group(function () {
    Route::post('/register', [RegisterUserController::class,'register'])->name('register');
    Route::post('/login', [AuthenticatedUserController::class,'login'])->name('login');
    Route::post('/forget-password', [PasswordResetController::class,'sendOtp'])->name('sendOtp');
    Route::post('/verify-otp', [PasswordResetController::class,'verifyOtp'])->name('verifyOtp');
    Route::post('/reset-password', [PasswordResetController::class,'resetPassword'])->name('resetPassword');
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('email/verify', [EmailVerificationController::class, 'verify'])->name('verify');
    Route::get('email/verify/resend', [EmailVerificationController::class, 'resend'])->name('resend');
    Route::post('/logout', [AuthenticatedUserController::class,'logout'])->name('logout');
});
