<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can create a post', function () {
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('posts.store'), ['body' => 'test post']);
    $response->assertStatus(201);
    $this->assertDatabaseHas('posts', ['body' => 'test post']);
    $response->assertJsonStructure([
        'data' => [
            'id',
            'body',
            'image',
            'likes_count',
            'bookmark_count',
            'comments_count',
            'created_at',
            'updated_at',
            'user',
        ],
    ]);
});

it('can update a post', function () {
    $post = $this->user->posts()->create(['body' => 'test post']);
    $response = $this->actingAs($this->user, 'sanctum')->putJson(route('posts.update', $post),
        ['body' => 'updated test post']);
    $response->assertStatus(200);
    $this->assertDatabaseHas('posts', ['body' => 'updated test post']);
});

it('can delete a post', function () {
    $post = $this->user->posts()->create(['body' => 'test post']);
    $response = $this->actingAs($this->user, 'sanctum')->deleteJson(route('posts.destroy', $post));
    $response->assertStatus(200);
    $this->assertDatabaseMissing('posts', ['body' => 'test post']);
});

it('can not create a post without body or image', function () {
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('posts.store'));
    $response->assertStatus(422);
});

it('can not update a post that does not belong to the user', function () {
    $otherUser = User::factory()->create();
    $post = $otherUser->posts()->create(['body' => 'test post']);
    $response = $this->actingAs($this->user, 'sanctum')->putJson(route('posts.update', $post),
        ['body' => 'updated test post']);
    $response->assertStatus(403);
});

it('can not delete a post that does not belong to the user', function () {
    $otherUser = User::factory()->create();
    $post = $otherUser->posts()->create(['body' => 'test post']);
    $response = $this->actingAs($this->user, 'sanctum')->deleteJson(route('posts.destroy', $post));
    $response->assertStatus(403);
});
