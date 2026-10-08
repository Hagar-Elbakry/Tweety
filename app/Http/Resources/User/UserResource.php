<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'avatar' => $this->avatar_url,
            'banner' => $this->banner_url,
            'bio' => $this->bio,
            'created_at' => $this->created_at->toIsoString(),
            'updated_at' => $this->updated_at->toIsoString(),
        ];
    }
}
