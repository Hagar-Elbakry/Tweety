<?php

use App\Mail\ResetPassword;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


beforeEach(function () {
    $user = User::factory()->create();
    $this->validData = [
        'email' => $user->email
    ];
});

it('forgets a user\'s password', function () {
    Mail::fake();
    $response = $this->postJson(route('sendOtp'), $this->validData);
    $response->assertStatus(200);
    Mail::assertQueued(ResetPassword::class);
});

it('fails to forget password if email is doesnt\'s exist', function () {
    $response = $this->postJson(route('sendOtp'), ['email' => 'notExit@gmail.com']);
    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'data' => [
            'email' => ['The selected email is invalid.'],
        ]
    ]);
});

it('verifies otp', function () {
    Mail::fake();
    $this->postJson(route('sendOtp'), $this->validData);
    $otp = DB::table('otps')->where('identifier', $this->validData['email'])->value('token');
    $response = $this->postJson(route('verifyOtp'),
        [
            'email' => $this->validData['email'],
            'otp' => $otp
        ]);
    $response->assertStatus(200);
});

it('fails to verify wrong otp', function () {
    $this->postJson(route('sendOtp'), $this->validData);
    $response = $this->postJson(route('verifyOtp'), [
        'email' => $this->validData['email'],
        'otp' => '000000'
    ]);
    $response->assertStatus(401);
});
it('fails to verify expired otp', function () {
    insertOtp($this->validData['email'], true);
    $response = $this->postJson(route('verifyOtp'), [
        'email' => $this->validData['email'],
        'otp' => '123456'
    ]);
    $response->assertStatus(401);
});

it('allow user to reset password', function () {
    $this->postJson(route('sendOtp'), $this->validData);
    $otp = DB::table('otps')->where('identifier', $this->validData['email'])->value('token');
    $response = $this->postJson(route('verifyOtp'),
        [
            'email' => $this->validData['email'],
            'otp' => $otp
        ]);

    $token = $response->json('data.token');
    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson(route('resetPassword'), [
            'password' => 'password',
            'password_confirmation' => 'password'
        ])
        ->assertStatus(200);
});

it('fails to reset password if password doesnt match', function () {
    $this->postJson(route('sendOtp'), $this->validData);
    $otp = DB::table('otps')->where('identifier', $this->validData['email'])->value('token');
    $response = $this->postJson(route('verifyOtp'),
        [
            'email' => $this->validData['email'],
            'otp' => $otp
        ]);
    $token = $response->json('data.token');
    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson(route('resetPassword'), [
            'password' => 'password',
            'password_confirmation' => 'wrongpassword'
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
            'data' => [
                'password' => ['The password field confirmation does not match.'],
            ]
        ]);
});
