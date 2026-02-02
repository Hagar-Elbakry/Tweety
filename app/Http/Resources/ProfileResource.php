<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'username' => $this->username,
            $this->mergeWhen(auth()->id() === $this->id, [
                'email' => $this->email,
                'posts_count' => $this->posts()->count(),
            ]),
            'avatar' => $this->avatar ? Storage::url($this->avatar) : null,
            'banner' => $this->banner ? Storage::url($this->banner) : null,
            'bio' => $this->bio ?? null,
            'posts' => PostResource::collection($this->whenLoaded('posts')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
