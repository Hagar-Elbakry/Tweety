<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => null,
    ]);
});

it('can verify email', function () {
    insertOtp($this->user->email);
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('verify'), ['otp' => '123456']);
    $response->assertStatus(200);
    $this->assertNotNull($this->user->fresh()->email_verified_at);
    $this->assertDatabaseHas('otps', ['valid' => 0]);
    $response->assertJson([
        'success' => true,
        'message' => 'User verified successfully',
    ]);
});

it('fails to verify email with wrong otp', function () {
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('verify'), ['otp' => '000000']);
    $response->assertStatus(401);
    $response->assertJson([
        'success' => false,
        'message' => 'Invalid Or Expired OTP',
    ]);
});

it('fails to verify email with expired otp', function () {
    insertOtp($this->user->email, true);
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('verify'), ['otp' => '123456']);
    $response->assertStatus(401);
    $response->assertJson([
        'success' => false,
        'message' => 'Invalid Or Expired OTP',
    ]);
});

it('resend email verification otp', function () {
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('resend'));
    $response->assertStatus(200);
    $this->assertDatabaseHas('otps', ['identifier' => $this->user->email]);
    $response->assertJson([
        'success' => true,
        'message' => 'Resend verification otp successfully',
    ]);
});

it('fails to resend email verification otp with verified email', function () {
    $this->user->markEmailAsVerified();
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('resend'));
    $response->assertStatus(409);
    $response->assertJson([
        'success' => false,
        'message' => 'User already verified',
    ]);
});
