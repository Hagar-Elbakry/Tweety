<?php

use App\Models\User;

beforeEach(function () {
    config(['auth.passwords.users.expire' => 15]);
    $this->user = User::factory()->create();
});

function getResetToken(User $user): string
{
    insertOtp($user->email);

    return test()->postJson(route('auth.password.verify-otp'), [
        'email' => $user->email,
        'otp' => '123456',
    ])->json('data.reset_token');
}

function resetWith(string $email, string $token, string $password = 'new-password-123', ?string $confirmation = null)
{
    return test()->postJson(route('auth.password.reset'), [
        'email' => $email,
        'token' => $token,
        'password' => $password,
        'password_confirmation' => $confirmation ?? $password,
    ]);
}

it('resets the password, revokes every session, and the new password logs in', function () {
    $this->user->createToken('phone');
    $this->user->createToken('laptop');
    $token = getResetToken($this->user);

    resetWith($this->user->email, $token)->assertStatus(200);

    $this->assertDatabaseCount('personal_access_tokens', 0);
    $this->postJson(route('auth.login'), ['email' => $this->user->email, 'password' => 'new-password-123'])
        ->assertStatus(200);
});

it('no longer accepts the old password after a reset', function () {
    resetWith($this->user->email, getResetToken($this->user))->assertStatus(200);

    $this->postJson(route('auth.login'), ['email' => $this->user->email, 'password' => 'password'])
        ->assertStatus(422);
});

it('uses a reset token only once', function () {
    $token = getResetToken($this->user);

    resetWith($this->user->email, $token)->assertStatus(200);
    resetWith($this->user->email, $token, 'another-password-1')->assertStatus(422)->assertJsonValidationErrors('token');
});

it('rejects an invalid reset token', function () {
    getResetToken($this->user);

    resetWith($this->user->email, 'not-the-real-token')->assertStatus(422)->assertJsonValidationErrors('token');
});

it('does not let a token reset another user\'s password', function () {
    $other = User::factory()->create();
    $token = getResetToken($this->user);

    resetWith($other->email, $token)->assertStatus(422);

    expect(Hash::check('password', $other->fresh()->password))->toBeTrue();
});

it('rejects an expired reset token', function () {
    $token = getResetToken($this->user);

    $this->travel(16)->minutes();

    resetWith($this->user->email, $token)->assertStatus(422)->assertJsonValidationErrors('token');
});

it('gives no api access with the reset token', function () {
    $token = getResetToken($this->user);
    $this->app['auth']->forgetGuards();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson(route('profile.me'))
        ->assertStatus(401);
});

it('throttles repeated reset attempts', function () {
    foreach (range(1, 5) as $attempt) {
        resetWith($this->user->email, 'bad-token')->assertStatus(422);
    }

    resetWith($this->user->email, 'bad-token')->assertStatus(429);
});
