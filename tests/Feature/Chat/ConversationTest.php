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


    $this->messageForConversation1->created_at = now()->subMinutes(5);
    $this->messageForConversation1->save();
    $this->messageForConversation2->created_at = now();
    $this->messageForConversation2->save();
});

it('finds an existing conversation between two users instead of creating a duplicate', function () {
    $response = $this->actingAs($this->user1, 'sanctum')->postJson(route('conversations.store'), [
        'recipient_id' => $this->user2->id,
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseCount('conversations', 2);
});

it('creates a new conversation if none exists', function () {
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

    $response->assertStatus(422)->assertJsonValidationErrors('recipient_id');
    $this->assertDatabaseCount('conversations', 2);
});

it('lists conversations with the other participant and last message preview', function () {
    $response = $this->actingAs($this->user1, 'sanctum')->getJson(route('conversations.index'));

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            0 => [
                'id' => $this->conversation2->id,
                'other_user' => [
                    'id' => $this->user3->id,
                    'name' => $this->user3->name,
                    'username' => $this->user3->username,
                    'avatar' => $this->user3->avatar,
                ],
                'last_message' => [
                    'id' => $this->messageForConversation2->id,
                    'body' => $this->messageForConversation2->body,
                    'sender_id' => $this->user3->id,
                    'created_at' => $this->messageForConversation2->created_at->toIsoString(),
                ],
                'is_read' => false,
            ],
            1 => [
                'id' => $this->conversation1->id,
                'other_user' => [
                    'id' => $this->user2->id,
                    'name' => $this->user2->name,
                    'username' => $this->user2->username,
                    'avatar' => $this->user2->avatar,
                ],
                'last_message' => [
                    'id' => $this->messageForConversation1->id,
                    'body' => $this->messageForConversation1->body,
                    'sender_id' => $this->user1->id,
                    'created_at' => $this->messageForConversation1->created_at->toIsoString(),
                ],
                'is_read' => true,
            ],
        ],
    ]);
});

it('orders conversations by their latest message', function () {
    $newest = Message::create([
        'conversation_id' => $this->conversation1->id,
        'sender_id' => $this->user2->id,
        'body' => 'a brand new message',
    ]);
    $newest->created_at = now()->addMinute();
    $newest->save();

    $response = $this->actingAs($this->user1, 'sanctum')->getJson(route('conversations.index'));

    expect($response->json('data.*.id'))
        ->toBe([$this->conversation1->id, $this->conversation2->id]);
});

it('does not list conversations the user is not part of', function () {
    $stranger = User::factory()->create();

    $response = $this->actingAs($stranger, 'sanctum')->getJson(route('conversations.index'));

    $response->assertStatus(200);
    expect($response->json('data'))->toBeEmpty();
});

it('includes pagination meta', function () {
    $response = $this->actingAs($this->user1, 'sanctum')->getJson(route('conversations.index'));

    $response->assertJsonStructure([
        'meta' => ['current_page', 'per_page', 'total', 'last_page', 'has_more'],
    ]);
    $response->assertJsonPath('meta.total', 2);
    $response->assertJsonPath('meta.has_more', false);
});
