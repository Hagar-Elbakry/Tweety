<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserSimpleResource;
use Illuminate\Http\Request;

class UserSearchResource extends UserSimpleResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            parent::toArray($request),
            'is_following' => $request->user()->isFollowing($this->resource),
        ];
    }
}
