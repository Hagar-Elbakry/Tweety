<?php

use App\Models\User;
use App\Notifications\NewLikeNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->user = User::factory()->create();
    $otherUser = User::factory()->create();
    $this->post = $otherUser->posts()->create(['body' => 'test post']);
});

it('can like a post', function () {
    Notification::fake();
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('posts.like', $this->post));
    $response->assertStatus(200);
    Notification::assertSentTo($this->post->user, NewLikeNotification::class);
    $response->assertJson([
        'message' => 'Post liked successfully.',
    ]);
});

it('can unlike a post', function () {
    $this->actingAs($this->user, 'sanctum')->postJson(route('posts.like', $this->post));
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('posts.like', $this->post));
    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Post unliked successfully.',
    ]);
});

it('can bookmark a post', function () {
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('posts.bookmark', $this->post));
    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Post bookmarked successfully',
    ]);
});

it('can unbookmark a post', function () {
    $this->actingAs($this->user, 'sanctum')->postJson(route('posts.bookmark', $this->post));
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('posts.bookmark', $this->post));
    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Post unbookmarked successfully',
    ]);
});
