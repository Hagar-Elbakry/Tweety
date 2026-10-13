<?php

use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->user = User::factory()->unverified()->create();
});

it('invalidates the old code and the new one verifies', function () {
    Mail::fake();
    insertOtp($this->user->email);

    resendAs($this->user)->assertStatus(200);

    $newCode = null;
    Mail::assertQueued(VerifyEmail::class, function (VerifyEmail $mail) use (&$newCode) {
        $newCode = $mail->otpCode;

        return true;
    });

    verifyAs($this->user, '123456')->assertStatus(422);
    verifyAs($this->user, $newCode)->assertStatus(200);
});

it('does not resend to an already verified user', function () {
    Mail::fake();
    $verifiedUser = User::factory()->create();

    resendAs($verifiedUser)->assertStatus(409);

    Mail::assertNothingQueued();
});
