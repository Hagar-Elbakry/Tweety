<?php

use App\Http\Controllers\API\V1\Auth\AuthenticatedUserController;
use App\Http\Controllers\API\V1\Auth\EmailVerificationController;
use App\Http\Controllers\API\V1\Auth\PasswordResetController;
use App\Http\Controllers\API\V1\Auth\RegisterUserController;
use App\Http\Controllers\API\V1\Auth\SocialAuthController;
use App\Http\Controllers\API\V1\CommentController;
use App\Http\Controllers\API\V1\PostController;
use App\Http\Controllers\API\V1\ProfileController;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('/reset-password', [
        PasswordResetController::class, 'resetPassword',
    ])->name('resetPassword')->middleware('abilities:reset-password');
    Route::post('email/verify', [EmailVerificationController::class, 'verify'])->name('verify');
    Route::get('email/verify/resend', [EmailVerificationController::class, 'resend'])->name('resend');
    Route::post('/logout', [AuthenticatedUserController::class, 'logout'])->name('logout');

    Route::apiResource('posts', PostController::class)->except(['index', 'show']);
    Route::get('/profile/me', [ProfileController::class, 'me'])->name('profile.me');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/posts/{post}/like', [PostController::class, 'like'])->name('posts.like');
    Route::post('/posts/{post}/bookmark', [PostController::class, 'bookmark'])->name('posts.bookmark');
    Route::post('/posts/{post}/comment', [CommentController::class, 'store'])->name('comments.store');
    Route::get('/posts/{post}/comments', [CommentController::class, 'index'])->name('comments.index');
});

Route::prefix('v1')->group(function () {
    Route::post('/register', RegisterUserController::class)->name('register');
    Route::post('/login', [AuthenticatedUserController::class, 'login'])->name('login')->middleware('throttle:login');

    Route::get('/google/redirect', [SocialAuthController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('google.callback');

    Route::post('/forget-password', [PasswordResetController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/verify-otp', [PasswordResetController::class, 'verifyOtp'])->name('verifyOtp');
    Route::get('/profile/{user:username}', [ProfileController::class, 'show'])->name('profile.show');
});
