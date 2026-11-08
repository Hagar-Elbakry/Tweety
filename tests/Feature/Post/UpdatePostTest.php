<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create();
    $this->existingPost = $this->user->posts()->create(['body' => 'original']);
});

function createPostWithImage(User $user, ?string $body = 'with image'): Post
{
    $path = UploadedFile::fake()->image('old.jpg')->store('posts', 'public');

    return $user->posts()->create(['body' => $body, 'image' => $path]);
}

function updatePostAs(User $user, Post $post, array $data)
{
    return test()->actingAs($user, 'sanctum')->putJson(route('posts.update', $post), $data);
}

function updatePostWithFileAs(User $user, Post $post, array $data)
{
    return test()->actingAs($user, 'sanctum')->post(route('posts.update', $post), ['_method' => 'PUT'] + $data);
}

it('updates the body and returns the post', function () {
    $response = updatePostAs($this->user, $this->existingPost, ['body' => 'edited']);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id', 'body', 'image',
                'likes_count', 'bookmarks_count', 'comments_count', 'reposts_count',
                'created_at', 'updated_at',
                'user' => ['id', 'name', 'username', 'avatar'],
            ],
        ])
        ->assertJsonPath('data.body', 'edited')
        ->assertJsonPath('data.user.id', $this->user->id);

    $this->assertDatabaseHas('posts', ['id' => $this->existingPost->id, 'body' => 'edited']);
});

it('keeps the image when only the body changes', function () {
    $post = createPostWithImage($this->user);
    $image = $post->image;

    updatePostAs($this->user, $post, ['body' => 'edited'])->assertStatus(200);

    expect($post->fresh()->image)->toBe($image);
    Storage::disk('public')->assertExists($image);
});

it('replaces the image and deletes the old file', function () {
    $post = createPostWithImage($this->user);
    $old = $post->image;

    updatePostWithFileAs($this->user, $post, ['image' => UploadedFile::fake()->image('new.jpg')])
        ->assertStatus(200);

    $new = $post->fresh()->image;
    expect($new)->not->toBe($old);
    Storage::disk('public')->assertMissing($old);
    Storage::disk('public')->assertExists($new);
});

it('removes the image on request and deletes the file', function () {
    $post = createPostWithImage($this->user);
    $old = $post->image;

    updatePostAs($this->user, $post, ['remove_image' => 1])
        ->assertStatus(200)
        ->assertJsonPath('data.image', null);

    expect($post->fresh()->image)->toBeNull();
    Storage::disk('public')->assertMissing($old);
});

it('lets a post with an image lose its text', function () {
    $post = createPostWithImage($this->user);

    updatePostAs($this->user, $post, ['body' => null])->assertStatus(200);

    expect($post->fresh()->body)->toBeNull();
});

it('refuses an update that would leave a text-only post empty', function () {
    updatePostAs($this->user, $this->existingPost, ['body' => null])
        ->assertStatus(422)
        ->assertJsonValidationErrors('body');

    expect($this->existingPost->fresh()->body)->toBe('original');
});

it('refuses removing the image of an image-only post, and keeps the file', function () {
    $post = createPostWithImage($this->user, null);

    updatePostAs($this->user, $post, ['remove_image' => 1])
        ->assertStatus(422)
        ->assertJsonValidationErrors('body');

    Storage::disk('public')->assertExists($post->image);
});

it('does not let anyone else edit the post', function () {
    $other = User::factory()->create();

    updatePostAs($other, $this->existingPost, ['body' => 'hijacked'])->assertStatus(403);

    expect($this->existingPost->fresh()->body)->toBe('original');
});
