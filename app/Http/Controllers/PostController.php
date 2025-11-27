<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;

class PostController extends Controller
{
    public function store(StorePostRequest $request) {
        $post = Post::create([
            'body' => $request->body,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'data' => new PostResource($post)
        ]);
    }

    public function update(StorePostRequest $request, Post $post) {
        if($request->user()->cannot('update', $post)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this post',
            ], 403);
        }
        $post->update([
            'body' => $request->body,
        ]);

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

        $post->delete();
        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ]);
    }
}
