<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserSimpleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this['type'],
            'user' => new UserSimpleResource($this['user']),
            'created_at' => $this['created_at']->format('Y-m-d H:i:s'),
        ];
    }
}
