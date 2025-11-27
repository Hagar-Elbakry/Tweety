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
}
