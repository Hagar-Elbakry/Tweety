<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function create($data)
    {
        if (isset($data['image'])) {
            $data['image'] = $this->UploadImage($data['image']);
        }
        $post = Post::create($data);

        return $post->load('user');
    }

    public function update($data, Post $post)
    {
        $newImagePath = null;
        $oldImagePath = $post->image;

        return DB::transaction(function () use ($data, $post, &$newImagePath, &$oldImagePath) {
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
        });
    }

    public function delete(Post $post)
    {
        $imagePath = $post->image;
        if ($post->delete()) {
            if ($imagePath) {
                $this->deleteImage($imagePath);
            }
        }
    }

    private function UploadImage($image)
    {
        return $image->store('posts', 'public');
    }

    private function deleteImage($path)
    {
        Storage::disk('public')->delete($path);
    }
}
