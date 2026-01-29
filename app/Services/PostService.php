<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PostService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function create(array $data): Post
    {
        if (isset($data['image'])) {
            $data['image'] = $this->UploadImage($data['image']);
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
                $newImagePath = $this->UploadImage($data['image']);
                $data['image'] = $newImagePath;
            }
            $post->update($data);
            if ($newImagePath && $oldImagePath) {
                $this->deleteImage($oldImagePath);
            }

            return $post;
        } catch (\Exception $e) {
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

    private function UploadImage(UploadedFile $image): string
    {
        return $image->store('posts', 'public');
    }

    private function deleteImage(string $path): void
    {
        Storage::disk('public')->delete($path);
    }
}
