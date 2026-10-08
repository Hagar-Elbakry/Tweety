<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserSimpleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'body' => $this->body,
            'image' => $this->image ? Storage::url($this->image) : null,
            'likes_count' => $this->whenCounted('likes'),
            'bookmarks_count' => $this->whenCounted('bookmarks'),
            'comments_count' => $this->whenCounted('comments'),
            'reposts_count' => $this->whenCounted('reposts'),
            'created_at' => $this->created_at->toIsoString(),
            'updated_at' => $this->updated_at->toIsoString(),
            'user' => $this->whenLoaded('user', fn() => new UserSimpleResource($this->user)),
        ];
    }
}
