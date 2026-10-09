<?php

use App\Mail\VerifyEmail;
use App\Mail\WelcomeUserMail;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();

    $this->validData = [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@gmail.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];
});

it('sends the verification email to the registered address with a 6-digit code', function () {
    $this->postJson(route('auth.register'), $this->validData)->assertStatus(201);

    Mail::assertSent(VerifyEmail::class, function (VerifyEmail $mail) {
        return $mail->hasTo($this->validData['email'])
            && preg_match('/^\d{6}$/', $mail->otpCode) === 1;
    });
});

it('mails an otp that can actually verify the email', function () {
    $this->postJson(route('auth.register'), $this->validData)->assertStatus(201);

    $otp = null;
    Mail::assertSent(VerifyEmail::class, function (VerifyEmail $mail) use (&$otp) {
        $otp = $mail->otpCode;

        return true;
    });

    expect((new Otp)->validate($this->validData['email'], $otp)->status)->toBeTrue();
});

it('sends the welcome email to the registered address', function () {
    $this->postJson(route('auth.register'), $this->validData)->assertStatus(201);

    Mail::assertSent(WelcomeUserMail::class, fn (WelcomeUserMail $mail) => $mail->hasTo($this->validData['email']));
});

it('sends no emails when registration fails validation', function () {
    $this->postJson(route('auth.register'), array_merge($this->validData, ['email' => 'not-an-email']))
        ->assertStatus(422);

    Mail::assertNothingSent();
});
