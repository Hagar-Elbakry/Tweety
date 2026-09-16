<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

beforeEach(function () {
    $this->conversation = Conversation::create();
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();
    $this->conversation->users()->attach($this->user1);
    $this->conversation->users()->attach($this->user2);
    $this->message = Message::create([
        'conversation_id' => $this->conversation->id,
        'sender_id' => $this->user1->id,
        'body' => 'Test message',
    ]);
});

it('allows a participant to delete a message for themselves', function () {
    $response = $this->actingAs($this->user1, 'sanctum')->deleteJson(route('conversations.messages.destroy',
        [$this->conversation->id, $this->message->id]));
    $response->assertStatus(200);
    $this->assertDatabaseHas('message_deletes', [
        'message_id' => $this->message->id,
        'user_id' => $this->user1->id,
    ]);
});

it('hides a deleted message only from the user who deleted it', function () {
    $this->actingAs($this->user1, 'sanctum')->deleteJson(route('conversations.messages.destroy',
        [$this->conversation->id, $this->message->id]));
    $response = $this->actingAs($this->user1, 'sanctum')->getJson(route('conversations.messages.index',
        [$this->conversation->id]));
    $response->assertStatus(200);
    $response->assertJsonMissing(['body' => $this->message->body]);
});

it('does not allow deleting the same message twice', function () {
    $this->actingAs($this->user1, 'sanctum')->deleteJson(route('conversations.messages.destroy',
        [$this->conversation->id, $this->message->id]));
    $this->actingAs($this->user1, 'sanctum')->deleteJson(route('conversations.messages.destroy',
        [$this->conversation->id, $this->message->id]));

    $this->assertDatabaseCount('message_deletes', 1);
});
