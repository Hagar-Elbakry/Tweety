<?php

use App\Models\Conversation;
use App\Models\User;

beforeEach(function () {
    $this->conversation = Conversation::create();
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();
    $this->conversation->users()->attach($this->user1);
    $this->conversation->users()->attach($this->user2);
});

it('allows a user to block another user', function () {
    $response = $this->actingAs($this->user1, 'sanctum')->postJson(route('users.block', $this->user2->id));
    $response->assertStatus(200);
    $this->assertDatabaseHas('user_blocks', [
        'blocker_id' => $this->user1->id,
        'blocked_id' => $this->user2->id,
    ]);
});

it('prevents blocking the same user twice', function () {
    $this->actingAs($this->user1, 'sanctum')->postJson(route('users.block', $this->user2->id));
    $response = $this->actingAs($this->user1, 'sanctum')->postJson(route('users.block', $this->user2->id));
    $response->assertStatus(200);
    $this->assertDatabaseCount('user_blocks', 1);
});

it('allows a user to unblock another user', function () {
    $this->actingAs($this->user1, 'sanctum')->postJson(route('users.block', $this->user2->id));
    $response = $this->actingAs($this->user1, 'sanctum')->deleteJson(route('users.unblock', $this->user2->id));
    $response->assertStatus(200);
    $this->assertDatabaseMissing('user_blocks', [
        'blocker_id' => $this->user1->id,
        'blocked_id' => $this->user2->id,
    ]);
});

it('rejects blocking yourself', function () {
    $response = $this->actingAs($this->user1, 'sanctum')->postJson(route('users.block', $this->user1->id));
    $response->assertStatus(403);
});

it('prevents a blocked user from sending messages', function () {
    $this->actingAs($this->user1, 'sanctum')->postJson(route('users.block', $this->user2->id));
    $response = $this->actingAs($this->user2, 'sanctum')->postJson(route('conversations.messages.store',
        $this->conversation->id), [
        'body' => 'Test message',
    ]);
    $response->assertStatus(403);
});

it('prevents the blocker from sending messages to the blocked user', function () {
    $this->actingAs($this->user1, 'sanctum')->postJson(route('users.block', $this->user2->id));
    $response = $this->actingAs($this->user1, 'sanctum')->postJson(route('conversations.messages.store',
        $this->conversation->id), [
        'body' => 'Test message',
    ]);
    $response->assertStatus(403);
});
