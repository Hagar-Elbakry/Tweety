<?php

use App\Models\Post;
use App\Models\User;
use App\Notifications\NewRepostNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();
    $this->post = Post::create([
        'user_id' => $this->user1->id,
        'body' => 'post body'
    ]);
});

it('allows a user to repost a post', function () {
    $response = $this->actingAs($this->user2, 'sanctum')->postJson(route('repost.store', $this->post->id));

    $response->assertStatus(200);
    $this->assertDatabaseHas('post_reposts', [
        'post_id' => $this->post->id,
        'user_id' => $this->user2->id,
        'type' => 'repost'
    ]);
});

it('prevents reposting the same post twice', function () {
    $this->actingAs($this->user2, 'sanctum')->postJson(route('repost.store', $this->post->id));
    $this->actingAs($this->user2, 'sanctum')->postJson(route('repost.store', $this->post->id));

    $this->assertDatabaseCount('post_reposts', 1);
});

it('allows a user to unrepost a post', function () {
    $this->actingAs($this->user2, 'sanctum')->postJson(route('repost.store', $this->post->id));
    $response = $this->actingAs($this->user2, 'sanctum')->deleteJson(route('repost.destroy', $this->post->id));
    $response->assertStatus(200);
    $this->assertDatabaseMissing('post_reposts', [
        'post_id' => $this->post->id,
        'user_id' => $this->user2->id,
        'type' => 'repost'
    ]);
});

it('allows a user to quote a post', function () {
    $response = $this->actingAs($this->user2, 'sanctum')->postJson(route('repost.quote', $this->post->id), [
        'comment' => 'quote on post'
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('post_reposts', [
        'post_id' => $this->post->id,
        'user_id' => $this->user2->id,
        'type' => 'quote',
        'comment' => 'quote on post'
    ]);
});

it('allows quoting the same post multiple times', function () {
    $this->actingAs($this->user2, 'sanctum')->postJson(route('repost.quote', $this->post->id), [
        'comment' => 'quote1 on post'
    ]);

    $this->actingAs($this->user2, 'sanctum')->postJson(route('repost.quote', $this->post->id), [
        'comment' => 'quote2 on post'
    ]);

    $this->assertDatabaseCount('post_reposts', 2);
});

it('rejects a quote without a comment', function () {
    $response = $this->actingAs($this->user2, 'sanctum')->postJson(route('repost.quote', $this->post->id));
    $response->assertStatus(422);
    $this->assertDatabaseCount('post_reposts', 0);
});

it('sends a notification to the post owner when reposted', function () {
    Notification::fake();
    $this->actingAs($this->user2, 'sanctum')->postJson(route('repost.store', $this->post->id));
    Notification::assertSentTo($this->user1, NewRepostNotification::class);
});

it('does not send a notification when reposting your own post', function () {
    Notification::fake();
    $this->actingAs($this->user1, 'sanctum')->postJson(route('repost.store', $this->post->id));
    Notification::assertNothingSentTo($this->user1);
});
