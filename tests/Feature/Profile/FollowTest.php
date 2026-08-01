<?php

use App\Events\NewFollowCreated;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

it('can follow user', function () {
    Event::fake();
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('follow'), [
        'user_id' => $this->otherUser->id,
    ]);
    $response->assertStatus(200);
    Event::assertDispatched(NewFollowCreated::class);
    $response->assertJson([
        'message' => 'Successfully followed the user.',
    ]);
    $this->assertDatabaseCount('follows', 1);
});

it('can unfollow user', function () {
    $this->actingAs($this->user, 'sanctum')->postJson(route('follow'), [
        'user_id' => $this->otherUser->id,
    ]);
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('follow'), [
        'user_id' => $this->otherUser->id,
    ]);
    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Successfully unfollowed the user.',
    ]);
    $this->assertDatabaseCount('follows', 0);
});
it('can not follow myself', function () {
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('follow'), [
        'user_id' => $this->user->id,
    ]);
    $response->assertStatus(422);
});
