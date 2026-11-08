<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Traits\Uploadable;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class PostService
{
    use Uploadable;

    public function create(array $data, User $user): Post
    {
        $newImage = isset($data['image']) ? $this->uploadFile($data['image'], 'posts') : null;

        try {
            $post = $user->posts()->create(Arr::only($data, ['body']) + ['image' => $newImage]);
        } catch (Exception $e) {
            if ($newImage) {
                $this->deleteFile($newImage);
            }
            throw $e;
        }

        return $this->withResourceData($post);
    }

    private function withResourceData(Post $post): Post
    {
        return $post->load('user')->loadCount(Post::COUNTED_RELATIONS);
    }

    public function update(array $data, Post $post): Post
    {
        $attributes = Arr::only($data, ['body']);
        $oldImage = $post->image;
        $newImage = null;

        if (isset($data['image'])) {
            $newImage = $this->uploadFile($data['image'], 'posts');
            $attributes['image'] = $newImage;
        } elseif (!empty($data['remove_image'])) {
            $attributes['image'] = null;
        }

        try {
            $post->update($attributes);
        } catch (Exception $e) {
            if ($newImage) {
                $this->deleteFile($newImage);
            }
            throw $e;
        }

        if ($oldImage && array_key_exists('image', $attributes)) {
            $this->deleteImageQuietly($oldImage);
        }

        return $this->withResourceData($post);
    }

    private function deleteImageQuietly(string $path): void
    {
        try {
            $this->deleteFile($path);
        } catch (Exception $e) {
            Log::error('Failed to delete image: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
        }
    }

    public function delete(Post $post): void
    {
        $imagePath = $post->image;
        $post->delete();

        if ($imagePath) {
            $this->deleteImageQuietly($imagePath);
        }
    }
}
