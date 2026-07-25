<?php

use App\Models\User;
use App\Notifications\NewFollowNotification;


beforeEach(function () {
    $this->user = User::factory()->create();
});

it('get notifications', function () {
    $follower = User::factory()->create();
    $this->user->notify(new NewFollowNotification($follower, $this->user));
    $response = $this->actingAs($this->user, 'sanctum')->getJson(route('notifications'));
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'type',
                'user',
                'created_at'
            ]
        ]
    ]);
});

it('returns empty notifications when user  has no notifications', function () {
    $response = $this->actingAs($this->user, 'sanctum')->getJson(route('notifications'));
    $response->assertStatus(200);
    $response->assertJson([
        'data' => []
    ]);
});
