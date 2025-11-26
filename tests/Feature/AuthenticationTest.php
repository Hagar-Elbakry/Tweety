<?php

namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $password = Hash::make('password');
        $response = $this->postJson('/api/v1/register', [
            'name' => 'test user',
            'username' => 'testuser',
            'email' => 'test@gmail.com',
            'password' => $password,
            'password_confirmation' => $password
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'username',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@gmail.com',
        ]);
    }

    public function test_user_can_login(): void {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => Hash::make('password')
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'username',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                    'token',
                ],
            ]);
    }
}


