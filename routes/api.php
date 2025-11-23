<?php

use App\Http\Controllers\API\V1\Auth\AuthenticatedUserController;
use App\Http\Controllers\API\V1\Auth\RegisterUserController;

Route::prefix('v1')->group(function () {
    Route::post('/register', [RegisterUserController::class,'register'])->name('register');
    Route::post('/login', [AuthenticatedUserController::class,'login'])->name('login');
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticatedUserController::class,'logout'])->name('logout');
});
