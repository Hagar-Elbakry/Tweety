<?php

use App\Models\User;

beforeEach(function () {
    $this->user1 = User::factory()->create([
        'name' => 'Hagar Elbakry'
    ]);

    $this->user2 = User::factory()->create([
        'username' => 'seifAhmed'
    ]);

    $this->user3 = User::factory()->create([
        'name' => 'Hagar'
    ]);
});

it('finds users whose name starts with the query', function () {
    $response = $this->actingAs($this->user3, 'sanctum')->getJson(route('search.users', ['q' => 'Hagar']));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'name',
                'user_name',
                'avatar',
                'is_following'
            ]
        ]
    ]);
});

it('finds users whose username starts with the query', function () {
    $response = $this->actingAs($this->user3, 'sanctum')->getJson(route('search.users', ['q' => 'seif']));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'name',
                'user_name',
                'avatar',
                'is_following'
            ]
        ]
    ]);
});

it('does not return users whose name matches in the middle', function () {
    $response = $this->actingAs($this->user3, 'sanctum')->getJson(route('search.users', ['q' => 'Elbakry']));

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'No users found matching your search'
    ]);
});

it('excludes the current user from search results', function () {
    $response = $this->actingAs($this->user3, 'sanctum')->getJson(route('search.users', ['q' => 'Hagar']));
    $response->assertStatus(200);
    $response->assertJsonCount(1, 'data');
});
