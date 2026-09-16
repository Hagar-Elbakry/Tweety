<?php

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->conversation = Conversation::create();
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();
    $this->conversation->users()->attach($this->user1);
    $this->conversation->users()->attach($this->user2);
});

it('allows a participant to send a message', function () {
    $response = $this->actingAs($this->user1, 'sanctum')->postJson(route('conversations.messages.store',
        $this->conversation->id), [
            'body' => 'Hello, this is a test message.',
        ]);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'sender_name',
            'sender_avatar',
            'body',
            'attachments' => [
                '*' => [
                    'url',
                    'type',
                    'original_name',
                ],
            ],
            'sent_at',
            'read_by' => [
                '*' => [
                    'name',
                    'avatar',
                    'seen_at',
                ],
            ],
            'is_mine',
        ],
    ]);

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $this->conversation->id,
        'sender_id' => $this->user1->id,
        'body' => 'Hello, this is a test message.',
    ]);
});

it('rejects sending a message from a non-participant', function () {
    $otherUser = User::factory()->create();
    $response = $this->actingAs($otherUser, 'sanctum')->postJson(route('conversations.messages.store',
        $this->conversation->id), [
            'body' => 'Hello, this is a test message.',
        ]);
    $response->assertStatus(403);
});

it('requires body or attachments', function () {
    $response = $this->actingAs($this->user1, 'sanctum')->postJson(route('conversations.messages.store',
        $this->conversation->id));
    $response->assertStatus(422);
    $response->assertJsonStructure([
        'data' => ['body', 'attachments'],
    ]);
});

it('broadcasts MessageSent when a message is sent', function () {
    Event::fake();
    $this->actingAs($this->user1, 'sanctum')->postJson(route('conversations.messages.store',
        $this->conversation->id), [
            'body' => 'Hello, this is a test message.',
        ]);
    Event::assertDispatched(MessageSent::class);
});
