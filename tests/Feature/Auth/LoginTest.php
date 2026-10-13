<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'test@gmail.com',
        'password' => 'password',
    ]);
    $this->validData = [
        'email' => 'test@gmail.com',
        'password' => 'password',
    ];
});

it('logs a user in and returns the user with a token', function () {
    $response = $this->postJson(route('auth.login'), $this->validData);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id', 'name', 'username', 'email', 'avatar', 'banner', 'bio', 'email_verified_at', 'created_at',
                    'updated_at'
                ],
                'token',
                'token_type',
                'expires_at',
            ],
        ])
        ->assertJsonPath('data.user.id', $this->user->id)
        ->assertJsonPath('data.token_type', 'Bearer');

    expect($response->json('data.token'))->toBeString();
});

it('issues a token that works on authenticated endpoints', function () {
    $token = $this->postJson(route('auth.login'), $this->validData)->json('data.token');
    $this->app['auth']->forgetGuards();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson(route('profile.me'))
        ->assertStatus(200);
});

it('creates a new token on every login', function () {
    $this->postJson(route('auth.login'), $this->validData);
    $this->postJson(route('auth.login'), $this->validData);

    $this->assertDatabaseCount('personal_access_tokens', 2);
});

it('rejects wrong credentials with a validation error', function (array $override) {
    $response = $this->postJson(route('auth.login'), array_merge($this->validData, $override));

    $response->assertStatus(422)->assertJsonValidationErrors('email');
    $this->assertDatabaseCount('personal_access_tokens', 0);
})->with([
    'unknown email' => [['email' => 'nobody@gmail.com']],
    'wrong password' => [['password' => 'wrong-password']],
]);

it('rejects invalid input', function (array $override, string $field) {
    $this->postJson(route('auth.login'), array_merge($this->validData, $override))
        ->assertStatus(422)
        ->assertJsonValidationErrors($field);
})->with([
    'missing email' => [['email' => null], 'email'],
    'malformed email' => [['email' => 'not-an-email'], 'email'],
    'missing password' => [['password' => null], 'password'],
]);

it('throttles repeated failed attempts for the same email', function () {
    foreach (range(1, 5) as $attempt) {
        $this->postJson(route('auth.login'), array_merge($this->validData, ['password' => 'wrong']))
            ->assertStatus(422);
    }

    $this->postJson(route('auth.login'), $this->validData)->assertStatus(429);
});
