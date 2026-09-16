<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->conversation = Conversation::create();
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();
    $this->conversation->users()->attach($this->user1);
    $this->conversation->users()->attach($this->user2);
});

it('allows sending a message with an attachment and no body', function () {
    $file = UploadedFile::fake()->image('photo.jpg');

    $response = $this->actingAs($this->user1, 'sanctum')->postJson(
        route('conversations.messages.store', $this->conversation->id),
        ['attachments' => [$file]]
    );

    $response->assertStatus(200);
    $this->assertDatabaseHas('messages', [
        'conversation_id' => $this->conversation->id,
        'sender_id' => $this->user1->id,
        'body' => null,
    ]);
    $this->assertDatabaseCount('message_attachments', 1);
});

it('allows sending a message with both body and attachments', function () {
    $file = UploadedFile::fake()->image('photo.jpg');

    $response = $this->actingAs($this->user1, 'sanctum')->postJson(
        route('conversations.messages.store', $this->conversation->id),
        [
            'body' => 'Check this out',
            'attachments' => [$file],
        ]
    );

    $response->assertStatus(200);
    $this->assertDatabaseHas('messages', [
        'body' => 'Check this out',
    ]);
    $this->assertDatabaseCount('message_attachments', 1);
});

it('stores multiple attachments for a single message', function () {
    $file1 = UploadedFile::fake()->image('photo1.jpg');
    $file2 = UploadedFile::fake()->image('photo2.jpg');

    $response = $this->actingAs($this->user1, 'sanctum')->postJson(
        route('conversations.messages.store', $this->conversation->id),
        [
            'body' => 'Two photos',
            'attachments' => [$file1, $file2],
        ]
    );

    $response->assertStatus(200);

    $this->assertDatabaseCount('messages', 1);
    $this->assertDatabaseCount('message_attachments', 2);
});
