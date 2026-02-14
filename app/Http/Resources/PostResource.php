<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Maize\Markable\Models\Bookmark;
use Maize\Markable\Models\Like;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body ?: null,
            'image' => $this->image ? Storage::url($this->image) : null,
            'likes_count' => Like::count($this->resource),
            'bookmark_count' => Bookmark::count($this->resource),
            'comments_count' => $this->comments_count,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
