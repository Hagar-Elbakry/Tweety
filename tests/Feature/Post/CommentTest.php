<?php

use App\Models\User;
use App\Notifications\NewCommentNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->user = User::factory()->create();
    $otherUser = User::factory()->create();
    $this->post = $otherUser->posts()->create(['body' => 'test post']);
});

it('can comment on a post', function () {
    Notification::fake();
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('posts.comments.store', $this->post),
        ['body' => 'test comment']);
    $response->assertStatus(200);
    Notification::assertSentTo($this->post->user, NewCommentNotification::class);
    $this->assertDatabaseHas('comments', ['body' => 'test comment']);
});

it('can get comments on a post', function () {
    $this->post->comments()->create([
        'body' => 'test comment',
        'user_id' => $this->user->id,
    ]);
    $response = $this->actingAs($this->user, 'sanctum')->getJson(route('posts.comments.index', $this->post));
    $response->assertStatus(200);
    $response->assertJsonCount(1, 'data');
});

it('can get replies of a reply', function () {
    $comment = $this->post->comments()->create([
        'body' => 'test comment',
        'user_id' => $this->user->id,
    ]);
    $reply = $comment->replies()->create([
        'body' => 'test reply',
        'post_id' => $this->post->id,
        'user_id' => $this->user->id,
        'parent_id' => $comment->id,
    ]);
    $replyOfReply = $reply->replies()->create([
        'body' => 'test reply of reply',
        'post_id' => $this->post->id,
        'user_id' => $this->user->id,
        'parent_id' => $reply->id,
    ]);
    $response = $this->actingAs($this->user, 'sanctum')->getJson(route('comments.replies.index', $reply));
    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'comments' => [
                [
                    'id' => $replyOfReply->id,
                    'body' => 'test reply of reply',
                    'user' => [
                        'name' => $this->user->name,
                        'username' => $this->user->username,
                        'avatar' => $this->user->avatar,
                    ],
                    'has_more_replies' => false,
                ],
            ],
        ],
    ]);
});

it('returns next page of replies using cursor', function () {
    $comment = $this->post->comments()->create([
        'body' => 'test comment',
        'user_id' => $this->user->id,
    ]);
    $reply = $comment->replies()->create([
        'body' => 'test reply',
        'post_id' => $this->post->id,
        'user_id' => $this->user->id,
        'parent_id' => $comment->id,
    ]);
    $reply->replies()->createMany([
        [
            'body' => 'test reply of reply1',
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'parent_id' => $reply->id,
        ],
        [
            'body' => 'test reply of reply 2',
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'parent_id' => $reply->id,
        ],
        [
            'body' => 'test reply of reply 3',
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'parent_id' => $reply->id,
        ],
        [
            'body' => 'test reply of reply 4',
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'parent_id' => $reply->id,
        ],
    ]);
    $response = $this->actingAs($this->user, 'sanctum')->getJson(route('comments.replies.index', $reply));
    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data.comments');
    $nextPageUrl = $response->json('data.next_page_url');
    $this->actingAs($this->user, 'sanctum')
        ->getJson($nextPageUrl)
        ->assertStatus(200)
        ->assertJsonCount(1, 'data.comments');
});

it('can delete a comment on a post by comment owner', function () {
    $comment = $this->post->comments()->create([
        'body' => 'test comment',
        'user_id' => $this->user->id,
    ]);
    $response = $this->actingAs($this->user, 'sanctum')->deleteJson(route('comments.destroy', [$comment]));
    $response->assertStatus(200);
    $this->assertDatabaseMissing('comments', ['body' => 'test comment']);
});

it('can delete a comment on a post by post owner', function () {
    $comment = $this->post->comments()->create([
        'body' => 'test comment',
        'user_id' => $this->user->id,
    ]);
    $response = $this->actingAs($this->post->user, 'sanctum')->deleteJson(route('comments.destroy',
        [$comment]));
    $response->assertStatus(200);
    $this->assertDatabaseMissing('comments', ['body' => 'test comment']);
});

it('cannot delete a comment on a post by other user', function () {
    $otherUser = User::factory()->create();
    $comment = $this->post->comments()->create([
        'body' => 'test comment',
        'user_id' => $this->user->id,
    ]);
    $response = $this->actingAs($otherUser, 'sanctum')->deleteJson(route('comments.destroy', [$comment]));
    $response->assertStatus(403);
});
