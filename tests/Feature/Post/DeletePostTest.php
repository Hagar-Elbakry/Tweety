<?php

use App\Models\Post;
use App\Models\PostRepost;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create();
    $this->existingPost = $this->user->posts()->create(['body' => 'to delete']);
});

function makePostWithImage(User $user): Post
{
    $path = UploadedFile::fake()->image('photo.jpg')->store('posts', 'public');

    return $user->posts()->create(['body' => 'with image', 'image' => $path]);
}

it('deletes the post', function () {
    $this->actingAs($this->user, 'sanctum')
        ->deleteJson(route('posts.destroy', $this->existingPost))
        ->assertStatus(200);

    $this->assertDatabaseMissing('posts', ['id' => $this->existingPost->id]);
});

it('deletes the image file with the post', function () {
    $post = makePostWithImage($this->user);
    $path = $post->image;

    $this->actingAs($this->user, 'sanctum')->deleteJson(route('posts.destroy', $post))->assertStatus(200);

    Storage::disk('public')->assertMissing($path);
});

it('removes the reposts and quotes of the deleted post', function () {
    $fan = User::factory()->create();
    PostRepost::create(['user_id' => $fan->id, 'post_id' => $this->existingPost->id, 'type' => 'repost']);
    PostRepost::create([
        'user_id' => $fan->id,
        'post_id' => $this->existingPost->id,
        'type' => 'quote',
        'comment' => 'look at this',
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->deleteJson(route('posts.destroy', $this->existingPost))
        ->assertStatus(200);

    $this->assertDatabaseCount('post_reposts', 0);
});

it('does not let anyone else delete the post or its image', function () {
    $post = makePostWithImage($this->user);
    $other = User::factory()->create();

    $this->actingAs($other, 'sanctum')->deleteJson(route('posts.destroy', $post))->assertStatus(403);

    $this->assertDatabaseHas('posts', ['id' => $post->id]);
    Storage::disk('public')->assertExists($post->image);
});
