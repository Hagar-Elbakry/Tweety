<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Traits\Uploadable;
use Exception;

class PostService
{
    use Uploadable;

    public function create(array $data, User $user): Post
    {
        if (isset($data['image'])) {
            $data['image'] = $this->uploadImage($data['image'], 'posts');
        }
        $post = $user->posts()->create($data);

        return $post->load('user')->loadCount(['comments', 'likes', 'bookmarks']);
    }

    public function update(array $data, Post $post): Post
    {
        $newImagePath = null;
        $oldImagePath = $post->image;
        try {
            if (isset($data['image'])) {
                $newImagePath = $this->uploadImage($data['image'], 'posts');
                $data['image'] = $newImagePath;
            }
            $post->update($data);
            if ($newImagePath && $oldImagePath) {
                $this->deleteImage($oldImagePath);
            }

            return $post->load('user')->loadCount(['comments', 'likes', 'bookmarks']);
        } catch (Exception $e) {
            if ($newImagePath) {
                $this->deleteImage($newImagePath);
            }
            throw $e;
        }
    }

    public function delete(Post $post): void
    {
        $imagePath = $post->image;
        if ($post->delete()) {
            if ($imagePath) {
                $this->deleteImage($imagePath);
            }
        }
    }
}
