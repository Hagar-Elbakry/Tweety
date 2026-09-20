<?php

use App\Models\Post;
use App\Models\PostRepost;
use App\Models\User;

beforeEach(function () {
    $this->me = User::factory()->create();
    $this->followedUser = User::factory()->create();
    $this->strangerUser = User::factory()->create();

    $this->me->following()->attach($this->followedUser->id);
});

it('includes posts from followed users in the feed', function () {
    $post = Post::create([
        'user_id' => $this->followedUser->id,
        'body' => 'a post from someone I follow',
    ]);

    $response = $this->actingAs($this->me, 'sanctum')->getJson(route('feed'));

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            0 => [
                'type' => 'post',
                'post' => ['id' => $post->id],
            ],
        ],
    ]);
});

it('includes the authenticated users own posts in the feed', function () {
    $post = Post::create([
        'user_id' => $this->me->id,
        'body' => 'my own post',
    ]);

    $response = $this->actingAs($this->me, 'sanctum')->getJson(route('feed'));

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            0 => [
                'type' => 'post',
                'post' => ['id' => $post->id],
            ],
        ],
    ]);
});

it('excludes posts from users not followed', function () {
    Post::create([
        'user_id' => $this->strangerUser->id,
        'body' => 'a post from a stranger',
    ]);

    $response = $this->actingAs($this->me, 'sanctum')->getJson(route('feed'));

    $response->assertStatus(200);
    $response->assertJsonCount(0, 'data');
});

it('includes reposts from followed users sorted by repost time', function () {
    $strangerPost = Post::create([
        'user_id' => $this->strangerUser->id,
        'body' => 'an old post by a stranger',
    ]);
    $strangerPost->created_at = now()->subDays(10);
    $strangerPost->save();

    $freshPost = Post::create([
        'user_id' => $this->followedUser->id,
        'body' => 'a fresh post',
    ]);
    $freshPost->created_at = now()->subDays(1);
    $freshPost->save();

    PostRepost::create([
        'user_id' => $this->followedUser->id,
        'post_id' => $strangerPost->id,
        'type' => 'repost',
    ]);

    $response = $this->actingAs($this->me, 'sanctum')->getJson(route('feed'));

    $response->assertStatus(200);
    $response->assertJsonCount(2, 'data');
    $response->assertJson([
        'data' => [
            0 => [
                'type' => 'repost',
                'post' => ['id' => $strangerPost->id],
            ],
        ],
    ]);
});

it('paginates the feed results', function () {
    for ($i = 1; $i <= 15; $i++) {
        Post::create([
            'user_id' => $this->followedUser->id,
            'body' => "post number {$i}",
            'created_at' => now()->subMinutes($i),
        ]);
    }

    $firstPage = $this->actingAs($this->me, 'sanctum')->getJson(route('feed'));
    $firstPage->assertStatus(200);
    $firstPage->assertJsonCount(10, 'data');

    $secondPage = $this->actingAs($this->me, 'sanctum')->getJson(route('feed', ['page' => 2]));
    $secondPage->assertStatus(200);
    $secondPage->assertJsonCount(5, 'data');
});
