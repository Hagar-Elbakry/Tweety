<?php

use App\Mail\WelcomeUserMail;
use App\Models\User as ModelsUser;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;

beforeEach(function () {
    Mail::fake();
    $this->googleEmail = 'test@gmail.com';
    $googleUser = (new User())->map([
        'id' => '123456789',
        'name' => 'Test User',
        'email' => 'test@gmail.com',
        'avatar' => null
    ]);
    Socialite::shouldReceive('driver->stateless->user')->andReturn($googleUser);
});

it('creates a user with google credentials', function () {
    $response = $this->get(route('google.callback'));
    $response->assertStatus(200);
    $this->assertDatabaseHas('users', ['email' => $this->googleEmail]);
    Mail::assertQueued(WelcomeUserMail::class);
    $response->assertJsonStructure([
        'data' => [
            'user',
            'token',
        ]
    ]);
});

it('login user if already exists with google credentials', function () {
    ModelsUser::factory()->create(['email' => $this->googleEmail]);
    $response = $this->get(route('google.callback'));
    $response->assertStatus(200);
    Mail::assertNotQueued(WelcomeUserMail::class);
    $this->assertDatabaseCount('users', 1);
    $response->assertJsonStructure([
        'data' => [
            'user',
            'token',
        ]
    ]);
});
