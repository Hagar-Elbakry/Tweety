<?php

use App\Events\UserRegistered;
use App\Models\User;
use Illuminate\Support\Facades\Event;


beforeEach(function () {
    $this->validData = [
        'name' => 'Test User',
        'username' => 'testuser',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];
});
it('allow user to register successfully', function () {
    Event::fake();
    $response = $this->postJson(
        '/api/v1/register',
        array_merge($this->validData, ['email' => fake()->unique()->safeEmail()])
    );
    Event::assertDispatched(UserRegistered::class);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'success',
        'message',
        'data' => [
            'user' => [
                'id',
                'name',
                'username',
                'email',
                'avatar',
                'banner',
                'bio',
                'created_at',
                'updated_at',
            ],
            'token',
        ],
    ]);
    $this->assertDatabaseHas('users', [
        'email' => $response->json('data.user.email'),
    ]);
});

it('fails registration if email is already taken', function () {
    $user = User::factory()->create();
    $response = $this->postJson(
        'api/v1/register',
        array_merge($this->validData, ['email' => $user->email])
    );
    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'data' => [
            'email' => ['The email has already been taken.'],
        ]
    ]);
});

it('fails registration with invalid data', function (array $invalidField) {
    $response = $this->postJson(
        '/api/v1/register',
        array_merge($this->validData, ['email' => fake()->unique()->safeEmail()], $invalidField)
    );
    $response->assertStatus(422);
})->with([
    'invalid username' => [['username' => '@testuser']],
    'taken username' => [fn() => ['username' => User::factory()->create()->username]],
    'password not match' => [['password_confirmation' => 'wrongpassword']],
]);
