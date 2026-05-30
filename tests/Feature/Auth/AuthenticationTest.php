<?php

use App\Models\User;


beforeEach(function () {
    $user = User::factory()->create([
        'email' => 'test@gmail.com',
        'password' => 'password',
    ]);
    $this->validData = [
        'email' => $user->email,
        'password' => 'password',
    ];
});

dataset('invalidData', [
    'wrong email' => [['email' => 'notExit@gmail.com']],
    'worng password' => [['password' => 'wrongpassword']]
]);

it('allow user to login', function () {
    $response = $this->postJson('/api/v1/login', $this->validData);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'message',
        'data' => [
            'user',
            'token',
        ]
    ]);
});

it('fails login with invalid data', function (array $invalidField) {
    $response = $this->postJson('/api/v1/login', array_merge($this->validData, $invalidField));
    $response->assertStatus(401);
    $response->assertJson([
        'success' => false,
        'message' => 'The provided credentials do not match our records.'
    ]);
})->with('invalidData');

it('allow user to logout', function () {
    $response = $this->postJson('/api/v1/login', $this->validData);
    $token = $response->json('data.token');

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/logout')
        ->assertStatus(200);

    auth()->forgetGuards();

    $response2 = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/logout');
    $response2->assertStatus(401);
});

