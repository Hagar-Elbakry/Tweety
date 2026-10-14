<?php

use App\Mail\ResetPassword;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('mails a 6-digit reset code to a registered email', function () {
    Mail::fake();
    $this->postJson(route('auth.password.forgot'), ['email' => $this->user->email])->assertStatus(200);
    Mail::assertQueued(ResetPassword::class, fn(ResetPassword $mail) => $mail->hasTo($this->user->email)
        && preg_match('/^\d{6}$/', $mail->otpCode) === 1);
});

it('invalidates the previous code when a new one is requested', function () {
    Mail::fake();
    insertOtp($this->user->email);

    $this->postJson(route('auth.password.forgot'), ['email' => $this->user->email])->assertStatus(200);

    $this->postJson(route('auth.password.verify-otp'), ['email' => $this->user->email, 'otp' => '123456'])
        ->assertStatus(422);
});

it('allows one code request per minute for the same email', function () {
    Mail::fake();

    $this->postJson(route('auth.password.forgot'), ['email' => $this->user->email])->assertStatus(200);
    $this->postJson(route('auth.password.forgot'), ['email' => $this->user->email])->assertStatus(429);

    $other = User::factory()->create();
    $this->postJson(route('auth.password.forgot'), ['email' => $other->email])->assertStatus(200);
});
