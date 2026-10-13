<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->unverified()->create();
});

it('verifies the email with a valid otp and returns the updated user', function () {
    insertOtp($this->user->email);
    $response = verifyAs($this->user, '123456');
    $response->assertStatus(200)
        ->assertJsonPath('data.user.id', $this->user->id);
    expect($response->json('data.user.email_verified_at'))->not->toBeNull();
    expect($this->user->fresh()->email_verified_at)->not->toBeNull();
    $this->assertDatabaseHas('otps', ['identifier' => $this->user->email, 'valid' => 0]);
});

it('rejects a wrong code while a valid otp exists, and leaves everything untouched', function () {
    insertOtp($this->user->email);
    $response = verifyAs($this->user, '000000');

    $response->assertStatus(422)->assertJsonValidationErrors('otp');
    expect($this->user->fresh()->email_verified_at)->toBeNull();
    $this->assertDatabaseHas('otps', ['identifier' => $this->user->email, 'valid' => 1]);
});

it('rejects an expired otp', function () {
    insertOtp($this->user->email, true);

    verifyAs($this->user, '123456')->assertStatus(422)->assertJsonValidationErrors('otp');
    expect($this->user->fresh()->email_verified_at)->toBeNull();
});

it('rejects a malformed otp', function (mixed $otp) {
    verifyAs($this->user, $otp)->assertStatus(422)->assertJsonValidationErrors('otp');
})->with([
    'missing' => [null],
    'too short' => ['12345'],
    'too long' => ['1234567'],
    'letters' => ['12ab56'],
]);

it('does not re-verify an already verified user', function () {
    $verifiedUser = User::factory()->create();
    $verifiedAt = $verifiedUser->email_verified_at;
    insertOtp($verifiedUser->email);

    verifyAs($verifiedUser, '123456')->assertStatus(409);
    expect($verifiedUser->fresh()->email_verified_at->equalTo($verifiedAt))->toBeTrue();
});

it('throttles repeated verification attempts', function () {
    foreach (range(1, 5) as $attempt) {
        verifyAs($this->user, '000000')->assertStatus(422);
    }

    insertOtp($this->user->email);

    verifyAs($this->user, '123456')->assertStatus(429);
    expect($this->user->fresh()->email_verified_at)->toBeNull();
});
