<?php

use App\Events\ConversationUpdated;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageRead;
use App\Models\User;
use Illuminate\Support\Facades\Event;

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
    $this->messageForConversation1FromUser2 = Message::create([
        'conversation_id' => $this->conversation1->id,
        'sender_id' => $this->user2->id,
        'body' => 'Test message for conversation 1 from user 2',
    ]);
    $this->messageForConversation2FromUser3 = Message::create([
        'conversation_id' => $this->conversation2->id,
        'sender_id' => $this->user3->id,
        'body' => 'Test message for conversation 2 from user 3',
    ]);
    $this->messageForConversation2FromUser1 = Message::create([
        'conversation_id' => $this->conversation2->id,
        'sender_id' => $this->user1->id,
        'body' => 'Test message for conversation 2 from user 1',
    ]);
});

it('returns zero when there are no unread messages', function () {
    MessageRead::create([
        'message_id' => $this->messageForConversation1FromUser2->id,
        'user_id' => $this->user1->id,
        'seen_at' => now(),
    ]);
    MessageRead::create([
        'message_id' => $this->messageForConversation2FromUser3->id,
        'user_id' => $this->user1->id,
        'seen_at' => now(),
    ]);

    $response = $this->actingAs($this->user1, 'sanctum')->getJson(route('conversations.unreadCount'));

    $response->assertStatus(200);
    $response->assertJson(['data' => ['unread_count' => 0]]);
});

it('counts unread messages sent by other users only', function () {
    $response = $this->actingAs($this->user1, 'sanctum')->getJson(route('conversations.unreadCount'));

    $response->assertStatus(200);
    $response->assertJson(['data' => ['unread_count' => 2]]);
});

it('broadcasts ConversationUpdated with the correct unread count', function () {
    Event::fake();
    $this->actingAs($this->user2, 'sanctum')->postJson(route('conversations.messages.store', $this->conversation1->id),
        [
            'body' => 'Test message for conversation 1',
        ]);

    Event::assertDispatched(ConversationUpdated::class, function ($event) {
        return $event->broadcastWith()['unread_message_count'] === 3;
    });
});
