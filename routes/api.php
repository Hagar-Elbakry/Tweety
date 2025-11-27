<?php

use App\Http\Controllers\API\V1\Auth\AuthenticatedUserController;
use App\Http\Controllers\API\V1\Auth\EmailVerificationController;
use App\Http\Controllers\API\V1\Auth\PasswordResetController;
use App\Http\Controllers\API\V1\Auth\RegisterUserController;
use App\Http\Controllers\API\V1\Auth\SocialAuthController;
use App\Http\Controllers\PostController;

Route::prefix('v1')->group(function () {
    Route::post('/register', [RegisterUserController::class,'register'])->name('register');
    Route::post('/login', [AuthenticatedUserController::class,'login'])->name('login');
    Route::get('/google/redirect', [SocialAuthController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('google.callback');
    Route::post('/forget-password', [PasswordResetController::class,'sendOtp'])->name('sendOtp');
    Route::post('/verify-otp', [PasswordResetController::class,'verifyOtp'])->name('verifyOtp');
    Route::post('/reset-password', [PasswordResetController::class,'resetPassword'])->name('resetPassword');
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('email/verify', [EmailVerificationController::class, 'verify'])->name('verify');
    Route::get('email/verify/resend', [EmailVerificationController::class, 'resend'])->name('resend');
    Route::post('/logout', [AuthenticatedUserController::class,'logout'])->name('logout');

    Route::controller(PostController::class)->prefix('posts')->as('posts.')->group(function () {
        Route::post('', 'store')->name('store');
        Route::patch('{post}', 'update')->name('update');
        Route::delete('{post}', 'destroy')->name('destroy');
    });
});
