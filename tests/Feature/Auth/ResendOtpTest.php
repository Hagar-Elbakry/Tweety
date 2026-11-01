<?php

use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('invalidates the old code and the new one verifies', function () {
    $user = User::factory()->unverified()->create();
    Mail::fake();
    insertOtp($user->email);

    resendAs($user)->assertStatus(200);

    $newCode = null;
    Mail::assertQueued(VerifyEmail::class, function (VerifyEmail $mail) use (&$newCode) {
        $newCode = $mail->otpCode;

        return true;
    });

    verifyAs($user, '123456')->assertStatus(422);
    verifyAs($user, $newCode)->assertStatus(200);
});

it('does not resend to an already verified user', function () {
    Mail::fake();
    $verifiedUser = User::factory()->create();

    resendAs($verifiedUser)->assertStatus(409);

    Mail::assertNothingQueued();
});
