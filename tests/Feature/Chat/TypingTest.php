<?php

use App\Events\UserTyping;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
    $this->conversation = Conversation::create();
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();
    $this->user3 = User::factory()->create();
    $this->conversation->users()->attach($this->user1);
    $this->conversation->users()->attach($this->user2);
});

it('broadcasts UserTyping when a participant sends a typing event', function () {
    $this->actingAs($this->user1, 'sanctum')->postJson(route('conversations.typing', $this->conversation->id));
    Event::assertDispatched(UserTyping::class);
});

it('rejects typing event from a non-participant', function () {
    $response = $this->actingAs($this->user3, 'sanctum')->postJson(route('conversations.typing',
        $this->conversation->id));
    $response->assertStatus(403);
    Event::assertNotDispatched(UserTyping::class);
});
