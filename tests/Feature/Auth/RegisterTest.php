<?php

use App\Events\UserRegistered;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
    $this->validData = [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@gmail.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];
});
it('registers a user and returns the user with a token', function () {
    $response = $this->postJson(route('auth.register'), $this->validData);
    $response->assertStatus(201)
        ->assertJsonStructure([
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
                'token_type',
                'expires_at',
            ],
        ])
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonPath('data.user.email', $this->validData['email']);

    $this->assertDatabaseHas('users', [
        'email' => $this->validData['email'],
        'username' => $this->validData['username'],
    ]);
});

it('stores the password hashed and never returns it', function () {
    $data = array_merge($this->validData, [
        'password' => 'S3cret-Pass!',
        'password_confirmation' => 'S3cret-Pass!',
    ]);

    $response = $this->postJson(route('auth.register'), $data);

    $user = User::where('email', $data['email'])->firstOrFail();

    expect($user->password)->not->toBe($data['password'])
        ->and(Hash::check($data['password'], $user->password))->toBeTrue();

    $response->assertJsonMissingPath('data.user.password');
    expect($response->getContent())->not->toContain($data['password']);
});

it('issues a token that expires in about 30 days', function () {
    $this->postJson(route('auth.register'), $this->validData);

    $token = User::where('email', $this->validData['email'])->firstOrFail()->tokens()->firstOrFail();

    expect($token->expires_at->between(now()->addDays(29), now()->addDays(31)))->toBeTrue();
});

it('dispatches UserRegistered with a 6-digit otp', function () {
    $this->postJson(route('auth.register'), $this->validData);

    Event::assertDispatched(UserRegistered::class, function ($event) {
        return $event->user->email === $this->validData['email']
            && preg_match('/^\d{6}$/', (string) $event->otpCode) === 1;
    });
});

it('rejects invalid registration data', function (array $override, string $field) {
    $response = $this->postJson(route('auth.register'), array_merge($this->validData, $override));

    $response->assertStatus(422)->assertJsonValidationErrors($field);
    Event::assertNotDispatched(UserRegistered::class);
    $this->assertDatabaseCount('users', 0);
})->with([
    'missing name' => [['name' => null], 'name'],
    'missing username' => [['username' => null], 'username'],
    'username with a symbol' => [['username' => '@testuser'], 'username'],
    'username with a space' => [['username' => 'test user'], 'username'],
    'username too short' => [['username' => 'ab'], 'username'],
    'missing email' => [['email' => null], 'email'],
    'malformed email' => [['email' => 'not-an-email'], 'email'],
    'password too short' => [['password' => 'short', 'password_confirmation' => 'short'], 'password'],
    'password confirmation mismatch' => [['password_confirmation' => 'different'], 'password'],
]);

it('rejects an already taken email', function () {
    $existing = User::factory()->create();

    $this->postJson(route('auth.register'), array_merge($this->validData, ['email' => $existing->email]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');

    Event::assertNotDispatched(UserRegistered::class);
});

it('rejects an already taken username', function () {
    User::factory()->create(['username' => 'takenuser']);

    $this->postJson(route('auth.register'), array_merge($this->validData, ['username' => 'takenuser']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('username');
});

it('throttles repeated registration attempts', function () {
    foreach (range(1, 5) as $attempt) {
        $this->postJson(route('auth.register'), []);
    }

    $this->postJson(route('auth.register'), [])->assertStatus(429);
});
