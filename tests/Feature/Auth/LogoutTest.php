<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['password' => 'password']);
});

function loginAndGetToken(User $user): string
{
    return test()->postJson(route('auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->json('data.token');
}

function callAsToken(string $token, string $method, string $routeName)
{
    app('auth')->forgetGuards();

    return test()->withHeader('Authorization', "Bearer {$token}")->{$method.'Json'}(route($routeName));
}

it('revokes the token that was used, and it stops working', function () {
    $token = loginAndGetToken($this->user);

    callAsToken($token, 'post', 'auth.logout')->assertStatus(200);

    $this->assertDatabaseCount('personal_access_tokens', 0);
    callAsToken($token, 'get', 'profile.me')->assertStatus(401);
});

it('only logs out the current device', function () {
    $phone = loginAndGetToken($this->user);
    $laptop = loginAndGetToken($this->user);

    callAsToken($phone, 'post', 'auth.logout')->assertStatus(200);

    $this->assertDatabaseCount('personal_access_tokens', 1);
    callAsToken($laptop, 'get', 'profile.me')->assertStatus(200);
});
