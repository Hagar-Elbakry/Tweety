<?php

use App\Http\Controllers\API\V1\Auth\AuthenticatedUserController;
use App\Http\Controllers\API\V1\Auth\EmailVerificationController;
use App\Http\Controllers\API\V1\Auth\PasswordResetController;
use App\Http\Controllers\API\V1\Auth\RegisterUserController;
use App\Http\Controllers\API\V1\Auth\SocialAuthController;
use App\Http\Controllers\API\V1\BlockController;
use App\Http\Controllers\API\V1\CommentController;
use App\Http\Controllers\API\V1\ConversationController;
use App\Http\Controllers\API\V1\FollowController;
use App\Http\Controllers\API\V1\MessageController;
use App\Http\Controllers\API\V1\NotificationsController;
use App\Http\Controllers\API\V1\PostController;
use App\Http\Controllers\API\V1\ProfileController;
use App\Http\Controllers\API\V1\ReplyController;
use App\Http\Controllers\API\V1\RepostController;
use App\Http\Controllers\API\V1\SearchController;
use App\Http\Controllers\API\V1\TypingController;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('/reset-password', [
        PasswordResetController::class, 'resetPassword',
    ])->name('resetPassword')->middleware('abilities:reset-password');
    Route::post('email/verify', [EmailVerificationController::class, 'verify'])->name('verify');
    Route::post('email/verify/resend',
        [EmailVerificationController::class, 'resend'])->name('resend')->middleware('throttle:resend-verification');
    Route::post('/logout', [AuthenticatedUserController::class, 'logout'])->name('logout');

    Route::apiResource('posts', PostController::class)->except(['index', 'show']);
    Route::get('/profile/me', [ProfileController::class, 'me'])->name('profile.me');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/posts/{post}/like', [PostController::class, 'like'])->name('posts.like');
    Route::post('/posts/{post}/bookmark', [PostController::class, 'bookmark'])->name('posts.bookmark');
    Route::apiResource('posts.comments', CommentController::class)
        ->shallow()
        ->only(['index', 'store', 'destroy']);
    Route::get('comments/{comment}/replies', ReplyController::class)->name('comments.replies.index');
    Route::post('/follow', FollowController::class)->name('follow');
    Route::get('/notifications', NotificationsController::class)->name('notifications');
    Route::post('/conversations', [ConversationController::class, 'store'])->name('conversations.store');
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/unread-count',
        [ConversationController::class, 'unreadCount'])->name('conversations.unreadCount');
    Route::get('/conversations/{conversation}/messages',
        [MessageController::class, 'index'])->name('conversations.messages.index');
    Route::post('/conversations/{conversation}/messages',
        [MessageController::class, 'store'])->name('conversations.messages.store');
    Route::post('/conversations/{conversation}/typing', TypingController::class)->name('conversations.typing');
    Route::delete('/conversations/{conversation}/messages/{message}',
        [MessageController::class, 'destroy'])->name('conversations.messages.destroy');
    Route::patch('/conversations/{conversation}/messages/{message}',
        [MessageController::class, 'update'])->name('conversations.messages.update');
    Route::post('/users/{user}/block', [BlockController::class, 'store'])->name('users.block');
    Route::delete('/users/{user}/block', [BlockController::class, 'destroy'])->name('users.unblock');
    Route::post('/posts/{post}/repost', [RepostController::class, 'store'])->name('repost.store');
    Route::delete('/posts/{post}/repost', [RepostController::class, 'destroy'])->name('repost.destroy');
    Route::post('/posts/{post}/quote', [RepostController::class, 'quote'])->name('repost.quote');
    Route::get('/users/search', [SearchController::class, 'users'])->name('search.users');
});

Route::prefix('v1')->group(function () {
    Route::post('/register', RegisterUserController::class)->name('register');
    Route::post('/login', [AuthenticatedUserController::class, 'login'])->name('login')->middleware('throttle:login');

    Route::get('/google/redirect', [SocialAuthController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('google.callback');

    Route::post('/forget-password',
        [PasswordResetController::class, 'sendOtp'])->name('sendOtp')->middleware('throttle:forgot-password');
    Route::post('/verify-otp', [PasswordResetController::class, 'verifyOtp'])->name('verifyOtp');
    Route::get('/profile/{user:username}', [ProfileController::class, 'show'])->name('profile.show');
});
