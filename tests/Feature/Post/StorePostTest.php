<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create();
});

it('creates a text post', function () {
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('posts.store'), ['body' => 'test post']);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id', 'body', 'image',
                'likes_count', 'bookmarks_count', 'comments_count', 'reposts_count',
                'created_at', 'updated_at',
                'user' => ['id', 'name', 'username', 'avatar'],
            ],
        ])
        ->assertJsonPath('data.body', 'test post')
        ->assertJsonPath('data.image', null)
        ->assertJsonPath('data.user.id', $this->user->id)
        ->assertJsonPath('data.likes_count', 0)
        ->assertJsonMissingPath('data.user.email');

    $this->assertDatabaseHas('posts', ['body' => 'test post', 'user_id' => $this->user->id]);
});

it('creates an image-only post and stores the file', function () {
    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('posts.store'), [
        'image' => UploadedFile::fake()->image('photo.jpg'),
    ]);

    $response->assertStatus(201);
    $post = Post::firstOrFail();
    expect($post->body)->toBeNull();
    Storage::disk('public')->assertExists($post->image);
    expect($response->json('data.image'))->toBeString()->toContain($post->image);
});

it('creates a post with text and image', function () {
    $this->actingAs($this->user, 'sanctum')->postJson(route('posts.store'), [
        'body' => 'with a picture',
        'image' => UploadedFile::fake()->image('photo.png'),
    ])->assertStatus(201);

    $post = Post::firstOrFail();
    expect($post->body)->toBe('with a picture');
    Storage::disk('public')->assertExists($post->image);
});
