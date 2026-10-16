<?php

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Two\User as GoogleUser;

beforeEach(function () {
    Mail::fake();
});

function fakeGoogleUser(array $attributes = [], bool $emailVerified = true): void
{
    $googleUser = (new GoogleUser)->map(array_merge([
        'id' => '123456789',
        'name' => 'Test User',
        'email' => 'test@gmail.com',
        'avatar' => null,
    ], $attributes))->setRaw(['email_verified' => $emailVerified]);

    Socialite::shouldReceive('driver->stateless->user')->andReturn($googleUser);
}

function googleLogin()
{
    return test()->postJson(route('google.callback'), ['code' => 'fake-code']);
}

it('returns the google url and a state to compare on the way back', function () {
    Socialite::shouldReceive('driver->stateless->with->redirect->getTargetUrl')
        ->andReturn('https://accounts.google.com/o/oauth2/auth?client_id=x');

    $response = $this->getJson(route('google.redirect'));

    $response->assertStatus(200)
        ->assertJsonPath('data.url', 'https://accounts.google.com/o/oauth2/auth?client_id=x');
    expect($response->json('data.state'))->toBeString()->toHaveLength(40);
});

it('creates a verified user from a google account and returns a token', function () {
    fakeGoogleUser();

    $response = googleLogin();

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'user' => [
                    'id', 'name', 'username', 'email', 'avatar', 'banner', 'bio', 'email_verified_at', 'created_at',
                    'updated_at'
                ],
                'token',
                'token_type',
                'expires_at',
                'is_new_user',
            ],
        ])
        ->assertJsonPath('data.is_new_user', true)
        ->assertJsonPath('data.token_type', 'Bearer');

    expect($response->json('data.token'))->toBeString();

    $user = User::where('email', 'test@gmail.com')->firstOrFail();
    expect($user->provider)->toBe('google')
        ->and($user->provider_id)->toBe('123456789')
        ->and($user->email_verified_at)->not->toBeNull();

    Mail::assertQueued(WelcomeUserMail::class, fn(WelcomeUserMail $mail) => $mail->hasTo('test@gmail.com'));
});

it('builds a username from the name and avoids collisions', function () {
    User::factory()->create(['username' => 'testuser']);
    fakeGoogleUser();

    $username = googleLogin()->json('data.user.username');

    expect($username)->toStartWith('testuser')->not->toBe('testuser');
});

it('returns a token that works on authenticated endpoints', function () {
    fakeGoogleUser();
    $token = googleLogin()->json('data.token');
    $this->app['auth']->forgetGuards();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson(route('profile.me'))
        ->assertStatus(200);
});

it('logs in an existing google user by google id, even if the email changed', function () {
    $user = User::factory()->create([
        'email' => 'old@gmail.com',
        'provider' => 'google',
        'provider_id' => '123456789',
    ]);
    fakeGoogleUser(['email' => 'test@gmail.com']);

    googleLogin()->assertStatus(200)
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.is_new_user', false);

    $this->assertDatabaseCount('users', 1);
    Mail::assertNothingQueued();
});

it('links google to an existing verified account without touching its password', function () {
    $user = User::factory()->create(['email' => 'test@gmail.com']);
    fakeGoogleUser();

    googleLogin()->assertStatus(200)->assertJsonPath('data.user.id', $user->id);

    $user->refresh();
    expect($user->provider)->toBe('google')
        ->and($user->provider_id)->toBe('123456789')
        ->and(Hash::check('password', $user->password))->toBeTrue();
    $this->assertDatabaseCount('users', 1);
    Mail::assertNothingQueued();
});

it('cuts off whoever pre-registered an unverified account with the same email', function () {
    $squatted = User::factory()->unverified()->create([
        'email' => 'test@gmail.com',
        'password' => 'attacker-knows-this',
    ]);
    $squatted->createToken('attacker-session');
    fakeGoogleUser();

    googleLogin()->assertStatus(200);

    $squatted->refresh();
    expect(Hash::check('attacker-knows-this', $squatted->password))->toBeFalse()
        ->and($squatted->email_verified_at)->not->toBeNull()
        ->and($squatted->tokens()->count())->toBe(1);

    $this->postJson(route('auth.login'), ['email' => 'test@gmail.com', 'password' => 'attacker-knows-this'])
        ->assertStatus(422);
});

it('refuses a google account whose email is not verified', function () {
    fakeGoogleUser([], false);

    googleLogin()->assertStatus(422)->assertJsonValidationErrors('email');

    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('personal_access_tokens', 0);
    Mail::assertNothingQueued();
});

it('refuses an email that is already linked to a different google account', function () {
    User::factory()->create([
        'email' => 'test@gmail.com',
        'provider' => 'google',
        'provider_id' => 'someone-else',
    ]);
    fakeGoogleUser();

    googleLogin()->assertStatus(409)->assertJsonValidationErrors('email');

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('throttles repeated callbacks', function () {
    foreach (range(1, 10) as $attempt) {
        $this->postJson(route('google.callback'), [])->assertStatus(422);
    }

    $this->postJson(route('google.callback'), [])->assertStatus(429);
});
