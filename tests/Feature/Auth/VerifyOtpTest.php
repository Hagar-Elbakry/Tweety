<?php


use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('returns a reset token for a valid code', function () {
    insertOtp($this->user->email);

    $response = $this->postJson(route('auth.password.verify-otp'), [
        'email' => $this->user->email,
        'otp' => '123456',
    ]);

    $response->assertStatus(200)->assertJsonPath('data.expires_in', 900);
    expect($response->json('data.reset_token'))->toBeString()->not->toBeEmpty();
});

it('rejects a wrong code and keeps the real one valid', function () {
    insertOtp($this->user->email);

    $this->postJson(route('auth.password.verify-otp'), ['email' => $this->user->email, 'otp' => '000000'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('otp');

    $this->assertDatabaseHas('otps', ['identifier' => $this->user->email, 'valid' => 1]);
});

it('rejects an expired code', function () {
    insertOtp($this->user->email, true);

    $this->postJson(route('auth.password.verify-otp'), ['email' => $this->user->email, 'otp' => '123456'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('otp');
});

it('accepts a code only once', function () {
    insertOtp($this->user->email);
    $payload = ['email' => $this->user->email, 'otp' => '123456'];

    $this->postJson(route('auth.password.verify-otp'), $payload)->assertStatus(200);
    $this->postJson(route('auth.password.verify-otp'), $payload)->assertStatus(422);
});

it('rejects malformed input', function (array $payload, string $field) {
    $this->postJson(route('auth.password.verify-otp'), array_merge(
        ['email' => $this->user->email, 'otp' => '123456'],
        $payload
    ))->assertStatus(422)->assertJsonValidationErrors($field);
})->with([
    'missing otp' => [['otp' => null], 'otp'],
    'short otp' => [['otp' => '12345'], 'otp'],
    'letters in otp' => [['otp' => '12ab56'], 'otp'],
    'missing email' => [['email' => null], 'email'],
    'malformed email' => [['email' => 'not-an-email'], 'email'],
]);
