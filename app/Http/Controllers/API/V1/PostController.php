<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function store(StorePostRequest $request) {
        $data = $request->validated();
        if($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images');
            $data['image'] = $imagePath;
        }
        $data['user_id'] = $request->user()->id;
        $post = Post::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'data' => new PostResource($post)
        ]);
    }

    public function update(StorePostRequest $request, Post $post) {
        $data = $request->validated();
        if($request->user()->cannot('update', $post)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this post',
            ], 403);
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

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => new PostResource($post)
        ]);
    }

    public function destroy(Post $post) {
        if(request()->user()->cannot('delete', $post)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this post',
            ], 403);
        }
        if($post->image) {
            Storage::delete($post->image);
        }
        $post->delete();
        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ]);
    }
}
