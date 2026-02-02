<?php

namespace App\Services;

use App\Models\Post;
use App\Traits\Uploadable;
use Exception;

class PostService
{
    use Uploadable;

    public function create(array $data): Post
    {
        if (isset($data['image'])) {
            $data['image'] = $this->UploadImage($data['image'], 'posts');
        }
        $post = Post::create($data);

        return $post->load('user');
    }

    public function update(array $data, Post $post): Post
    {
        $newImagePath = null;
        $oldImagePath = $post->image;
        try {
            if (isset($data['image'])) {
                $newImagePath = $this->UploadImage($data['image'], 'posts');
                $data['image'] = $newImagePath;
            }
            $post->update($data);
            if ($newImagePath && $oldImagePath) {
                $this->deleteImage($oldImagePath);
            }

            return $post;
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
