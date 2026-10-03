<?php

use App\Models\Conversation;
use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'password' => bcrypt('correct-password'),
    ]);

    $this->token = $this->user->createToken('test')->plainTextToken;
    $this->withToken($this->token);
});

it('allows a user to delete their account with the correct password', function () {
    $response = $this->deleteJson(route('profile.destroy'), [
        'password' => 'correct-password',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseMissing('users', [
        'id' => $this->user->id,
    ]);
});

it('rejects account deletion with an incorrect password', function () {
    $response = $this->deleteJson(route('profile.destroy'), [
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422);
    $this->assertDatabaseHas('users', [
        'id' => $this->user->id,
    ]);
});

it('revokes the current access token on deletion', function () {
    $this->deleteJson(route('profile.destroy'), [
        'password' => 'correct-password',
    ]);

    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $this->user->id,
    ]);
});

it('cascades the deletion to related data, such as posts', function () {
    $post = Post::create([
        'user_id' => $this->user->id,
        'body' => 'a post that should be deleted with me',
    ]);

    $this->deleteJson(route('profile.destroy'), [
        'password' => 'correct-password',
    ]);

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
    ]);
});

it('nullifies sender_id on messages instead of deleting them', function () {
    $otherUser = User::factory()->create();
    $conversation = Conversation::create();
    $conversation->users()->attach($this->user);
    $conversation->users()->attach($otherUser);

    $message = $conversation->messages()->create([
        'sender_id' => $this->user->id,
        'body' => 'a message that should survive, but orphaned',
    ]);

    $this->deleteJson(route('profile.destroy'), [
        'password' => 'correct-password',
    ]);

    $this->assertDatabaseHas('messages', [
        'id' => $message->id,
        'sender_id' => null,
    ]);
});
