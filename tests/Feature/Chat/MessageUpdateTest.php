<?php

use App\Events\MessageUpdated;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageRead;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->conversation = Conversation::create();
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();
    $this->conversation->users()->attach($this->user1);
    $this->conversation->users()->attach($this->user2);
    $this->message = Message::create([
        'body' => 'test message',
        'conversation_id' => $this->conversation->id,
        'sender_id' => $this->user1->id,
    ]);
});

it('allows the sender to update an unread message', function () {
    $response = $this->actingAs($this->user1, 'sanctum')->patchJson(route('conversations.messages.update',
        [$this->conversation->id, $this->message->id]), [
            'body' => 'updated message',
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
        'id' => $this->message->id,
        'body' => 'updated message',
    ]);
});

it('rejects updating a message sent by another user', function () {
    $response = $this->actingAs($this->user2, 'sanctum')->patchJson(route('conversations.messages.update',
        [$this->conversation->id, $this->message->id]), [
            'body' => 'updated message',
        ]);
    $response->assertStatus(403);
});

it('rejects updating a message that has already been seen', function () {
    MessageRead::create([
        'message_id' => $this->message->id,
        'user_id' => $this->user2->id,
        'seen_at' => now(),
    ]);

    $response = $this->actingAs($this->user1, 'sanctum')->patchJson(route('conversations.messages.update',
        [$this->conversation->id, $this->message->id]), [
            'body' => 'updated message',
        ]);
    $response->assertStatus(403);
});

it('broadcasts MessageUpdated when a message is updated', function () {
    Event::fake();
    $this->actingAs($this->user1, 'sanctum')->patchJson(route('conversations.messages.update',
        [$this->conversation->id, $this->message->id]), [
            'body' => 'updated message',
        ]);
    Event::assertDispatched(MessageUpdated::class);
});
