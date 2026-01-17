<?php

namespace App\Services;

use App\Models\Post;
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

    public function createPost($request) {
        $data = $request->validated();
        if($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images');
            $data['image'] = $imagePath;
        }
        $data['user_id'] = $request->user()->id;
        $post = Post::create($data);
        return $post;
    }

    public function updatePost($request, $post) {
        $data = $request->validated();
        if($request->user()->cannot('update', $post)) {
            return null;
        }
        if($request->hasFile('image')) {
            $oldImage = $post->image;
            if($oldImage) {
                Storage::delete($oldImage);
            }
            $imagePath = $request->file('image')->store('images');
            $data['image'] = $imagePath;
        }
        $post->update($data);
        return $post;
    }

    public function deletePost($post) {
        if(request()->user()->cannot('delete', $post)) {
            return false;
        }
        if($post->image) {
            Storage::delete($post->image);
        }
        $post->delete();
        return true;
    }
}
