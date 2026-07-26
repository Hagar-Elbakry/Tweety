<?php

use App\Models\User;
use App\Notifications\NewCommentNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->user = User::factory()->create();
    $otherUser = User::factory()->create();
    $this->post = $otherUser->posts()->create(['body' => 'test post']);
});

it('can comment on a post', function () {
    Notification::fake();
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('posts.comments.store', $this->post),
        ['body' => 'test comment']);
    $response->assertStatus(200);
    Notification::assertSentTo($this->post->user, NewCommentNotification::class);
    $this->assertDatabaseHas('comments', ['body' => 'test comment']);
});

it('can get comments on a post', function () {
    $this->post->comments()->create([
        'body' => 'test comment',
        'user_id' => $this->user->id,
    ]);
    $response = $this->actingAs($this->user, 'sanctum')->getJson(route('posts.comments.index', $this->post));
    $response->assertStatus(200);
    $response->assertJsonCount(1, 'data');
});

it('can delete a comment on a post by comment owner', function () {
    $comment = $this->post->comments()->create([
        'body' => 'test comment',
        'user_id' => $this->user->id,
    ]);
    $response = $this->actingAs($this->user, 'sanctum')->deleteJson(route('comments.destroy', [$comment]));
    $response->assertStatus(200);
    $this->assertDatabaseMissing('comments', ['body' => 'test comment']);
});

it('can delete a comment on a post by post owner', function () {
    $comment = $this->post->comments()->create([
        'body' => 'test comment',
        'user_id' => $this->user->id,
    ]);
    $response = $this->actingAs($this->post->user, 'sanctum')->deleteJson(route('comments.destroy',
        [$comment]));
    $response->assertStatus(200);
    $this->assertDatabaseMissing('comments', ['body' => 'test comment']);
});

it('cannot delete a comment on a post by other user', function () {
    $otherUser = User::factory()->create();
    $comment = $this->post->comments()->create([
        'body' => 'test comment',
        'user_id' => $this->user->id,
    ]);
    $response = $this->actingAs($otherUser, 'sanctum')->deleteJson(route('comments.destroy', [$comment]));
    $response->assertStatus(403);
});
