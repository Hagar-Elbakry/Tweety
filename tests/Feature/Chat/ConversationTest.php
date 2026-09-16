<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

beforeEach(function () {
    $this->conversation1 = Conversation::create();
    $this->conversation2 = Conversation::create();
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();
    $this->user3 = User::factory()->create();
    $this->conversation1->users()->attach($this->user1);
    $this->conversation1->users()->attach($this->user2);
    $this->conversation2->users()->attach($this->user1);
    $this->conversation2->users()->attach($this->user3);
    $this->messageForConversation1 = Message::create([
        'conversation_id' => $this->conversation1->id,
        'sender_id' => $this->user1->id,
        'body' => 'Test message for conversation 1 from user 1',
    ]);
    $this->messageForConversation2 = Message::create([
        'conversation_id' => $this->conversation2->id,
        'sender_id' => $this->user3->id,
        'body' => 'Test message for conversation 2 from user 3',
    ]);
});

it('finds an existing conversation between two users instead of creating a duplicate', function () {
    $response = $this->actingAs($this->user1, 'sanctum')->postJson(route('conversations.store'), [
        'recipient_id' => $this->user2->id,
    ]);
    $response->assertStatus(200);
    $this->assertDatabaseCount('conversations', 2);
});

it('creates a new conversation if non exists', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($this->user1, 'sanctum')->postJson(route('conversations.store'), [
        'recipient_id' => $user->id,
    ]);
    $response->assertStatus(200);
    $this->assertDatabaseCount('conversations', 3);
});

it('rejects creating a conversation with yourself', function () {
    $response = $this->actingAs($this->user1, 'sanctum')->postJson(route('conversations.store'), [
        'recipient_id' => $this->user1->id,
    ]);
    $response->assertStatus(422);
    $this->assertDatabaseCount('conversations', 2);
});

it('lists conversations with the other participant and last message preview', function () {
    $response = $this->actingAs($this->user1, 'sanctum')->getJson(route('conversations.index'));
    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            0 => [
                'id' => $this->conversation1->id,
                'other_user' => [
                    'name' => $this->user2->name,
                    'avatar' => $this->user2->avatar,
                ],
                'last_message' => [
                    'body' => $this->messageForConversation1->body,
                    'sent_at' => $this->messageForConversation1->created_at->toIsoString(),
                ],
                'is_read' => false,
            ],
            1 => [
                'id' => $this->conversation2->id,
                'other_user' => [
                    'name' => $this->user3->name,
                    'avatar' => $this->user3->avatar,
                ],
                'last_message' => [
                    'body' => $this->messageForConversation2->body,
                    'sent_at' => $this->messageForConversation2->created_at->toIsoString(),
                ],
                'is_read' => false,
            ],
        ],
    ]);
});
