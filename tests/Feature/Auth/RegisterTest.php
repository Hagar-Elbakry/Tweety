<?php

use App\Events\UserRegistered;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->validData = [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@gmail.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];
});
it('allow user to register successfully', function () {
    Event::fake();
    $response = $this->postJson(route('register'), $this->validData);
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
    Event::fake();
    $user = User::factory()->create();
    $response = $this->postJson(
        route('register'),
        array_merge($this->validData, ['email' => $user->email])
    );
    Event::assertNotDispatched(UserRegistered::class);
    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'data' => [
            'email' => ['The email has already been taken.'],
        ],
    ]);
});

it('fails registration with invalid data', function (array $invalidField) {
    Event::fake();
    $response = $this->postJson(
        route('register'),
        array_merge($this->validData, $invalidField)
    );
    Event::assertNotDispatched(UserRegistered::class);
    $response->assertStatus(422);
})->with([
    'invalid username' => [['username' => '@testuser']],
    'taken username' => [fn () => ['username' => User::factory()->create()->username]],
    'password not match' => [['password_confirmation' => 'wrongpassword']],
]);
