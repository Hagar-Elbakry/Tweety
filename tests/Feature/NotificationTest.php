<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\NewCommentNotification;
use App\Notifications\NewFollowNotification;
use App\Notifications\NewLikeNotification;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

it('get notifications', function () {
    $post = Post::create([
        'body' => 'This is a test post',
        'user_id' => $this->otherUser->id
    ]);
    $comment = Comment::create([
        'body' => 'This is a test comment',
        'post_id' => $post->id,
        'user_id' => $this->user->id
    ]);
    $this->user->notify(new NewFollowNotification($this->otherUser, $this->user));
    $this->otherUser->notify(new NewLikeNotification($this->user, $post));
    $this->otherUser->notify(new NewCommentNotification($this->user, $this->otherUser, $comment));
    $response = $this->actingAs($this->user, 'sanctum')->getJson(route('notifications'));
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'type',
                'user',
                'created_at',
            ],
        ],
    ]);
});

it('returns empty notifications when user  has no notifications', function () {
    $response = $this->actingAs($this->user, 'sanctum')->getJson(route('notifications'));
    $response->assertStatus(200);
    $response->assertJson([
        'data' => [],
    ]);
});
