<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('shows my profile', function () {
    $response = $this->actingAs($this->user, 'sanctum')->getJson(route('profile.me'));
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'name',
            'username',
            'email',
            'posts_count',
            'avatar',
            'banner',
            'bio',
            'posts',
            'created_at',
            'updated_at',
        ]
    ]);
});

it('shows user profile', function () {
    $response = $this->getJson(route('profile.show', ['user' => $this->user->username]));
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'name',
            'username',
            'avatar',
            'banner',
            'bio',
            'posts',
            'created_at',
            'updated_at',
        ]
    ]);
});

it('can update profile', function () {
    $response = $this->actingAs($this->user, 'sanctum')->patchJson(route('profile.update'), [
        'name' => 'Updated Name',
        'username' => 'UpdatedUsername',
        'email' => 'updated@gmail.com',
        'bio' => 'Updated Bio',
    ]);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'name',
            'username',
            'email',
            'posts_count',
            'avatar',
            'banner',
            'bio',
            'created_at',
            'updated_at',
        ]
    ]);
    $this->assertDatabaseHas('users', [
        'name' => 'Updated Name',
        'username' => 'UpdatedUsername',
        'email' => 'updated@gmail.com',
        'bio' => 'Updated Bio',
    ]);
});
